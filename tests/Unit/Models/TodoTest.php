<?php

use App\Models\Todo;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('todo belongs to todo list', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create(['name' => 'Test List']);
    $todo = $todoList->todos()->create(['title' => 'Test Todo', 'priority' => 'medium']);

    expect($todo->todoList)->toBeInstanceOf(TodoList::class);
    expect($todo->todoList->id)->toBe($todoList->id);
    expect($todo->todoList->name)->toBe('Test List');
});

test('todo has correct fillable attributes', function () {
    $todo = new Todo;

    $expectedFillable = [
        'title',
        'description',
        'completed_at',
        'priority',
        'due_date',
        'todo_list_id',
    ];

    expect($todo->getFillable())->toBe($expectedFillable);
});

test('todo casts attributes correctly', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create(['name' => 'Test List']);
    $todo = $todoList->todos()->create([
        'title' => 'Test Todo',
        'priority' => 'medium',
        'completed_at' => now(),
        'due_date' => '2024-12-31',
    ]);

    expect($todo->completed_at)->not->toBeNull();
    expect($todo->completed_at)->toBeInstanceOf(DateTime::class);
    expect($todo->due_date)->toBeInstanceOf(DateTime::class);
});

test('todo can be created with all attributes', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create(['name' => 'Test List']);

    $todo = $todoList->todos()->create([
        'title' => 'Complete Project',
        'description' => 'Finish the final project for the client',
        'priority' => 'high',
        'completed_at' => null,
        'due_date' => '2024-12-31',
    ]);

    expect($todo->title)->toBe('Complete Project');
    expect($todo->description)->toBe('Finish the final project for the client');
    expect($todo->priority)->toBe('high');
    expect($todo->completed_at)->toBeNull();
    expect($todo->due_date->format('Y-m-d'))->toBe('2024-12-31');
    expect($todo->todo_list_id)->toBe($todoList->id);
});

test('todo can be created with minimal attributes', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create(['name' => 'Test List']);

    $todo = $todoList->todos()->create([
        'title' => 'Simple Todo',
        'priority' => 'low',
    ]);

    expect($todo->title)->toBe('Simple Todo');
    expect($todo->priority)->toBe('low');
    expect($todo->description)->toBeNull();
    expect($todo->completed_at)->toBeNull(); // Default value
    expect($todo->due_date)->toBeNull();
});

test('todo priority can be low, medium, or high', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create(['name' => 'Test List']);

    $priorities = ['low', 'medium', 'high'];

    foreach ($priorities as $priority) {
        $todo = $todoList->todos()->create([
            'title' => "Todo with {$priority} priority",
            'priority' => $priority,
        ]);

        expect($todo->priority)->toBe($priority);
    }
});

test('todo completed defaults to null', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create(['name' => 'Test List']);

    $todo = $todoList->todos()->create([
        'title' => 'New Todo',
        'priority' => 'medium',
    ]);

    expect($todo->completed_at)->toBeNull();
});

test('todo can be marked as completed', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create(['name' => 'Test List']);

    $todo = $todoList->todos()->create([
        'title' => 'Todo to Complete',
        'priority' => 'medium',
        'completed_at' => null,
    ]);

    expect($todo->completed_at)->toBeNull();

    $todo->update(['completed_at' => now()]);

    expect($todo->fresh()->completed_at)->not->toBeNull();
});

test('todo can have due date', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create(['name' => 'Test List']);

    $dueDate = '2024-12-25';
    $todo = $todoList->todos()->create([
        'title' => 'Christmas Todo',
        'priority' => 'high',
        'due_date' => $dueDate,
    ]);

    expect($todo->due_date)->toBeInstanceOf(DateTime::class);
    expect($todo->due_date->format('Y-m-d'))->toBe($dueDate);
});

test('todo can be updated', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create(['name' => 'Test List']);

    $todo = $todoList->todos()->create([
        'title' => 'Original Title',
        'description' => 'Original description',
        'priority' => 'low',
        'completed_at' => null,
    ]);

    $todo->update([
        'title' => 'Updated Title',
        'description' => 'Updated description',
        'priority' => 'high',
        'completed_at' => now(),
    ]);

    $updatedTodo = $todo->fresh();
    expect($updatedTodo->title)->toBe('Updated Title');
    expect($updatedTodo->description)->toBe('Updated description');
    expect($updatedTodo->priority)->toBe('high');
    expect($updatedTodo->completed_at)->not->toBeNull();
});

test('todo can be deleted', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create(['name' => 'Test List']);

    $todo = $todoList->todos()->create([
        'title' => 'Todo to Delete',
        'priority' => 'medium',
    ]);

    $todoId = $todo->id;

    $todo->delete();

    expect(Todo::find($todoId))->toBeNull();
});

test('todo is deleted when todo list is deleted', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create(['name' => 'Test List']);

    $todo = $todoList->todos()->create([
        'title' => 'Todo in List',
        'priority' => 'medium',
    ]);

    $todoId = $todo->id;

    // Delete the todo list
    $todoList->delete();

    // Todo should also be deleted due to cascade
    expect(Todo::find($todoId))->toBeNull();
});

test('todo factory creates valid todos', function () {
    $todo = Todo::factory()->create();

    expect($todo)->toBeInstanceOf(Todo::class);
    expect($todo->title)->not->toBeNull();
    expect($todo->priority)->toBeIn(['low', 'medium', 'high']);
    // completed_at can be either null or a DateTime instance
    if ($todo->completed_at !== null) {
        expect($todo->completed_at)->toBeInstanceOf(DateTime::class);
    }
    expect($todo->todo_list_id)->not->toBeNull();
});

test('todo factory can create completed todos', function () {
    $todo = Todo::factory()->completed()->create();

    expect($todo->completed_at)->not->toBeNull();
});

test('todo factory can create todos with specific priority', function () {
    $highPriorityTodo = Todo::factory()->highPriority()->create();
    $mediumPriorityTodo = Todo::factory()->mediumPriority()->create();
    $lowPriorityTodo = Todo::factory()->lowPriority()->create();

    expect($highPriorityTodo->priority)->toBe('high');
    expect($mediumPriorityTodo->priority)->toBe('medium');
    expect($lowPriorityTodo->priority)->toBe('low');
});

test('todo factory can create todos with due dates', function () {
    $todo = Todo::factory()->withDueDate()->create();

    expect($todo->due_date)->not->toBeNull();
    expect($todo->due_date)->toBeInstanceOf(DateTime::class);
});
