<?php

namespace App\Listeners;

use App\Models\Setting;
use Illuminate\Auth\Events\Registered;

class CreateUserSettings
{
    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        $user = $event->user;

        // Create default settings for the newly registered user
        $user->settings()->create(Setting::getDefaults());
    }
}
