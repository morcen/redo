<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TodoList extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'refresh_daily',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'refresh_daily' => 'boolean',
    ];

    /**
     * Get the user that owns the todo list.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the todos for the list.
     */
    public function todos(): HasMany
    {
        return $this->hasMany(Todo::class);
    }

    /**
     * Get the completion percentage for this todo list.
     */
    public function getCompletionPercentageAttribute(): float
    {
        $totalTodos = $this->todos()->count();

        if ($totalTodos === 0) {
            return 0.0;
        }

        $completedTodos = $this->todos()->where('completed', true)->count();

        return round(($completedTodos / $totalTodos) * 100, 1);
    }

    /**
     * Get the total number of todos in this list.
     */
    public function getTotalTodosAttribute(): int
    {
        return $this->todos()->count();
    }

    /**
     * Get the number of completed todos in this list.
     */
    public function getCompletedTodosAttribute(): int
    {
        return $this->todos()->where('completed', true)->count();
    }
}
