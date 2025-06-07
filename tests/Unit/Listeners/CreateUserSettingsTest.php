<?php

use App\Listeners\CreateUserSettings;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('creates default settings when user is registered', function () {
    $user = User::factory()->create();

    // Ensure no settings exist initially
    expect($user->settings()->exists())->toBeFalse();

    // Create the event and listener
    $event = new Registered($user);
    $listener = new CreateUserSettings;

    // Handle the event
    $listener->handle($event);

    // Verify settings were created
    expect($user->settings()->exists())->toBeTrue();

    $settings = $user->settings()->first();
    expect($settings)->toBeInstanceOf(Setting::class);
    expect($settings->timezone)->toBe('UTC');
    expect($settings->date_format)->toBe('Y-m-d');
    expect($settings->time_format)->toBe('H:i');
    expect($settings->email_notifications)->toBeTrue();
    expect($settings->browser_notifications)->toBeFalse();
    expect($settings->user_id)->toBe($user->id);
});

test('listener is called when user registers through controller', function () {
    // Test the full registration flow
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect('/dashboard');

    // Verify user was created
    $user = User::where('email', 'test@example.com')->first();
    expect($user)->not->toBeNull();

    // Verify settings were automatically created
    expect($user->settings()->exists())->toBeTrue();

    $settings = $user->settings()->first();
    expect($settings->timezone)->toBe('UTC');
    expect($settings->date_format)->toBe('Y-m-d');
    expect($settings->time_format)->toBe('H:i');
    expect($settings->email_notifications)->toBeTrue();
    expect($settings->browser_notifications)->toBeFalse();
});
