<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('general settings page can be rendered', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/settings/general');

    $response->assertOk();
});

test('user can view general settings with default values', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/settings/general');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('settings/General')
        ->has('settings')
        ->has('timezones')
        ->where('settings.timezone', 'UTC')
        ->where('settings.date_format', 'Y-m-d')
        ->where('settings.time_format', 'H:i')
        ->where('settings.email_notifications', true)
        ->where('settings.browser_notifications', false)
    );
});

test('user can update general settings', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch('/settings/general', [
        'timezone' => 'America/New_York',
        'date_format' => 'm/d/Y',
        'time_format' => 'g:i A',
        'email_notifications' => false,
        'browser_notifications' => true,
    ]);

    $response->assertRedirect('/settings/general');
    $response->assertSessionHas('status', 'Settings updated successfully.');

    $user->refresh();
    $settings = $user->settings;
    
    expect($settings->timezone)->toBe('America/New_York');
    expect($settings->date_format)->toBe('m/d/Y');
    expect($settings->time_format)->toBe('g:i A');
    expect($settings->email_notifications)->toBeFalse();
    expect($settings->browser_notifications)->toBeTrue();
});

test('user can update timezone via ajax', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/settings/timezone', [
        'timezone' => 'Europe/London',
    ]);

    $response->assertOk();
    $response->assertJson(['success' => true]);

    $user->refresh();
    expect($user->settings->timezone)->toBe('Europe/London');
});

test('settings validation works correctly', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->patch('/settings/general', [
        'timezone' => '', // Required field
        'date_format' => '', // Required field
        'time_format' => '', // Required field
        'email_notifications' => 'invalid', // Should be boolean
        'browser_notifications' => 'invalid', // Should be boolean
    ]);

    $response->assertSessionHasErrors([
        'timezone',
        'date_format',
        'time_format',
    ]);
});

test('timezone validation works for ajax endpoint', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withHeaders(['Accept' => 'application/json'])
        ->post('/settings/timezone', [
            'timezone' => '', // Required field
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['timezone']);
});

test('settings are created with defaults when first accessed', function () {
    $user = User::factory()->create();
    
    // Ensure user has no settings initially
    expect($user->settings()->exists())->toBeFalse();

    // Access settings page
    $response = $this->actingAs($user)->get('/settings/general');

    $response->assertOk();
    
    // Verify settings were created with defaults
    $user->refresh();
    expect($user->settings()->exists())->toBeTrue();
    
    $settings = $user->settings;
    expect($settings->timezone)->toBe('UTC');
    expect($settings->date_format)->toBe('Y-m-d');
    expect($settings->time_format)->toBe('H:i');
    expect($settings->email_notifications)->toBeTrue();
    expect($settings->browser_notifications)->toBeFalse();
});

test('existing settings are preserved when updating', function () {
    $user = User::factory()->create();
    
    // Create initial settings
    $user->settings()->create([
        'timezone' => 'America/Chicago',
        'date_format' => 'Y-m-d',
        'time_format' => 'H:i',
        'email_notifications' => true,
        'browser_notifications' => false,
    ]);

    // Update only timezone
    $response = $this->actingAs($user)->patch('/settings/general', [
        'timezone' => 'Europe/Paris',
        'date_format' => 'Y-m-d',
        'time_format' => 'H:i',
        'email_notifications' => true,
        'browser_notifications' => false,
    ]);

    $response->assertRedirect('/settings/general');
    
    $user->refresh();
    $settings = $user->settings;
    
    expect($settings->timezone)->toBe('Europe/Paris');
    expect($settings->date_format)->toBe('Y-m-d');
    expect($settings->time_format)->toBe('H:i');
    expect($settings->email_notifications)->toBeTrue();
    expect($settings->browser_notifications)->toBeFalse();
});

test('timezones list is provided to frontend', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/settings/general');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('settings/General')
        ->has('timezones')
        ->where('timezones.UTC', 'UTC')
        ->where('timezones.America/New_York', 'America/New_York')
        ->where('timezones.Europe/London', 'Europe/London')
    );
});

test('guest cannot access settings pages', function () {
    $response = $this->get('/settings/general');
    $response->assertRedirect('/login');

    $response = $this->patch('/settings/general', []);
    $response->assertRedirect('/login');

    $response = $this->post('/settings/timezone', []);
    $response->assertRedirect('/login');
});
