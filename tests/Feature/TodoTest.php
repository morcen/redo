<?php

use App\Models\Todo;
use App\Models\User;

test('user can view todos page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/todos');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Todos/Index'));
});

test('user can create todo', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create([
        'name' => 'Test List',
        'description' => 'Test description',
    ]);

    $todoData = [
        'title' => 'Test Todo',
        'description' => 'This is a test todo',
        'priority' => 'medium',
        'due_date' => now()->addDays(1)->format('Y-m-d'),
        'todo_list_id' => $todoList->id,
    ];

    $response = $this->actingAs($user)->post('/todos', $todoData);

    $response->assertRedirect();
    expect($user->todos()->where('title', 'Test Todo')->exists())->toBeTrue();
});

test('user can update todo', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create([
        'name' => 'Test List',
        'description' => 'Test description',
    ]);
    $todo = $todoList->todos()->create([
        'title' => 'Original Todo',
        'priority' => 'low',
        'completed' => false,
    ]);

    $updateData = [
        'title' => 'Updated Todo',
        'description' => 'Updated description',
        'completed' => true,
        'priority' => 'high',
        'due_date' => now()->addDays(2)->format('Y-m-d'),
        'todo_list_id' => $todoList->id,
    ];

    $response = $this->actingAs($user)->put("/todos/{$todo->id}", $updateData);

    $response->assertRedirect();
    $todo->refresh();
    expect($todo->title)->toBe('Updated Todo');
    expect($todo->completed)->toBeTrue();
});

test('user can delete todo', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create([
        'name' => 'Test List',
        'description' => 'Test description',
    ]);
    $todo = $todoList->todos()->create([
        'title' => 'Todo to delete',
        'priority' => 'low',
    ]);

    $response = $this->actingAs($user)->delete("/todos/{$todo->id}");

    $response->assertRedirect();
    expect(Todo::find($todo->id))->toBeNull();
});

test('user cannot access other users todos', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $todoList = $user2->todoLists()->create([
        'name' => 'User 2 List',
        'description' => 'Private list',
    ]);
    $todo = $todoList->todos()->create([
        'title' => 'Private Todo',
        'priority' => 'medium',
    ]);

    $response = $this->actingAs($user1)->put("/todos/{$todo->id}", [
        'title' => 'Hacked Todo',
        'priority' => 'high',
        'todo_list_id' => $todoList->id,
    ]);

    $response->assertStatus(403);
});

test('user can view todos for a specific list', function () {
    $user = User::factory()->create();
    $todoList1 = $user->todoLists()->create(['name' => 'Work Tasks']);
    $todoList2 = $user->todoLists()->create(['name' => 'Personal Tasks']);

    $todo1 = $todoList1->todos()->create([
        'title' => 'Work Todo',
        'priority' => 'medium',
    ]);

    $todo2 = $todoList2->todos()->create([
        'title' => 'Personal Todo',
        'priority' => 'medium',
    ]);

    // Test viewing todos for first list
    $response = $this->actingAs($user)->get("/todo-lists/{$todoList1->id}/todos");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('TodoLists/Todos')
        ->has('todos', 1)
        ->where('todos.0.title', 'Work Todo')
        ->where('list.id', $todoList1->id)
        ->where('list.name', 'Work Tasks')
    );

    // Test viewing todos for second list
    $response = $this->actingAs($user)->get("/todo-lists/{$todoList2->id}/todos");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('TodoLists/Todos')
        ->has('todos', 1)
        ->where('todos.0.title', 'Personal Todo')
        ->where('list.id', $todoList2->id)
        ->where('list.name', 'Personal Tasks')
    );
});

test('user cannot access other users todo lists', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $todoList = $user2->todoLists()->create(['name' => 'User 2 List']);

    // User 2 creates a todo in their list
    $todo = $todoList->todos()->create([
        'title' => 'User 2 Todo',
        'priority' => 'medium',
    ]);

    // User 1 should not be able to access User 2's list
    $response = $this->actingAs($user1)->get("/todo-lists/{$todoList->id}/todos");

    $response->assertStatus(403);
});

test('user can toggle todo completion status', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create([
        'name' => 'Test List',
        'description' => 'Test description',
    ]);
    $todo = $todoList->todos()->create([
        'title' => 'Test Todo',
        'priority' => 'medium',
        'completed' => false,
    ]);

    // Toggle from false to true
    $response = $this->actingAs($user)->put("/todos/{$todo->id}", [
        'title' => $todo->title,
        'description' => $todo->description,
        'completed' => true,
        'priority' => $todo->priority,
        'due_date' => $todo->due_date,
        'todo_list_id' => $todo->todo_list_id,
    ]);

    $response->assertRedirect();
    $todo->refresh();
    expect($todo->completed)->toBeTrue();

    // Toggle from true to false
    $response = $this->actingAs($user)->put("/todos/{$todo->id}", [
        'title' => $todo->title,
        'description' => $todo->description,
        'completed' => false,
        'priority' => $todo->priority,
        'due_date' => $todo->due_date,
        'todo_list_id' => $todo->todo_list_id,
    ]);

    $response->assertRedirect();
    $todo->refresh();
    expect($todo->completed)->toBeFalse();
});
