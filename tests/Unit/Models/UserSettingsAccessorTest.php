<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('user model no longer has settings accessor method', function () {
    $user = User::factory()->create();
    
    // Verify that the getSettingsAttribute method no longer exists
    expect(method_exists($user, 'getSettingsAttribute'))->toBeFalse();
    
    // Verify that accessing settings property directly returns null when no settings exist
    // (since we removed the accessor that would create them automatically)
    expect($user->settings)->toBeNull();
});

test('user settings relationship still works correctly', function () {
    $user = User::factory()->create();
    
    // Create settings manually
    $settings = $user->settings()->create([
        'timezone' => 'America/New_York',
        'date_format' => 'm/d/Y',
        'time_format' => 'g:i A',
        'email_notifications' => false,
        'browser_notifications' => true,
    ]);
    
    // Verify the relationship works
    expect($user->settings()->first())->not->toBeNull();
    expect($user->settings()->first()->timezone)->toBe('America/New_York');
    
    // Verify that accessing the relationship property works
    $user->refresh(); // Refresh to clear any cached relationships
    expect($user->settings)->not->toBeNull();
    expect($user->settings->timezone)->toBe('America/New_York');
});
