<?php

use App\Models\User;

test('todo list completion percentage is calculated correctly', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create([
        'name' => 'Test List',
        'description' => 'Test description',
    ]);

    // Create 5 todos, 3 completed and 2 pending
    $todoList->todos()->create(['title' => 'Todo 1', 'priority' => 'medium', 'completed' => true]);
    $todoList->todos()->create(['title' => 'Todo 2', 'priority' => 'medium', 'completed' => true]);
    $todoList->todos()->create(['title' => 'Todo 3', 'priority' => 'medium', 'completed' => true]);
    $todoList->todos()->create(['title' => 'Todo 4', 'priority' => 'medium', 'completed' => false]);
    $todoList->todos()->create(['title' => 'Todo 5', 'priority' => 'medium', 'completed' => false]);

    // Test the model attributes
    expect($todoList->total_todos)->toBe(5);
    expect($todoList->completed_todos)->toBe(3);
    expect($todoList->completion_percentage)->toBe(60.0);
});

test('todo list with no todos has 0% completion', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create([
        'name' => 'Empty List',
        'description' => 'Test description',
    ]);

    expect($todoList->total_todos)->toBe(0);
    expect($todoList->completed_todos)->toBe(0);
    expect($todoList->completion_percentage)->toBe(0.0);
});

test('todo list index includes completion statistics', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create([
        'name' => 'Test List',
        'description' => 'Test description',
    ]);

    // Create 2 todos, 1 completed
    $todoList->todos()->create(['title' => 'Todo 1', 'priority' => 'medium', 'completed' => true]);
    $todoList->todos()->create(['title' => 'Todo 2', 'priority' => 'medium', 'completed' => false]);

    $response = $this->actingAs($user)->get('/todo-lists');

    $response->assertStatus(200);

    // Check that the response includes the completion data
    $lists = $response->viewData('page')['props']['lists'];
    $testList = collect($lists)->firstWhere('name', 'Test List');

    expect($testList['filtered_total_todos'])->toBe(2);
    expect($testList['filtered_completed_todos'])->toBe(1);
    expect($testList['filtered_completion_percentage'])->toBe(50.0);
});

test('completion percentage updates when todo is marked complete', function () {
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

    // Initially 0% complete
    expect($todoList->fresh()->completion_percentage)->toBe(0.0);

    // Mark todo as complete
    $this->actingAs($user)->put("/todos/{$todo->id}", [
        'title' => $todo->title,
        'description' => $todo->description,
        'completed' => true,
        'priority' => $todo->priority,
        'due_date' => $todo->due_date,
        'todo_list_id' => $todo->todo_list_id,
    ]);

    // Should now be 100% complete
    expect($todoList->fresh()->completion_percentage)->toBe(100.0);
});

test('todo list todos page includes completion statistics', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create([
        'name' => 'Test List',
        'description' => 'Test description',
    ]);

    // Create 3 todos, 2 completed
    $todoList->todos()->create(['title' => 'Todo 1', 'priority' => 'medium', 'completed' => true]);
    $todoList->todos()->create(['title' => 'Todo 2', 'priority' => 'medium', 'completed' => true]);
    $todoList->todos()->create(['title' => 'Todo 3', 'priority' => 'medium', 'completed' => false]);

    $response = $this->actingAs($user)->get("/todo-lists/{$todoList->id}/todos");

    $response->assertStatus(200);

    // Check that the response includes the completion data for the specific list
    $list = $response->viewData('page')['props']['list'];

    expect($list['filtered_total_todos'])->toBe(3);
    expect($list['filtered_completed_todos'])->toBe(2);
    expect($list['filtered_completion_percentage'])->toBe(66.7);
});
