<?php

use App\Models\Setting;
use App\Models\Todo;
use App\Models\TodoList;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('user has many todo lists', function () {
    $user = User::factory()->create();
    $todoList1 = $user->todoLists()->create(['name' => 'Work Tasks']);
    $todoList2 = $user->todoLists()->create(['name' => 'Personal Tasks']);

    expect($user->todoLists)->toHaveCount(2);
    expect($user->todoLists->first())->toBeInstanceOf(TodoList::class);
    expect($user->todoLists->pluck('name')->toArray())->toContain('Work Tasks', 'Personal Tasks');
});

test('user has many todos through todo lists', function () {
    $user = User::factory()->create();
    $todoList1 = $user->todoLists()->create(['name' => 'Work Tasks']);
    $todoList2 = $user->todoLists()->create(['name' => 'Personal Tasks']);

    $todo1 = $todoList1->todos()->create(['title' => 'Work Todo', 'priority' => 'high']);
    $todo2 = $todoList2->todos()->create(['title' => 'Personal Todo', 'priority' => 'medium']);
    $todo3 = $todoList1->todos()->create(['title' => 'Another Work Todo', 'priority' => 'low']);

    $userTodos = $user->todos;
    
    expect($userTodos)->toHaveCount(3);
    expect($userTodos->first())->toBeInstanceOf(Todo::class);
    expect($userTodos->pluck('title')->toArray())->toContain('Work Todo', 'Personal Todo', 'Another Work Todo');
});

test('user has one setting relationship', function () {
    $user = User::factory()->create();
    $setting = $user->settings()->create(Setting::getDefaults());

    expect($user->settings()->first())->toBeInstanceOf(Setting::class);
    expect($user->settings()->first()->id)->toBe($setting->id);
});

test('user can be created with factory', function () {
    $user = User::factory()->create();

    expect($user)->toBeInstanceOf(User::class);
    expect($user->name)->not->toBeNull();
    expect($user->email)->not->toBeNull();
    expect($user->email_verified_at)->not->toBeNull();
    expect($user->password)->not->toBeNull();
});

test('user factory can create unverified users', function () {
    $user = User::factory()->unverified()->create();

    expect($user->email_verified_at)->toBeNull();
});

test('user has correct fillable attributes', function () {
    $user = new User();
    
    $expectedFillable = [
        'name',
        'email',
        'password',
    ];

    expect($user->getFillable())->toBe($expectedFillable);
});

test('user has correct hidden attributes', function () {
    $user = new User();
    
    $expectedHidden = [
        'password',
        'remember_token',
    ];

    expect($user->getHidden())->toBe($expectedHidden);
});

test('user casts attributes correctly', function () {
    $user = User::factory()->create();

    expect($user->email_verified_at)->toBeInstanceOf(DateTime::class);
    expect($user->password)->toBeString();
});

test('user can have multiple todo lists with different names', function () {
    $user = User::factory()->create();
    
    $listNames = ['Work', 'Personal', 'Shopping', 'Health', 'Learning'];
    
    foreach ($listNames as $name) {
        $user->todoLists()->create(['name' => $name]);
    }
    
    expect($user->todoLists)->toHaveCount(5);
    expect($user->todoLists->pluck('name')->toArray())->toBe($listNames);
});

test('user todos relationship includes todos from all lists', function () {
    $user = User::factory()->create();
    $workList = $user->todoLists()->create(['name' => 'Work']);
    $personalList = $user->todoLists()->create(['name' => 'Personal']);
    
    // Create todos in different lists
    $workTodo1 = $workList->todos()->create(['title' => 'Work Task 1', 'priority' => 'high']);
    $workTodo2 = $workList->todos()->create(['title' => 'Work Task 2', 'priority' => 'medium']);
    $personalTodo = $personalList->todos()->create(['title' => 'Personal Task', 'priority' => 'low']);
    
    $allUserTodos = $user->todos;
    
    expect($allUserTodos)->toHaveCount(3);
    expect($allUserTodos->contains($workTodo1))->toBeTrue();
    expect($allUserTodos->contains($workTodo2))->toBeTrue();
    expect($allUserTodos->contains($personalTodo))->toBeTrue();
});

test('deleting user cascades to todo lists and todos', function () {
    $user = User::factory()->create();
    $todoList = $user->todoLists()->create(['name' => 'Test List']);
    $todo = $todoList->todos()->create(['title' => 'Test Todo', 'priority' => 'medium']);
    $settings = $user->settings()->create(Setting::getDefaults());
    
    $userId = $user->id;
    $todoListId = $todoList->id;
    $todoId = $todo->id;
    $settingsId = $settings->id;
    
    // Delete the user
    $user->delete();
    
    // Verify all related records are deleted
    expect(User::find($userId))->toBeNull();
    expect(TodoList::find($todoListId))->toBeNull();
    expect(Todo::find($todoId))->toBeNull();
    expect(Setting::find($settingsId))->toBeNull();
});

test('user email must be unique', function () {
    $email = 'test@example.com';
    
    User::factory()->create(['email' => $email]);
    
    expect(fn () => User::factory()->create(['email' => $email]))
        ->toThrow(Exception::class);
});
