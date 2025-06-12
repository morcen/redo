<?php

use App\Models\Todo;
use App\Models\User;
use Carbon\Carbon;

test('todo lists index shows date-filtered completion percentages', function () {
    $user = User::factory()->create();

    // Create a todo list
    $todoList = $user->todoLists()->create([
        'name' => 'Test List',
        'description' => 'Test Description',
        'refresh_daily' => false,
    ]);

    // Create todos for today
    $today = Carbon::today();
    $todo1 = new Todo([
        'title' => 'Today Todo 1',
        'description' => 'Test todo for today',
        'priority' => 'medium',
        'completed_at' => now(),
    ]);
    $todo1->created_at = $today;
    $todo1->updated_at = $today;
    $todoList->todos()->save($todo1);

    $todo2 = new Todo([
        'title' => 'Today Todo 2',
        'description' => 'Another test todo for today',
        'priority' => 'high',
        'completed_at' => null,
    ]);
    $todo2->created_at = $today;
    $todo2->updated_at = $today;
    $todoList->todos()->save($todo2);

    // Create todos for yesterday
    $yesterday = Carbon::yesterday();
    $todo3 = new Todo([
        'title' => 'Yesterday Todo 1',
        'description' => 'Test todo for yesterday',
        'priority' => 'low',
        'completed_at' => now(),
    ]);
    $todo3->created_at = $yesterday;
    $todo3->updated_at = $yesterday;
    $todoList->todos()->save($todo3);

    $todo4 = new Todo([
        'title' => 'Yesterday Todo 2',
        'description' => 'Another test todo for yesterday',
        'priority' => 'medium',
        'completed_at' => now(),
    ]);
    $todo4->created_at = $yesterday;
    $todo4->updated_at = $yesterday;
    $todoList->todos()->save($todo4);

    // Test today's completion percentage (1 out of 2 completed = 50%)
    $response = $this->actingAs($user)->get('/todo-lists?date='.$today->format('Y-m-d'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('TodoLists/Index')
        ->has('lists', 1)
        ->where('lists.0.filtered_completion_percentage', 50)
        ->where('lists.0.filtered_total_todos', 2)
        ->where('lists.0.filtered_completed_todos', 1)
        ->where('selectedDate', $today->format('Y-m-d'))
    );

    // Test yesterday's completion percentage (2 out of 2 completed = 100%)
    $response = $this->actingAs($user)->get('/todo-lists?date='.$yesterday->format('Y-m-d'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('TodoLists/Index')
        ->has('lists', 1)
        ->where('lists.0.filtered_completion_percentage', 100)
        ->where('lists.0.filtered_total_todos', 2)
        ->where('lists.0.filtered_completed_todos', 2)
        ->where('selectedDate', $yesterday->format('Y-m-d'))
    );
});

test('todo lists index provides available dates', function () {
    $user = User::factory()->create();

    $todoList = $user->todoLists()->create([
        'name' => 'Test List',
        'description' => 'Test Description',
        'refresh_daily' => false,
    ]);

    // Create todos on different dates
    $today = Carbon::today();
    $yesterday = Carbon::yesterday();
    $twoDaysAgo = Carbon::today()->subDays(2);

    $todo1 = new Todo([
        'title' => 'Today Todo',
        'priority' => 'medium',
    ]);
    $todo1->created_at = $today;
    $todo1->updated_at = $today;
    $todoList->todos()->save($todo1);

    $todo2 = new Todo([
        'title' => 'Yesterday Todo',
        'priority' => 'medium',
    ]);
    $todo2->created_at = $yesterday;
    $todo2->updated_at = $yesterday;
    $todoList->todos()->save($todo2);

    $todo3 = new Todo([
        'title' => 'Two Days Ago Todo',
        'priority' => 'medium',
    ]);
    $todo3->created_at = $twoDaysAgo;
    $todo3->updated_at = $twoDaysAgo;
    $todoList->todos()->save($todo3);

    $response = $this->actingAs($user)->get('/todo-lists');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('TodoLists/Index')
        ->has('availableDates', 3)
        ->where('availableDates.0', $twoDaysAgo->format('Y-m-d'))
        ->where('availableDates.1', $yesterday->format('Y-m-d'))
        ->where('availableDates.2', $today->format('Y-m-d'))
    );
});

test('individual todo list view shows date-filtered completion percentages', function () {
    $user = User::factory()->create();

    $todoList = $user->todoLists()->create([
        'name' => 'Test List',
        'description' => 'Test Description',
        'refresh_daily' => false,
    ]);

    // Create todos for today
    $today = Carbon::today();
    $todo1 = new Todo([
        'title' => 'Today Todo 1',
        'priority' => 'medium',
        'completed_at' => now(),
    ]);
    $todo1->created_at = $today;
    $todo1->updated_at = $today;
    $todoList->todos()->save($todo1);

    $todo2 = new Todo([
        'title' => 'Today Todo 2',
        'priority' => 'high',
        'completed_at' => null,
    ]);
    $todo2->created_at = $today;
    $todo2->updated_at = $today;
    $todoList->todos()->save($todo2);

    // Create todos for yesterday
    $yesterday = Carbon::yesterday();
    $todo3 = new Todo([
        'title' => 'Yesterday Todo',
        'priority' => 'low',
        'completed_at' => now(),
    ]);
    $todo3->created_at = $yesterday;
    $todo3->updated_at = $yesterday;
    $todoList->todos()->save($todo3);

    // Test today's completion percentage in individual view
    $response = $this->actingAs($user)->get("/todo-lists/{$todoList->id}/todos?date=".$today->format('Y-m-d'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('TodoLists/Todos')
        ->where('list.filtered_completion_percentage', 50)
        ->where('list.filtered_total_todos', 2)
        ->where('list.filtered_completed_todos', 1)
        ->has('todos', 2)
    );

    // Test yesterday's completion percentage in individual view
    $response = $this->actingAs($user)->get("/todo-lists/{$todoList->id}/todos?date=".$yesterday->format('Y-m-d'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('TodoLists/Todos')
        ->where('list.filtered_completion_percentage', 100)
        ->where('list.filtered_total_todos', 1)
        ->where('list.filtered_completed_todos', 1)
        ->has('todos', 1)
    );
});
