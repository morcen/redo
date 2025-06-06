<?php

use App\Http\Controllers\TodoListController;
use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('todo-lists', TodoListController::class)->except(['create', 'show', 'edit']);
    Route::post('todo-lists/{todo_list}/duplicate', [TodoListController::class, 'duplicate'])->name('todo-lists.duplicate');
    Route::get('todo-lists/{todo_list}/todos', [TodoListController::class, 'todos'])->name('todo-lists.todos');
    Route::resource('todos', TodoController::class)->except(['create', 'show', 'edit']);

    // Test route for debugging date filtering
    Route::get('test-dates', function () {
        $user = Auth::user();
        $todos = \App\Models\Todo::where('user_id', $user->id)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'available_dates' => $todos->pluck('date'),
            'todos_by_date' => $todos
        ]);
    });


});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
