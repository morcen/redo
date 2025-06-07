<?php

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

uses(RefreshDatabase::class);

test('setting belongs to user', function () {
    $user = User::factory()->create();
    $setting = Setting::factory()->create(['user_id' => $user->id]);

    expect($setting->user)->toBeInstanceOf(User::class);
    expect($setting->user->id)->toBe($user->id);
});

test('setting has correct fillable attributes', function () {
    $setting = new Setting();
    
    $expectedFillable = [
        'user_id',
        'timezone',
        'date_format',
        'time_format',
        'email_notifications',
        'browser_notifications',
    ];

    expect($setting->getFillable())->toBe($expectedFillable);
});

test('setting casts boolean attributes correctly', function () {
    $setting = Setting::factory()->create([
        'email_notifications' => true,
        'browser_notifications' => false,
    ]);

    expect($setting->email_notifications)->toBeTrue();
    expect($setting->browser_notifications)->toBeFalse();
    
    // Test that they are actually boolean types
    expect(is_bool($setting->email_notifications))->toBeTrue();
    expect(is_bool($setting->browser_notifications))->toBeTrue();
});

test('setting get defaults returns correct values', function () {
    $defaults = Setting::getDefaults();

    $expectedDefaults = [
        'timezone' => 'UTC',
        'date_format' => 'Y-m-d',
        'time_format' => 'H:i',
        'email_notifications' => true,
        'browser_notifications' => false,
    ];

    expect($defaults)->toBe($expectedDefaults);
});

test('setting can be created with defaults', function () {
    $user = User::factory()->create();
    $setting = $user->settings()->create(Setting::getDefaults());

    expect($setting->timezone)->toBe('UTC');
    expect($setting->date_format)->toBe('Y-m-d');
    expect($setting->time_format)->toBe('H:i');
    expect($setting->email_notifications)->toBeTrue();
    expect($setting->browser_notifications)->toBeFalse();
    expect($setting->user_id)->toBe($user->id);
});

test('setting can be updated with custom values', function () {
    $user = User::factory()->create();
    $setting = $user->settings()->create(Setting::getDefaults());

    $setting->update([
        'timezone' => 'America/New_York',
        'date_format' => 'm/d/Y',
        'time_format' => 'g:i A',
        'email_notifications' => false,
        'browser_notifications' => true,
    ]);

    expect($setting->fresh()->timezone)->toBe('America/New_York');
    expect($setting->fresh()->date_format)->toBe('m/d/Y');
    expect($setting->fresh()->time_format)->toBe('g:i A');
    expect($setting->fresh()->email_notifications)->toBeFalse();
    expect($setting->fresh()->browser_notifications)->toBeTrue();
});

test('setting is deleted when user is deleted', function () {
    $user = User::factory()->create();
    $setting = $user->settings()->create(Setting::getDefaults());
    
    $settingId = $setting->id;
    
    // Delete the user
    $user->delete();
    
    // Verify setting is also deleted due to cascade
    expect(Setting::find($settingId))->toBeNull();
});

test('setting factory creates valid settings', function () {
    $setting = Setting::factory()->create();

    expect($setting)->toBeInstanceOf(Setting::class);
    expect($setting->user_id)->not->toBeNull();
    expect($setting->timezone)->not->toBeNull();
    expect($setting->date_format)->not->toBeNull();
    expect($setting->time_format)->not->toBeNull();
    expect(is_bool($setting->email_notifications))->toBeTrue();
    expect(is_bool($setting->browser_notifications))->toBeTrue();
});

test('setting can store various timezone formats', function () {
    $timezones = [
        'UTC',
        'America/New_York',
        'Europe/London',
        'Asia/Tokyo',
        'Australia/Sydney',
        'Pacific/Honolulu',
    ];

    foreach ($timezones as $timezone) {
        $setting = Setting::factory()->create(['timezone' => $timezone]);
        expect($setting->timezone)->toBe($timezone);
    }
});

test('setting can store various date formats', function () {
    $dateFormats = [
        'Y-m-d',
        'm/d/Y',
        'd/m/Y',
        'F j, Y',
        'j F Y',
    ];

    foreach ($dateFormats as $format) {
        $setting = Setting::factory()->create(['date_format' => $format]);
        expect($setting->date_format)->toBe($format);
    }
});

test('setting can store various time formats', function () {
    $timeFormats = [
        'H:i',
        'g:i A',
        'h:i A',
        'H:i:s',
        'g:i:s A',
    ];

    foreach ($timeFormats as $format) {
        $setting = Setting::factory()->create(['time_format' => $format]);
        expect($setting->time_format)->toBe($format);
    }
});
