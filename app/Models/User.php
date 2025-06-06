<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the todo lists for the user.
     */
    public function todoLists(): HasMany
    {
        return $this->hasMany(TodoList::class);
    }

    /**
     * Get the todos for the user through their todo lists.
     */
    public function todos(): HasManyThrough
    {
        return $this->hasManyThrough(Todo::class, TodoList::class);
    }

    /**
     * Get the settings for the user.
     */
    public function settings(): HasOne
    {
        return $this->hasOne(Setting::class);
    }

    /**
     * Get the user's settings or create default ones.
     */
    public function getSettingsAttribute()
    {
        if (!$this->settings) {
            $this->settings()->create(Setting::getDefaults());
            $this->load('settings');
        }

        return $this->settings;
    }
}
