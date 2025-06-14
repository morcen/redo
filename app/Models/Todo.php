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
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'completed_at',
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
        'completed_at' => 'datetime',
        'due_date' => 'date',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'completed_at' => null,
    ];

    /**
     * Get the todo list that owns the todo.
     */
    public function todoList(): BelongsTo
    {
        return $this->belongsTo(TodoList::class);
    }

    /**
     * Check if the todo is completed.
     * Accessor for backward compatibility.
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Mark the todo as completed with current timestamp.
     */
    public function markAsCompleted(): void
    {
        $this->update(['completed_at' => now()]);
    }

    /**
     * Mark the todo as not completed.
     */
    public function markAsIncomplete(): void
    {
        $this->update(['completed_at' => null]);
    }

    /**
     * Get the user that owns the todo through the todo list.
     */
    public function user()
    {
        return $this->todoList->user ?? null;
    }
}
