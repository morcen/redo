<?php

use App\Models\User;

test('hierarchical ownership model works correctly', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // User 1 creates a todo list
    $list1 = $user1->todoLists()->create([
        'name' => 'User 1 List',
        'description' => 'Private list for user 1',
    ]);

    // User 2 creates a todo list
    $list2 = $user2->todoLists()->create([
        'name' => 'User 2 List',
        'description' => 'Private list for user 2',
    ]);

    // User 1 creates todos in their list
    $todo1 = $list1->todos()->create([
        'title' => 'User 1 Todo',
        'priority' => 'high',
    ]);

    // User 2 creates todos in their list
    $todo2 = $list2->todos()->create([
        'title' => 'User 2 Todo',
        'priority' => 'medium',
    ]);

    // Verify ownership through relationships
    expect($list1->user_id)->toBe($user1->id);
    expect($list2->user_id)->toBe($user2->id);

    // Verify todos belong to correct lists
    expect($todo1->todo_list_id)->toBe($list1->id);
    expect($todo2->todo_list_id)->toBe($list2->id);

    // Verify user can access their own todos through the relationship
    expect($user1->todos()->count())->toBe(1);
    expect($user2->todos()->count())->toBe(1);

    expect($user1->todos()->first()->title)->toBe('User 1 Todo');
    expect($user2->todos()->first()->title)->toBe('User 2 Todo');

    // Verify users can only see their own todo lists
    expect($user1->todoLists()->count())->toBe(1);
    expect($user2->todoLists()->count())->toBe(1);

    expect($user1->todoLists()->first()->name)->toBe('User 1 List');
    expect($user2->todoLists()->first()->name)->toBe('User 2 List');
});

test('user cannot create todo in another users list', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // User 1 creates a todo list
    $list = $user1->todoLists()->create([
        'name' => 'User 1 List',
        'description' => 'Private list',
    ]);

    // User 2 tries to create a todo in User 1's list
    $response = $this->actingAs($user2)->post('/todos', [
        'title' => 'Unauthorized Todo',
        'priority' => 'medium',
        'todo_list_id' => $list->id,
    ]);

    $response->assertStatus(403);
});

test('user cannot access another users todo list', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // User 1 creates a todo list
    $list = $user1->todoLists()->create([
        'name' => 'User 1 List',
        'description' => 'Private list',
    ]);

    // User 2 tries to access User 1's list
    $response = $this->actingAs($user2)->get("/todo-lists/{$list->id}/todos");

    $response->assertStatus(403);
});

test('user cannot update another users todo', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // User 1 creates a todo list and todo
    $list = $user1->todoLists()->create([
        'name' => 'User 1 List',
        'description' => 'Private list',
    ]);

    $todo = $list->todos()->create([
        'title' => 'User 1 Todo',
        'priority' => 'high',
    ]);

    // User 2 tries to update User 1's todo
    $response = $this->actingAs($user2)->put("/todos/{$todo->id}", [
        'title' => 'Hacked Todo',
        'priority' => 'low',
        'todo_list_id' => $list->id,
    ]);

    $response->assertStatus(403);
});

test('user cannot delete another users todo', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    // User 1 creates a todo list and todo
    $list = $user1->todoLists()->create([
        'name' => 'User 1 List',
        'description' => 'Private list',
    ]);

    $todo = $list->todos()->create([
        'title' => 'User 1 Todo',
        'priority' => 'high',
    ]);

    // User 2 tries to delete User 1's todo
    $response = $this->actingAs($user2)->delete("/todos/{$todo->id}");

    $response->assertStatus(403);
});
