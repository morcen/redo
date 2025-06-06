<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'timezone',
        'date_format',
        'time_format',
        'email_notifications',
        'browser_notifications',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_notifications' => 'boolean',
        'browser_notifications' => 'boolean',
    ];

    /**
     * Get the user that owns the setting.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the default settings for a user.
     *
     * @return array<string, mixed>
     */
    public static function getDefaults(): array
    {
        return [
            'timezone' => 'UTC',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'email_notifications' => true,
            'browser_notifications' => false,
        ];
    }
}
