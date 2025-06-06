<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\Todo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecreateTodoForToday extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:recreate-todo-for-today {--dry-run : Show what would be done without actually creating todos} {--force : Force execution regardless of time} {--debug : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recreate todos from yesterday for users who have crossed into a new day based on their timezone, only for lists marked with refresh_daily';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $isForced = $this->option('force');
        $isDebug = $this->option('debug');

        if ($isDryRun) {
            $this->info('Running in dry-run mode - no todos will be created');
        }

        if ($isForced) {
            $this->info('Force mode enabled - ignoring time restrictions');
        }

        $this->info('Starting todo recreation process...');

        // Get all users with their settings
        $users = User::with('settings')->get();

        if ($isDebug) {
            $this->info("Found {$users->count()} users to process");
        }

        $processedUsers = 0;
        $totalTodosCreated = 0;
        $skippedUsers = 0;

        foreach ($users as $user) {
            $result = $this->processUserTodos($user, $isDryRun, $isForced, $isDebug);

            if ($result['processed']) {
                $processedUsers++;
                $totalTodosCreated += $result['todos_created'];

                $this->line("✓ User {$user->name} ({$user->email}): {$result['todos_created']} todos recreated");
            } elseif ($isDebug && $result['reason']) {
                $skippedUsers++;
                $this->line("- User {$user->name} ({$user->email}): {$result['reason']}");
            }
        }

        $this->info('Process completed!');
        $this->info("Users processed: {$processedUsers}");
        if ($isDebug) {
            $this->info("Users skipped: {$skippedUsers}");
        }
        $this->info('Total todos '.($isDryRun ? 'would be created' : 'created').": {$totalTodosCreated}");
    }

    /**
     * Process todos for a single user
     */
    private function processUserTodos(User $user, bool $isDryRun, bool $isForced, bool $isDebug): array
    {
        // Get user's timezone settings
        $settings = $user->settings()->first();
        if (! $settings) {
            // Create default settings if they don't exist
            $settings = $user->settings()->create(Setting::getDefaults());
        }

        $userTimezone = $settings->timezone ?? 'UTC';

        try {
            // Get current time in user's timezone
            $nowInUserTz = Carbon::now($userTimezone);
            $yesterdayInUserTz = $nowInUserTz->copy()->subDay();

            // Check if it's a new day for this user (between 00:00 and 01:00 in their timezone)
            // This ensures we only process users who have recently crossed into a new day
            $currentHour = $nowInUserTz->hour;

            // Only process if it's early in the day (first hour) to avoid multiple executions
            if (! $isForced && $currentHour > 1) {
                return [
                    'processed' => false,
                    'todos_created' => 0,
                    'reason' => "Not in processing window (current hour: {$currentHour} in {$userTimezone})",
                ];
            }

            // Get todos from yesterday for this user, only from lists marked for daily refresh
            $yesterdayTodos = Todo::where('refresh_daily', true)
                ->with('todoList')
                ->join('todo_lists', 'todo_lists.id', '=', 'todos.todo_list_id')
                ->whereDate('todos.created_at', $yesterdayInUserTz->format('Y-m-d'))
                ->where('user_id', $user->id)
                ->get();

            if ($yesterdayTodos->isEmpty()) {
                return [
                    'processed' => false,
                    'todos_created' => 0,
                    'reason' => "No todos from yesterday ({$yesterdayInUserTz->format('Y-m-d')}) in lists marked for daily refresh",
                ];
            }

            if ($isDebug) {
                $this->line("  Found {$yesterdayTodos->count()} todos from {$yesterdayInUserTz->format('Y-m-d')} for {$user->name} (from lists marked for daily refresh)");
            }

            $todosCreated = 0;

            if (! $isDryRun) {
                // Create new todos for today based on yesterday's ones
                DB::transaction(function () use ($yesterdayTodos, &$todosCreated) {
                    foreach ($yesterdayTodos as $todo) {
                        Todo::create([
                            'todo_list_id' => $todo->todo_list_id,
                            'title' => $todo->title,
                            'description' => $todo->description,
                            'priority' => $todo->priority,
                            'due_date' => $todo->due_date,
                            'completed' => false,
                        ]);
                        $todosCreated++;
                    }
                });
            } else {
                $todosCreated = $yesterdayTodos->count();
            }

            return [
                'processed' => true,
                'todos_created' => $todosCreated,
                'reason' => null,
            ];

        } catch (\Exception $e) {
            $this->error("Error processing user {$user->email}: ".$e->getMessage());

            return [
                'processed' => false,
                'todos_created' => 0,
                'reason' => 'Error: '.$e->getMessage(),
            ];
        }
    }
}
