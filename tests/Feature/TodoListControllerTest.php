<?php

use App\Models\TodoList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can view todo lists index', function () {
    $user = User::factory()->create();
    $todoList1 = $user->todoLists()->create(['name' => 'Work Tasks']);
    $todoList2 = $user->todoLists()->create(['name' => 'Personal Tasks']);

    $response = $this->actingAs($user)->get('/todo-lists');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('TodoLists/Index')
        ->has('lists', 2)
        ->where('lists.0.name', 'Work Tasks')
        ->where('lists.1.name', 'Personal Tasks')
    );
});

test('user can create todo list', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/todo-lists', [
        'name' => 'New Project',
        'description' => 'Project description',
        'refresh_daily' => true,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'List created successfully!');

    $this->assertDatabaseHas('todo_lists', [
        'name' => 'New Project',
        'description' => 'Project description',
        'refresh_daily' => true,
        'user_id' => $user->id,
    ]);
});

test('user can update todo list', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create([
        'name' => 'Original Name',
        'description' => 'Original description',
        'refresh_daily' => false,
    ]);

    $response = $this->actingAs($user)->put("/todo-lists/{$todoList->id}", [
        'name' => 'Updated Name',
        'description' => 'Updated description',
        'refresh_daily' => true,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'List updated successfully!');

    $todoList->refresh();
    expect($todoList->name)->toBe('Updated Name');
    expect($todoList->description)->toBe('Updated description');
    expect($todoList->refresh_daily)->toBeTrue();
});

test('user can delete todo list', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create(['name' => 'List to Delete']);

    $response = $this->actingAs($user)->delete("/todo-lists/{$todoList->id}");

    $response->assertRedirect();
    $response->assertSessionHas('success', 'List deleted successfully!');

    expect(TodoList::find($todoList->id))->toBeNull();
});

test('user cannot access other users todo lists', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $todoList = $user2->todoLists()->create(['name' => 'Private List']);

    $response = $this->actingAs($user1)->put("/todo-lists/{$todoList->id}", [
        'name' => 'Hacked Name',
    ]);

    $response->assertStatus(403);
});

test('user cannot delete other users todo lists', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $todoList = $user2->todoLists()->create(['name' => 'Private List']);

    $response = $this->actingAs($user1)->delete("/todo-lists/{$todoList->id}");

    $response->assertStatus(403);
});

test('todo list validation works for creation', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/todo-lists', [
        'name' => '', // Required field
        'description' => str_repeat('a', 1001), // Too long
    ]);

    $response->assertSessionHasErrors(['name']);
});

test('todo list validation works for updates', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create(['name' => 'Test List']);

    $response = $this->actingAs($user)->put("/todo-lists/{$todoList->id}", [
        'name' => '', // Required field
    ]);

    $response->assertSessionHasErrors(['name']);
});

test('user can view todos for specific list', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create(['name' => 'Work Tasks']);

    $todo1 = $todoList->todos()->create(['title' => 'Task 1', 'priority' => 'high']);
    $todo2 = $todoList->todos()->create(['title' => 'Task 2', 'priority' => 'medium']);

    $response = $this->actingAs($user)->get("/todo-lists/{$todoList->id}/todos");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('TodoLists/Todos')
        ->has('list')
        ->has('todos', 2)
        ->where('list.name', 'Work Tasks')
        ->where('todos.0.title', 'Task 1')
        ->where('todos.1.title', 'Task 2')
    );
});

test('user cannot view todos for other users lists', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $todoList = $user2->todoLists()->create(['name' => 'Private List']);

    $response = $this->actingAs($user1)->get("/todo-lists/{$todoList->id}/todos");

    $response->assertStatus(403);
});

test('todo lists index includes completion statistics', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create(['name' => 'Test List']);

    // Create some todos with different completion status
    $todoList->todos()->create(['title' => 'Completed Todo', 'priority' => 'high', 'completed_at' => now()]);
    $todoList->todos()->create(['title' => 'Pending Todo 1', 'priority' => 'medium', 'completed_at' => null]);
    $todoList->todos()->create(['title' => 'Pending Todo 2', 'priority' => 'low', 'completed_at' => null]);

    $response = $this->actingAs($user)->get('/todo-lists');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('TodoLists/Index')
        ->has('lists', 1)
        ->where('lists.0.filtered_completion_percentage', 33.3)
    );
});

test('todo list todos page includes completion statistics', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create(['name' => 'Test List']);

    // Create todos with different completion status
    $todoList->todos()->create(['title' => 'Completed Todo', 'priority' => 'high', 'completed_at' => now()]);
    $todoList->todos()->create(['title' => 'Pending Todo', 'priority' => 'medium', 'completed_at' => null]);

    $response = $this->actingAs($user)->get("/todo-lists/{$todoList->id}/todos");

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('TodoLists/Todos')
        ->has('list')
        ->where('list.filtered_completion_percentage', 50)
    );
});

test('guest cannot access todo lists', function () {
    $response = $this->get('/todo-lists');
    $response->assertRedirect('/login');

    $response = $this->post('/todo-lists', []);
    $response->assertRedirect('/login');
});

test('todo list duplicate functionality works correctly', function () {
    $user = User::factory()->create();
    $originalList = $user->todoLists()->create([
        'name' => 'Original List',
        'description' => 'Original description',
        'refresh_daily' => true,
    ]);

    // Add todos to the original list
    $originalList->todos()->create(['title' => 'Todo 1', 'priority' => 'high', 'completed_at' => now()]);
    $originalList->todos()->create(['title' => 'Todo 2', 'priority' => 'medium', 'completed_at' => null]);

    $response = $this->actingAs($user)->post("/todo-lists/{$originalList->id}/duplicate");

    $response->assertRedirect();
    $response->assertSessionHas('success', 'List duplicated successfully!');

    // Verify duplicated list exists
    $duplicatedList = TodoList::where('name', 'Original List (Copy)')->first();
    expect($duplicatedList)->not->toBeNull();
    expect($duplicatedList->description)->toBe('Original description');
    expect($duplicatedList->refresh_daily)->toBeTrue();
    expect($duplicatedList->user_id)->toBe($user->id);

    // Verify todos were duplicated with completion reset
    expect($duplicatedList->todos)->toHaveCount(2);
    $duplicatedTodos = $duplicatedList->todos;
    expect($duplicatedTodos->where('title', 'Todo 1')->first()->completed)->toBeNull();
    expect($duplicatedTodos->where('title', 'Todo 2')->first()->completed)->toBeNull();
});

test('user cannot duplicate other users todo lists', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $todoList = $user2->todoLists()->create(['name' => 'Private List']);

    $response = $this->actingAs($user1)->post("/todo-lists/{$todoList->id}/duplicate");

    $response->assertStatus(403);
});
