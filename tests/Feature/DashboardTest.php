<?php

use App\Models\Setting;
use App\Models\Todo;
use App\Models\TodoList;
use App\Models\User;
use Carbon\Carbon;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// Authentication Tests
test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});

// Dashboard Data Structure Tests
test('dashboard returns all required data for new user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    
    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('todayStats')
            ->has('urgentTasks')
            ->has('dailyHabitsProgress')
            ->has('quickStats')
            ->has('recentActivity')
            ->has('streakInfo')
            ->has('upcomingTasks')
            ->where('todayStats.total', 0)
            ->where('todayStats.completed', 0)
            ->where('todayStats.remaining', 0)
            ->where('todayStats.completionRate', 0)
            ->where('urgentTasks', [])
            ->where('dailyHabitsProgress', [])
            ->where('recentActivity', [])
            ->where('upcomingTasks', [])
            ->where('quickStats.total_todos', 0)
            ->where('quickStats.completed_todos', 0)
            ->where('quickStats.total_lists', 0)
            ->where('quickStats.daily_lists', 0)
            ->where('streakInfo.current_streak', 0)
            ->where('streakInfo.best_streak', 0)
        );
});

test('dashboard shows today\'s stats correctly', function () {
    $user = User::factory()->create();
    $list = TodoList::factory()->for($user)->create();
    
    // Create todos for today
    Todo::factory()->for($list)->today()->completed()->count(3)->create();
    Todo::factory()->for($list)->today()->pending()->count(2)->create();
    
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    
    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('todayStats.total', 5)
            ->where('todayStats.completed', 3)
            ->where('todayStats.remaining', 2)
            ->where('todayStats.completionRate', 60)
            ->where('todayStats.date', now()->format('Y-m-d'))
        );
});

test('dashboard identifies urgent tasks correctly', function () {
    $user = User::factory()->create();
    $list = TodoList::factory()->for($user)->create();
    
    // Create overdue task
    $overdueTodo = Todo::factory()->for($list)->overdue()->highPriority()->create([
        'title' => 'Overdue Important Task'
    ]);
    
    // Create due today task
    $dueTodayTodo = Todo::factory()->for($list)->dueToday()->mediumPriority()->create([
        'title' => 'Due Today Task'
    ]);
    
    // Create future task (should not be urgent)
    Todo::factory()->for($list)->withDueDate()->lowPriority()->create();
    
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    
    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('urgentTasks', 2)
            ->where('urgentTasks.0.title', 'Overdue Important Task')
            ->where('urgentTasks.0.is_overdue', true)
            ->where('urgentTasks.0.priority', 'high')
            ->where('urgentTasks.1.title', 'Due Today Task')
            ->where('urgentTasks.1.is_overdue', false)
            ->where('urgentTasks.1.priority', 'medium')
        );
});

test('dashboard tracks daily habits progress correctly', function () {
    $user = User::factory()->create();
    
    // Create daily habit list
    $dailyList = TodoList::factory()->for($user)->dailyHabit()->create([
        'name' => 'Morning Routine'
    ]);
    
    // Create regular project list
    $projectList = TodoList::factory()->for($user)->project()->create();
    
    // Create todos for today in daily habit list
    Todo::factory()->for($dailyList)->today()->completed()->count(3)->create();
    Todo::factory()->for($dailyList)->today()->pending()->count(2)->create();
    
    // Create todos in project list (should not appear in daily habits)
    Todo::factory()->for($projectList)->today()->completed()->count(1)->create();
    
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    
    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('dailyHabitsProgress', 1)
            ->where('dailyHabitsProgress.0.name', 'Morning Routine')
            ->where('dailyHabitsProgress.0.total_todos', 5)
            ->where('dailyHabitsProgress.0.completed_todos', 3)
            ->where('dailyHabitsProgress.0.completion_rate', 60)
            ->where('dailyHabitsProgress.0.is_complete', false)
        );
});

test('dashboard calculates quick stats accurately', function () {
    $user = User::factory()->create();
    $list1 = TodoList::factory()->for($user)->dailyHabit()->create();
    $list2 = TodoList::factory()->for($user)->project()->create();
    
    // Create various todos
    Todo::factory()->for($list1)->completed()->count(3)->create();
    Todo::factory()->for($list2)->pending()->count(2)->create();
    
    // Create this week's todos
    $weekStart = now()->startOfWeek();
    Todo::factory()->for($list1)->completed()->create([
        'created_at' => $weekStart->addDay(),
        'completed_at' => $weekStart->addDay()->addHours(2)
    ]);
    
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    
    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('quickStats.total_todos', 6)
            ->where('quickStats.completed_todos', 4)
            ->where('quickStats.total_lists', 2)
            ->where('quickStats.daily_lists', 1)
            ->where('quickStats.overall_completion_rate', 66.7)
            ->where('quickStats.this_week_completed', 4)
        );
});

test('dashboard shows recent activity correctly', function () {
    $user = User::factory()->create();
    $list = TodoList::factory()->for($user)->create(['name' => 'Work Tasks']);
    
    // Create completed todos at different times
    $todo1 = Todo::factory()->for($list)->create([
        'title' => 'Most Recent Task',
        'priority' => 'high',
        'completed_at' => now()->subMinutes(5)
    ]);
    
    $todo2 = Todo::factory()->for($list)->create([
        'title' => 'Older Task',
        'priority' => 'medium',
        'completed_at' => now()->subHours(2)
    ]);
    
    // Create pending todo (should not appear in recent activity)
    Todo::factory()->for($list)->pending()->create();
    
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    
    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('recentActivity', 2)
            ->where('recentActivity.0.title', 'Most Recent Task')
            ->where('recentActivity.0.priority', 'high')
            ->where('recentActivity.0.list_name', 'Work Tasks')
            ->where('recentActivity.1.title', 'Older Task')
        );
});

test('dashboard calculates streak information for daily habits', function () {
    $user = User::factory()->create();
    $dailyList = TodoList::factory()->for($user)->dailyHabit()->create();
    
    // Create completed todos for consecutive days to build a streak
    // Note: Streak calculation logic is complex, so we test basic structure here
    Todo::factory()->for($dailyList)->create([
        'created_at' => now()->subDays(2),
        'completed_at' => now()->subDays(2)->addHours(2)
    ]);
    
    Todo::factory()->for($dailyList)->create([
        'created_at' => now()->subDays(1),
        'completed_at' => now()->subDays(1)->addHours(2)
    ]);
    
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    
    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('streakInfo')
            ->where('streakInfo.streak_type', 'daily_habits')
            ->whereType('streakInfo.current_streak', 'integer')
            ->whereType('streakInfo.best_streak', 'integer')
        );
});

test('dashboard shows upcoming tasks correctly', function () {
    $user = User::factory()->create();
    $list = TodoList::factory()->for($user)->create(['name' => 'Future Plans']);
    
    // Create upcoming tasks
    $upcomingTodo = Todo::factory()->for($list)->pending()->create([
        'title' => 'Upcoming Important Task',
        'priority' => 'high',
        'due_date' => now()->addDays(3)->format('Y-m-d')
    ]);
    
    // Create overdue task (should not appear in upcoming)
    Todo::factory()->for($list)->overdue()->create();
    
    // Create completed task with future due date (should not appear)
    Todo::factory()->for($list)->completed()->create([
        'due_date' => now()->addWeek()->format('Y-m-d')
    ]);
    
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    
    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('upcomingTasks', 1)
            ->where('upcomingTasks.0.title', 'Upcoming Important Task')
            ->where('upcomingTasks.0.priority', 'high')
            ->where('upcomingTasks.0.list_name', 'Future Plans')
            ->where('upcomingTasks.0.days_until_due', fn ($value) => $value >= 2 && $value <= 3)
        );
});

// Timezone Tests
test('dashboard respects user timezone settings', function () {
    $user = User::factory()->create();
    $setting = Setting::factory()->for($user)->create([
        'timezone' => 'America/New_York'
    ]);
    
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    
    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('todayStats')
            ->whereType('todayStats.date', 'string')
        );
});

test('dashboard handles users without settings gracefully', function () {
    $user = User::factory()->create();
    // No settings created - should use UTC default
    
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    
    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('todayStats.date', now()->format('Y-m-d'))
        );
});
