<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Models\TodoList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Auth::user()->todos();

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
        $query->whereDate('todos.created_at', $selectedDate);

        $todos = $query
            ->orderBy('todos.created_at', 'desc')
            ->orderByRaw('todos.completed_at IS NULL DESC')
            ->get();

        // Get available dates for the dropdown
        $availableDates = Auth::user()->todos()
            ->selectRaw('DATE(todos.created_at) as date')
            ->distinct()
            ->orderBy('date', 'desc')
            ->pluck('date')
            ->values();

        $todoLists = Auth::user()->todoLists()->orderBy('name')->get();

        return Inertia::render('Todos/Index', [
            'todos' => $todos,
            'availableDates' => $availableDates,
            'selectedDate' => $selectedDate,
            'todoLists' => $todoLists,
            'filters' => $request->only(['completed', 'priority', 'search', 'date']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
            'todo_list_id' => 'required|exists:todo_lists,id',
        ]);

        // Ensure the todo list belongs to the authenticated user
        $todoList = TodoList::findOrFail($validated['todo_list_id']);
        if ($todoList->user_id !== Auth::id()) {
            abort(403);
        }

        $todoList->todos()->create($validated);

        return redirect()->back()->with('success', 'Todo created successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Todo $todo)
    {
        // Ensure the todo belongs to the authenticated user through the todo list
        if ($todo->todoList->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'boolean',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
            'todo_list_id' => 'required|exists:todo_lists,id',
        ]);

        // Convert boolean completed to timestamp
        if (isset($validated['completed'])) {
            $validated['completed_at'] = $validated['completed'] ? now() : null;
            unset($validated['completed']); // Remove the boolean field
        }

        // Ensure the new todo list also belongs to the authenticated user
        $newTodoList = TodoList::findOrFail($validated['todo_list_id']);
        if ($newTodoList->user_id !== Auth::id()) {
            abort(403);
        }

        $todo->update($validated);

        return redirect()->back()->with('success', 'Todo updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Todo $todo)
    {
        // Ensure the todo belongs to the authenticated user through the todo list
        if ($todo->todoList->user_id !== Auth::id()) {
            abort(403);
        }

        $todo->delete();

        return redirect()->back()->with('success', 'Todo deleted successfully!');
    }
}
