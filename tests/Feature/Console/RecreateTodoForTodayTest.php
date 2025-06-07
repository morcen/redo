<?php

use App\Models\Setting;
use App\Models\Todo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('command recreates todos from yesterday for users in new day', function () {
    // Create a user with timezone settings
    $user = User::factory()->create();
    $settings = $user->settings()->create([
        'timezone' => 'America/New_York',
        'date_format' => 'Y-m-d',
        'time_format' => 'H:i',
        'email_notifications' => true,
        'browser_notifications' => false,
    ]);

    // Create a todo list marked for daily refresh
    $todoList = $user->todoLists()->create([
        'name' => 'Daily Tasks',
        'description' => 'Tasks that refresh daily',
        'refresh_daily' => true,
    ]);

    // Create todos from "yesterday" in the user's timezone
    $yesterday = Carbon::now($settings->timezone)->subDay();

    // Use DB::table to ensure the created_at timestamp is set correctly
    DB::table('todos')->insert([
        [
            'todo_list_id' => $todoList->id,
            'title' => 'Morning Exercise',
            'description' => 'Go for a run',
            'priority' => 'high',
            'completed' => true,
            'created_at' => $yesterday,
            'updated_at' => $yesterday,
        ],
        [
            'todo_list_id' => $todoList->id,
            'title' => 'Check Emails',
            'description' => 'Review inbox',
            'priority' => 'medium',
            'completed' => false,
            'created_at' => $yesterday,
            'updated_at' => $yesterday,
        ],
    ]);

    // Mock the current time to be early in the new day (within processing window)
    Carbon::setTestNow(Carbon::now($settings->timezone)->startOfDay()->addMinutes(30));

    // Run the command with force flag to bypass time restrictions
    $this->artisan('app:recreate-todo-for-today --force')
        ->expectsOutput('Starting todo recreation process...')
        ->assertExitCode(0);

    // Verify new todos were created for today
    $todayTodos = $todoList->todos()->whereDate('created_at', Carbon::now($settings->timezone)->format('Y-m-d'))->get();

    expect($todayTodos)->toHaveCount(2);

    // Verify the todos have correct attributes and completion is reset
    $recreatedTodo1 = $todayTodos->where('title', 'Morning Exercise')->first();
    expect($recreatedTodo1)->not->toBeNull();
    expect($recreatedTodo1->completed)->toBeFalse(); // Should be reset
    expect($recreatedTodo1->priority)->toBe('high');
    expect($recreatedTodo1->description)->toBe('Go for a run');

    $recreatedTodo2 = $todayTodos->where('title', 'Check Emails')->first();
    expect($recreatedTodo2)->not->toBeNull();
    expect($recreatedTodo2->completed)->toBeFalse(); // Should be reset
    expect($recreatedTodo2->priority)->toBe('medium');
    expect($recreatedTodo2->description)->toBe('Review inbox');

    Carbon::setTestNow(); // Reset time
});

test('command skips lists not marked for daily refresh', function () {
    $user = User::factory()->create();
    $settings = $user->settings()->create(Setting::getDefaults());

    // Create a todo list NOT marked for daily refresh
    $todoList = $user->todoLists()->create([
        'name' => 'Project Tasks',
        'description' => 'One-time project tasks',
        'refresh_daily' => false,
    ]);

    // Create a todo from yesterday
    $yesterday = Carbon::now($settings->timezone)->subDay();
    DB::table('todos')->insert([
        'todo_list_id' => $todoList->id,
        'title' => 'Project Planning',
        'priority' => 'high',
        'completed' => false,
        'created_at' => $yesterday,
        'updated_at' => $yesterday,
    ]);

    // Run the command
    $this->artisan('app:recreate-todo-for-today --force --debug')
        ->expectsOutput('Starting todo recreation process...')
        ->assertExitCode(0);

    // Verify no new todos were created
    $todayTodos = $todoList->todos()->whereDate('created_at', Carbon::now()->format('Y-m-d'))->get();
    expect($todayTodos)->toHaveCount(0);
});

test('command runs in dry-run mode without creating todos', function () {
    $user = User::factory()->create();
    $settings = $user->settings()->create(Setting::getDefaults());

    $todoList = $user->todoLists()->create([
        'name' => 'Daily Tasks',
        'refresh_daily' => true,
    ]);

    $yesterday = Carbon::now($settings->timezone)->subDay();
    DB::table('todos')->insert([
        'todo_list_id' => $todoList->id,
        'title' => 'Test Todo',
        'priority' => 'medium',
        'completed' => false,
        'created_at' => $yesterday,
        'updated_at' => $yesterday,
    ]);

    // Run in dry-run mode
    $this->artisan('app:recreate-todo-for-today --dry-run --force')
        ->expectsOutput('Running in dry-run mode - no todos will be created')
        ->expectsOutput('Starting todo recreation process...')
        ->assertExitCode(0);

    // Verify no new todos were actually created
    $todayTodos = $todoList->todos()->whereDate('created_at', Carbon::now()->format('Y-m-d'))->get();
    expect($todayTodos)->toHaveCount(0);
});

test('command creates default settings for users without settings', function () {
    $user = User::factory()->create();

    // Ensure user has no settings initially (check the relationship directly)
    expect($user->settings()->exists())->toBeFalse();

    $todoList = $user->todoLists()->create([
        'name' => 'Daily Tasks',
        'refresh_daily' => true,
    ]);

    $yesterday = Carbon::now('UTC')->subDay();
    DB::table('todos')->insert([
        'todo_list_id' => $todoList->id,
        'title' => 'Test Todo',
        'priority' => 'medium',
        'completed' => false,
        'created_at' => $yesterday,
        'updated_at' => $yesterday,
    ]);

    // Run the command
    $this->artisan('app:recreate-todo-for-today --force')
        ->assertExitCode(0);

    // Verify settings were created with defaults
    $user->refresh();
    expect($user->settings)->not->toBeNull();
    expect($user->settings->timezone)->toBe('UTC');
    expect($user->settings->date_format)->toBe('Y-m-d');
    expect($user->settings->time_format)->toBe('H:i');
    expect($user->settings->email_notifications)->toBeTrue();
    expect($user->settings->browser_notifications)->toBeFalse();
});

test('command skips users outside processing time window', function () {
    $user = User::factory()->create();
    $settings = $user->settings()->create([
        'timezone' => 'America/New_York',
    ] + Setting::getDefaults());

    $todoList = $user->todoLists()->create([
        'name' => 'Daily Tasks',
        'refresh_daily' => true,
    ]);

    $yesterday = Carbon::now($settings->timezone)->subDay();
    DB::table('todos')->insert([
        'todo_list_id' => $todoList->id,
        'title' => 'Test Todo',
        'priority' => 'medium',
        'completed' => false,
        'created_at' => $yesterday,
        'updated_at' => $yesterday,
    ]);

    // Mock time to be outside processing window (e.g., 10 AM)
    Carbon::setTestNow(Carbon::now($settings->timezone)->setHour(10));

    // Run without force flag
    $this->artisan('app:recreate-todo-for-today --debug')
        ->expectsOutput('Starting todo recreation process...')
        ->assertExitCode(0);

    // Verify no todos were created due to time restriction
    $todayTodos = $todoList->todos()->whereDate('created_at', Carbon::now($settings->timezone)->format('Y-m-d'))->get();
    expect($todayTodos)->toHaveCount(0);

    Carbon::setTestNow(); // Reset time
});
