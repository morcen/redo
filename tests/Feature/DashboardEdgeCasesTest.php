<?php

use App\Models\Setting;
use App\Models\Todo;
use App\Models\TodoList;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// Edge Cases Tests
test('dashboard handles user with only empty lists', function () {
    $user = User::factory()->create();

    // Create empty lists
    TodoList::factory()->for($user)->dailyHabit()->create();
    TodoList::factory()->for($user)->project()->create();

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response->assertStatus(200)
        ->assertInertia(
            fn ($page) => $page
            ->component('Dashboard')
            ->where('todayStats.total', 0)
            ->where('todayStats.completionRate', 0)
            ->where('urgentTasks', [])
            ->where('dailyHabitsProgress.0.total_todos', 0)
            ->where('dailyHabitsProgress.0.completion_rate', 0)
            ->where('quickStats.total_lists', 2)
            ->where('quickStats.daily_lists', 1)
        );
});

test('dashboard handles user with only completed todos', function () {
    $user = User::factory()->create();
    $list = TodoList::factory()->for($user)->create();

    // All todos completed
    Todo::factory()->for($list)->today()->completed()->count(5)->create();

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response->assertStatus(200)
        ->assertInertia(
            fn ($page) => $page
            ->component('Dashboard')
            ->where('todayStats.total', 5)
            ->where('todayStats.completed', 5)
            ->where('todayStats.remaining', 0)
            ->where('todayStats.completionRate', 100)
            ->where('urgentTasks', []) // No urgent tasks since all completed
        );
});

test('dashboard handles user with many overdue tasks', function () {
    $user = User::factory()->create();
    $list = TodoList::factory()->for($user)->create();

    // Create 10 overdue tasks, but dashboard should limit to 5
    Todo::factory()->for($list)->overdue()->highPriority()->count(10)->create();

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response->assertStatus(200)
        ->assertInertia(
            fn ($page) => $page
            ->component('Dashboard')
            ->has('urgentTasks', 5) // Should be limited to 5
            ->where('urgentTasks.0.is_overdue', true)
            ->where('urgentTasks.4.is_overdue', true)
        );
});

test('dashboard handles todos with null due dates correctly', function () {
    $user = User::factory()->create();
    $list = TodoList::factory()->for($user)->create();

    // Mix of todos with and without due dates
    Todo::factory()->for($list)->today()->pending()->create(['due_date' => null]);
    Todo::factory()->for($list)->overdue()->create();
    Todo::factory()->for($list)->dueToday()->create();

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response->assertStatus(200)
        ->assertInertia(
            fn ($page) => $page
            ->component('Dashboard')
            ->has('urgentTasks', 2) // Only the overdue and due today
            ->has('todayStats')
            ->has('upcomingTasks')
        );
});

test('dashboard handles extreme timezone offsets', function () {
    $user = User::factory()->create();

    // Test with Pacific/Kiritimati (UTC+14)
    Setting::factory()->for($user)->create(['timezone' => 'Pacific/Kiritimati']);

    $list = TodoList::factory()->for($user)->dailyHabit()->create();
    Todo::factory()->for($list)->today()->pending()->create();

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response->assertStatus(200)
        ->assertInertia(
            fn ($page) => $page
            ->component('Dashboard')
            ->has('todayStats')
            ->whereType('todayStats.date', 'string')
        );
});

test('dashboard handles invalid timezone gracefully', function () {
    $user = User::factory()->create();

    // Create setting with invalid timezone
    Setting::factory()->for($user)->create(['timezone' => 'Invalid/Timezone']);

    $this->actingAs($user);

    // Should not throw exception and fall back to UTC
    $response = $this->get('/dashboard');

    $response->assertStatus(200);
});

test('dashboard handles very large datasets efficiently', function () {
    $user = User::factory()->create();
    $dailyList = TodoList::factory()->for($user)->dailyHabit()->create();
    $projectList = TodoList::factory()->for($user)->project()->create();

    // Create large dataset
    Todo::factory()->for($dailyList)->today()->completed()->count(50)->create();
    Todo::factory()->for($dailyList)->today()->pending()->count(25)->create();
    Todo::factory()->for($projectList)->completed()->count(100)->create();
    Todo::factory()->for($projectList)->overdue()->count(20)->create();

    $this->actingAs($user);

    $startTime = microtime(true);
    $response = $this->get('/dashboard');
    $executionTime = microtime(true) - $startTime;

    // Should complete within reasonable time (2 seconds)
    expect($executionTime)->toBeLessThan(2.0);

    $response->assertStatus(200)
        ->assertInertia(
            fn ($page) => $page
            ->component('Dashboard')
            ->whereType('todayStats.total', 'integer')
            ->whereType('todayStats.completed', 'integer')
            ->has('urgentTasks', 5) // Limited to 5 despite having 20 overdue
            ->has('recentActivity', 5) // Limited to 5 despite having many completed
        );
});

// Streak Calculation Edge Cases
test('dashboard handles streak calculation with missing days', function () {
    $user = User::factory()->create();
    $dailyList = TodoList::factory()->for($user)->dailyHabit()->create();

    // Create todos with gaps in days
    Todo::factory()->for($dailyList)->create([
        'created_at' => now()->subDays(5),
        'completed_at' => now()->subDays(5)->addHour(),
    ]);

    // Gap of 3 days

    Todo::factory()->for($dailyList)->create([
        'created_at' => now()->subDays(1),
        'completed_at' => now()->subDays(1)->addHour(),
    ]);

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response->assertStatus(200)
        ->assertInertia(
            fn ($page) => $page
            ->component('Dashboard')
            ->has('streakInfo')
            ->whereType('streakInfo.current_streak', 'integer')
            ->whereType('streakInfo.best_streak', 'integer')
        );
});

test('dashboard handles user with no daily habits for streak', function () {
    $user = User::factory()->create();
    $projectList = TodoList::factory()->for($user)->project()->create();

    // Only project todos, no daily habits
    Todo::factory()->for($projectList)->completed()->count(10)->create();

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response->assertStatus(200)
        ->assertInertia(
            fn ($page) => $page
            ->component('Dashboard')
            ->where('streakInfo.current_streak', 0)
            ->where('streakInfo.best_streak', 0)
            ->where('dailyHabitsProgress', [])
        );
});

// Data Consistency Tests
test('dashboard data is consistent across multiple calls', function () {
    $user = User::factory()->create();
    $list = TodoList::factory()->for($user)->dailyHabit()->create();

    Todo::factory()->for($list)->today()->completed()->count(3)->create();
    Todo::factory()->for($list)->today()->pending()->count(2)->create();

    $this->actingAs($user);

    // Make multiple calls
    $response1 = $this->get('/dashboard');
    $response2 = $this->get('/dashboard');

    // Both should have same data structure and values
    $response1->assertStatus(200);
    $response2->assertStatus(200);

    // Extract the data from both responses (simplified check)
    $response1->assertInertia(
        fn ($page) => $page
        ->where('todayStats.total', 5)
        ->where('todayStats.completed', 3)
    );

    $response2->assertInertia(
        fn ($page) => $page
        ->where('todayStats.total', 5)
        ->where('todayStats.completed', 3)
    );
});

// Performance Edge Cases
test('dashboard handles user switching timezones', function () {
    $user = User::factory()->create();
    $setting = Setting::factory()->for($user)->create(['timezone' => 'UTC']);
    $list = TodoList::factory()->for($user)->create();

    // Create todos in UTC
    Todo::factory()->for($list)->today()->completed()->create();

    $this->actingAs($user);
    $response1 = $this->get('/dashboard');
    $response1->assertStatus(200);

    // Change timezone
    $setting->update(['timezone' => 'America/New_York']);

    $response2 = $this->get('/dashboard');
    $response2->assertStatus(200);

    // Should handle timezone change gracefully
    $response2->assertInertia(
        fn ($page) => $page
        ->has('todayStats')
        ->has('quickStats')
    );
});

test('dashboard handles concurrent user data modifications', function () {
    $user = User::factory()->create();
    $list = TodoList::factory()->for($user)->create();

    // Initial state
    Todo::factory()->for($list)->today()->pending()->count(3)->create();

    $this->actingAs($user);

    // Simulate data change during request processing
    $response = $this->get('/dashboard');

    // Add more todos after initial load
    Todo::factory()->for($list)->today()->completed()->count(2)->create();

    // First request should still be valid
    $response->assertStatus(200)
        ->assertInertia(
            fn ($page) => $page
            ->where('todayStats.total', 3)
            ->where('todayStats.completed', 0)
        );

    // Second request should show updated data
    $response2 = $this->get('/dashboard');
    $response2->assertStatus(200)
        ->assertInertia(
            fn ($page) => $page
            ->where('todayStats.total', 5)
            ->where('todayStats.completed', 2)
        );
});

// Boundary Tests
test('dashboard handles todos at exact midnight boundaries', function () {
    $user = User::factory()->create();
    $list = TodoList::factory()->for($user)->create();

    // Create todo at exactly midnight
    $midnight = now()->startOfDay();
    Todo::factory()->for($list)->create([
        'created_at' => $midnight,
        'completed_at' => null,
    ]);

    // Create todo one second before midnight
    $beforeMidnight = $midnight->copy()->subSecond();
    Todo::factory()->for($list)->create([
        'created_at' => $beforeMidnight,
        'completed_at' => null,
    ]);

    $this->actingAs($user);

    $response = $this->get('/dashboard');

    $response->assertStatus(200)
        ->assertInertia(
            fn ($page) => $page
            ->where('todayStats.total', 1) // Only the midnight todo should count for today
            ->has('quickStats')
        );
});
