<?php

use App\Models\Todo;
use App\Models\TodoList;
use App\Models\User;

test('todo completion status can be toggled', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create([
        'name' => 'Test List',
        'description' => 'Test description',
    ]);

    $todo = $todoList->todos()->create([
        'title' => 'Test Todo',
        'priority' => 'medium',
        'completed_at' => null,
    ]);

    // Initially not completed
    expect($todo->completed_at)->toBeNull();

    // Mark as completed
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
    expect($todo->completed_at)->not->toBeNull();

    // Mark as incomplete
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
    expect($todo->completed_at)->toBeNull();
});

test('todo completion updates completion percentage', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create([
        'name' => 'Test List',
        'description' => 'Test description',
    ]);

    $todo1 = $todoList->todos()->create([
        'title' => 'Test Todo 1',
        'priority' => 'medium',
        'completed_at' => null,
    ]);

    $todo2 = $todoList->todos()->create([
        'title' => 'Test Todo 2',
        'priority' => 'medium',
        'completed_at' => null,
    ]);

    // Initially 0% complete
    expect($todoList->fresh()->completion_percentage)->toBe(0.0);

    // Mark first todo as complete
    $this->actingAs($user)->put("/todos/{$todo1->id}", [
        'title' => $todo1->title,
        'description' => $todo1->description,
        'completed' => true,
        'priority' => $todo1->priority,
        'due_date' => $todo1->due_date,
        'todo_list_id' => $todo1->todo_list_id,
    ]);

    // Should be 50% complete
    expect($todoList->fresh()->completion_percentage)->toBe(50.0);

    // Mark second todo as complete
    $this->actingAs($user)->put("/todos/{$todo2->id}", [
        'title' => $todo2->title,
        'description' => $todo2->description,
        'completed' => true,
        'priority' => $todo2->priority,
        'due_date' => $todo2->due_date,
        'todo_list_id' => $todo2->todo_list_id,
    ]);

    // Should be 100% complete
    expect($todoList->fresh()->completion_percentage)->toBe(100.0);
});
