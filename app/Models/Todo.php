<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Todo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * TODO: add order_number
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'completed',
        'priority',
        'due_date',
        'todo_list_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'completed' => 'boolean',
        'due_date' => 'date',
    ];

    /**
     * Get the todo list that owns the todo.
     */
    public function todoList(): BelongsTo
    {
        return $this->belongsTo(TodoList::class);
    }

    /**
     * Get the user that owns the todo through the todo list.
     */
    public function user()
    {
        return $this->todoList->user ?? null;
    }
}
