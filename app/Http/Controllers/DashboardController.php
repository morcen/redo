<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with comprehensive statistics and data.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $today = now()->format('Y-m-d');
        $userTimezone = $user->settings?->timezone ?? 'UTC';

        // Get today's date in user's timezone
        try {
            $todayInUserTz = Carbon::now($userTimezone)->format('Y-m-d');
        } catch (\Exception $e) {
            // Fall back to UTC if timezone is invalid
            $todayInUserTz = now()->format('Y-m-d');
        }

        $dashboardData = [
            'todayStats' => $this->getTodayStats($user, $todayInUserTz),
            'urgentTasks' => $this->getUrgentTasks($user),
            'dailyHabitsProgress' => $this->getDailyHabitsProgress($user, $todayInUserTz),
            'quickStats' => $this->getQuickStats($user),
            'recentActivity' => $this->getRecentActivity($user),
            'streakInfo' => $this->getStreakInfo($user),
            'upcomingTasks' => $this->getUpcomingTasks($user),
        ];

        return Inertia::render('Dashboard', $dashboardData);
    }

    /**
     * Get today's tasks statistics
     */
    private function getTodayStats($user, $todayInUserTz)
    {
        $todayTodos = $user->todos()
            ->whereDate('todos.created_at', $todayInUserTz)
            ->get();

        $totalToday = $todayTodos->count();
        $completedToday = $todayTodos->whereNotNull('completed_at')->count();
        $remainingToday = $totalToday - $completedToday;
        $completionRate = $totalToday > 0 ? round(($completedToday / $totalToday) * 100, 1) : 0;

        return [
            'total' => $totalToday,
            'completed' => $completedToday,
            'remaining' => $remainingToday,
            'completionRate' => $completionRate,
            'date' => $todayInUserTz,
        ];
    }

    /**
     * Get urgent tasks (due today or overdue)
     */
    private function getUrgentTasks($user)
    {
        $today = now()->format('Y-m-d');

        $urgentTodos = $user->todos()
            ->whereNull('completed_at') // Only incomplete tasks
            ->where(function ($query) use ($today) {
                $query->whereDate('due_date', '<=', $today)
                      ->whereNotNull('due_date');
            })
            ->with('todoList')
            ->orderBy('due_date', 'asc')
            ->orderBy('priority', 'desc')
            ->limit(5)
            ->get();

        return $urgentTodos->map(function ($todo) {
            $isOverdue = $todo->due_date && $todo->due_date < now()->format('Y-m-d');
            return [
                'id' => $todo->id,
                'title' => $todo->title,
                'description' => $todo->description,
                'priority' => $todo->priority,
                'due_date' => $todo->due_date,
                'is_overdue' => $isOverdue,
                'list_name' => $todo->todoList->name,
                'list_id' => $todo->todoList->id,
            ];
        });
    }

    /**
     * Get daily habits progress (from lists marked with refresh_daily)
     */
    private function getDailyHabitsProgress($user, $todayInUserTz)
    {
        $dailyLists = $user->todoLists()
            ->where('refresh_daily', true)
            ->get();

        return $dailyLists->map(function ($list) use ($todayInUserTz) {
            $todayTodos = $list->todos()->whereDate('created_at', $todayInUserTz)->get();
            $totalTodos = $todayTodos->count();
            $completedTodos = $todayTodos->whereNotNull('completed_at')->count();
            $completionRate = $totalTodos > 0 ? round(($completedTodos / $totalTodos) * 100, 1) : 0;

            return [
                'id' => $list->id,
                'name' => $list->name,
                'description' => $list->description,
                'total_todos' => $totalTodos,
                'completed_todos' => $completedTodos,
                'completion_rate' => $completionRate,
                'is_complete' => $completionRate === 100.0,
            ];
        });
    }

    /**
     * Get quick statistics overview
     */
    private function getQuickStats($user)
    {
        $allTodos = $user->todos()->get();
        $totalTodos = $allTodos->count();
        $completedTodos = $allTodos->whereNotNull('completed_at')->count();
        $totalLists = $user->todoLists()->count();
        $dailyLists = $user->todoLists()->where('refresh_daily', true)->count();

        $overallCompletionRate = $totalTodos > 0 ? round(($completedTodos / $totalTodos) * 100, 1) : 0;

        // Get this week's stats
        $weekStart = now()->startOfWeek();
        $thisWeekTodos = $user->todos()
            ->where('todos.created_at', '>=', $weekStart)
            ->get();
        $thisWeekCompleted = $thisWeekTodos->whereNotNull('completed_at')->count();

        return [
            'total_todos' => $totalTodos,
            'completed_todos' => $completedTodos,
            'total_lists' => $totalLists,
            'daily_lists' => $dailyLists,
            'overall_completion_rate' => $overallCompletionRate,
            'this_week_completed' => $thisWeekCompleted,
            'this_week_total' => $thisWeekTodos->count(),
        ];
    }

    /**
     * Get recent activity (last 5 completed tasks)
     */
    private function getRecentActivity($user)
    {
        $recentActivity = $user->todos()
            ->whereNotNull('completed_at')
            ->with('todoList')
            ->orderBy('completed_at', 'desc')
            ->limit(5)
            ->get();

        return $recentActivity->map(function ($todo) {
            return [
                'id' => $todo->id,
                'title' => $todo->title,
                'completed_at' => $todo->completed_at->format('M j, Y g:i A'),
                'list_name' => $todo->todoList->name,
                'priority' => $todo->priority,
            ];
        });
    }

    /**
     * Get streak information
     */
    private function getStreakInfo($user)
    {
        // Get daily habits lists
        $dailyListIds = $user->todoLists()
            ->where('refresh_daily', true)
            ->pluck('id');

        if ($dailyListIds->isEmpty()) {
            return [
                'current_streak' => 0,
                'best_streak' => 0,
                'streak_type' => 'daily_habits',
            ];
        }

        // Calculate current streak by checking consecutive days with 100% completion
        $currentStreak = 0;
        $bestStreak = 0;
        $tempStreak = 0;

        // Check last 30 days
        for ($i = 0; $i < 30; $i++) {
            $checkDate = now()->subDays($i)->format('Y-m-d');
            $dayTodos = Todo::whereIn('todo_list_id', $dailyListIds)
                ->whereDate('created_at', $checkDate)
                ->get();

            if ($dayTodos->isEmpty()) {
                if ($i === 0) {
                    continue;
                } // Skip today if no todos yet
                break; // Break streak if no todos on this day
            }

            $dayCompletion = $dayTodos->whereNotNull('completed_at')->count() / $dayTodos->count();

            if ($dayCompletion === 1.0) { // 100% completion
                $tempStreak++;
                if ($i === 0 || $currentStreak === 0) {
                    $currentStreak = $tempStreak;
                }
            } else {
                $bestStreak = max($bestStreak, $tempStreak);
                $tempStreak = 0;
                if ($i === 0) {
                    $currentStreak = 0;
                }
            }
        }

        $bestStreak = max($bestStreak, $tempStreak);

        return [
            'current_streak' => $currentStreak,
            'best_streak' => $bestStreak,
            'streak_type' => 'daily_habits',
        ];
    }

    /**
     * Get upcoming tasks (next 5 tasks with due dates)
     */
    private function getUpcomingTasks($user)
    {
        $today = now()->format('Y-m-d');

        $upcomingTasks = $user->todos()
            ->whereNull('completed_at')
            ->where('due_date', '>', $today)
            ->with('todoList')
            ->orderBy('due_date', 'asc')
            ->orderBy('priority', 'desc')
            ->limit(5)
            ->get();

        return $upcomingTasks->map(function ($todo) {
            $daysUntilDue = (int) now()->diffInDays($todo->due_date);
            return [
                'id' => $todo->id,
                'title' => $todo->title,
                'due_date' => $todo->due_date,
                'days_until_due' => $daysUntilDue,
                'priority' => $todo->priority,
                'list_name' => $todo->todoList->name,
                'list_id' => $todo->todoList->id,
            ];
        });
    }
}
