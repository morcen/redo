<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TodoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        if ($user) {
            // Create a default todo list
            $todoList = $user->todoLists()->create([
                'name' => 'My Tasks',
                'description' => 'Default task list',
                'refresh_daily' => true,
            ]);
            $todoList->todos()->create([
                'title' => 'Complete Laravel project',
                'description' => 'Finish building the todo application with all features',
                'priority' => 'high',
                'due_date' => now()->addDays(3),
                'completed' => false,
            ]);

            $todoList->todos()->create([
                'title' => 'Review code',
                'description' => 'Review the todo application code for best practices',
                'priority' => 'medium',
                'due_date' => now()->addDays(1),
                'completed' => false,
            ]);

            $todoList->todos()->create([
                'title' => 'Write documentation',
                'description' => 'Document the todo application features and usage',
                'priority' => 'low',
                'due_date' => now()->addWeek(),
                'completed' => true,
            ]);

            $todoList->todos()->create([
                'title' => 'Setup testing',
                'description' => 'Add unit and feature tests for the todo application',
                'priority' => 'medium',
                'completed' => false,
            ]);

            // Create a permanent list that doesn't refresh daily
            $permanentList = $user->todoLists()->create([
                'name' => 'Long-term Goals',
                'description' => 'Goals and tasks that persist across days',
                'refresh_daily' => false,
            ]);

            $permanentList->todos()->create([
                'title' => 'Learn a new programming language',
                'description' => 'Pick up a new language like Rust or Go',
                'priority' => 'low',
                'due_date' => now()->addMonths(3),
                'completed' => false,
            ]);

            $permanentList->todos()->create([
                'title' => 'Read 12 books this year',
                'description' => 'Personal development through reading',
                'priority' => 'medium',
                'completed' => false,
            ]);

            // Create a fully completed list
            $completedList = $user->todoLists()->create([
                'name' => 'Completed Project',
                'description' => 'A project that has been fully completed',
                'refresh_daily' => false,
            ]);

            $completedList->todos()->create([
                'title' => 'Design mockups',
                'description' => 'Create UI/UX mockups for the project',
                'priority' => 'high',
                'completed' => true,
            ]);

            $completedList->todos()->create([
                'title' => 'Implement backend',
                'description' => 'Build the API and database structure',
                'priority' => 'high',
                'completed' => true,
            ]);

            $completedList->todos()->create([
                'title' => 'Build frontend',
                'description' => 'Create the user interface',
                'priority' => 'medium',
                'completed' => true,
            ]);

            // Create a list with mixed completion (75% complete)
            $mixedList = $user->todoLists()->create([
                'name' => 'Website Redesign',
                'description' => 'Redesigning the company website',
                'refresh_daily' => false,
            ]);

            $mixedList->todos()->create([
                'title' => 'Research competitors',
                'description' => 'Analyze competitor websites',
                'priority' => 'medium',
                'completed' => true,
            ]);

            $mixedList->todos()->create([
                'title' => 'Create wireframes',
                'description' => 'Design the website structure',
                'priority' => 'high',
                'completed' => true,
            ]);

            $mixedList->todos()->create([
                'title' => 'Choose color scheme',
                'description' => 'Select colors for the new design',
                'priority' => 'medium',
                'completed' => true,
            ]);

            $mixedList->todos()->create([
                'title' => 'Implement responsive design',
                'description' => 'Make the website mobile-friendly',
                'priority' => 'high',
                'completed' => false,
            ]);

            // Create an empty list to show 0% completion
            $emptyList = $user->todoLists()->create([
                'name' => 'Future Ideas',
                'description' => 'Ideas for future projects',
                'refresh_daily' => false,
            ]);
        }
    }
}
