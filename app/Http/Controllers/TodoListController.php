<?php

namespace App\Http\Controllers;

use App\Models\TodoList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TodoListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get the selected date, default to today
        $selectedDate = $request->get('date', now()->format('Y-m-d'));

        $lists = Auth::user()->todoLists()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($list) use ($selectedDate) {
                // Calculate completion stats based on selected date
                $totalTodos = $list->todos()->whereDate('created_at', $selectedDate)->count();
                $completedTodos = $list->todos()
                    ->whereDate('created_at', $selectedDate)
                    ->whereNotNull('completed_at')
                    ->count();

                $list->filtered_completion_percentage = $totalTodos > 0
                    ? round(($completedTodos / $totalTodos) * 100, 1)
                    : 0.0;
                $list->filtered_total_todos = $totalTodos;
                $list->filtered_completed_todos = $completedTodos;

                return $list;
            });

        // Get available dates across all user's todo lists
        $availableDates = Auth::user()->todoLists()
            ->with('todos')
            ->get()
            ->flatMap(function ($list) {
                return $list->todos->pluck('created_at');
            })
            ->map(function ($date) {
                return $date->format('Y-m-d');
            })
            ->unique()
            ->sort()
            ->values();

        return Inertia::render('TodoLists/Index', [
            'lists' => $lists,
            'availableDates' => $availableDates,
            'selectedDate' => $selectedDate,
            'filters' => $request->only(['date']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'refresh_daily' => 'boolean',
        ]);

        Auth::user()->todoLists()->create($validated);

        return redirect()->back()->with('success', 'List created successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TodoList $todoList)
    {
        // Ensure the todo list belongs to the authenticated user
        if ($todoList->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'refresh_daily' => 'boolean',
        ]);

        $todoList->update($validated);

        return redirect()->back()->with('success', 'List updated successfully!');
    }

    /**
     * Duplicate the specified resource.
     */
    public function duplicate($id)
    {
        // Find the todo list by ID
        $todoList = TodoList::findOrFail($id);

        // Ensure the todo list belongs to the authenticated user
        if ($todoList->user_id !== Auth::id()) {
            abort(403);
        }

        // Create a new todo list with the same attributes
        $duplicatedList = Auth::user()->todoLists()->create([
            'name' => $todoList->name.' (Copy)',
            'description' => $todoList->description,
            'refresh_daily' => $todoList->refresh_daily,
        ]);

        // Duplicate all todos from the original list
        $todos = $todoList->todos;
        foreach ($todos as $todo) {
            $duplicatedList->todos()->create([
                'title' => $todo->title,
                'description' => $todo->description,
                'completed' => null, // Reset completion status for duplicated todos
                'priority' => $todo->priority,
                'due_date' => $todo->due_date,
            ]);
        }

        return redirect()->back()->with('success', 'List duplicated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TodoList $todoList)
    {
        // Ensure the todo belongs to the authenticated user
        if ($todoList->user_id !== Auth::id()) {
            abort(403);
        }

        $todoList->delete();

        return redirect()->back()->with('success', 'List deleted successfully!');
    }

    /**
     * Display todos for a specific list.
     */
    public function todos(Request $request, TodoList $todoList)
    {
        // Ensure the todo list belongs to the authenticated user
        if ($todoList->user_id !== Auth::id()) {
            abort(403);
        }

        $query = $todoList->todos();

        // Filter by completion status
        if ($request->has('completed')) {
            if ($request->boolean('completed')) {
                $query->whereNotNull('completed_at');
            } else {
                $query->whereNull('completed_at');
            }
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->get('priority'));
        }

        // Search by title or description
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by date (creation date)
        $selectedDate = $request->get('date', now()->format('Y-m-d'));
        $query->whereDate('created_at', $selectedDate);

        $todos = $query
            ->orderBy('completed_at', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Get available dates for the dropdown (for this specific list)
        $availableDates = $todoList->todos()
            ->selectRaw('DATE(created_at) as date')
            ->distinct()
            ->orderBy('date', 'desc')
            ->pluck('date')
            ->values();

        $todoLists = Auth::user()->todoLists()->orderBy('name')->get();

        // Add completion statistics to the current list based on selected date
        $totalTodos = $todoList->todos()->whereDate('created_at', $selectedDate)->count();
        $completedTodos = $todoList->todos()
            ->whereDate('created_at', $selectedDate)
            ->whereNotNull('completed_at')
            ->count();
        $completionPercentage = $totalTodos > 0
            ? round(($completedTodos / $totalTodos) * 100, 1)
            : 0.0;

        $todoList->filtered_total_todos = $totalTodos;
        $todoList->filtered_completed_todos = $completedTodos;
        $todoList->filtered_completion_percentage = $completionPercentage;

        return Inertia::render('TodoLists/Todos', [
            'list' => $todoList,
            'todos' => $todos,
            'availableDates' => $availableDates,
            'selectedDate' => $selectedDate,
            'todoLists' => $todoLists,
            'filters' => $request->only(['completed', 'priority', 'search', 'date']),
        ]);
    }
}
