<?php

namespace Database\Factories;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting>
 */
class SettingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Setting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $timezones = [
            'UTC',
            'America/New_York',
            'America/Chicago',
            'America/Denver',
            'America/Los_Angeles',
            'Europe/London',
            'Europe/Paris',
            'Europe/Berlin',
            'Asia/Tokyo',
            'Asia/Shanghai',
            'Australia/Sydney',
            'Pacific/Honolulu',
        ];

        $dateFormats = [
            'Y-m-d',
            'm/d/Y',
            'd/m/Y',
            'F j, Y',
            'j F Y',
        ];

        $timeFormats = [
            'H:i',
            'g:i A',
            'h:i A',
            'H:i:s',
        ];

        return [
            'user_id' => User::factory(),
            'timezone' => fake()->randomElement($timezones),
            'date_format' => fake()->randomElement($dateFormats),
            'time_format' => fake()->randomElement($timeFormats),
            'email_notifications' => fake()->boolean(),
            'browser_notifications' => fake()->boolean(),
        ];
    }

    /**
     * Create settings with default values.
     */
    public function defaults(): static
    {
        return $this->state(fn (array $attributes) => Setting::getDefaults());
    }

    /**
     * Create settings with email notifications enabled.
     */
    public function withEmailNotifications(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_notifications' => true,
        ]);
    }

    /**
     * Create settings with browser notifications enabled.
     */
    public function withBrowserNotifications(): static
    {
        return $this->state(fn (array $attributes) => [
            'browser_notifications' => true,
        ]);
    }

    /**
     * Create settings with specific timezone.
     */
    public function timezone(string $timezone): static
    {
        return $this->state(fn (array $attributes) => [
            'timezone' => $timezone,
        ]);
    }

    /**
     * Create settings with US Eastern timezone.
     */
    public function easternTime(): static
    {
        return $this->timezone('America/New_York');
    }

    /**
     * Create settings with Pacific timezone.
     */
    public function pacificTime(): static
    {
        return $this->timezone('America/Los_Angeles');
    }

    /**
     * Create settings with European timezone.
     */
    public function europeanTime(): static
    {
        return $this->timezone('Europe/London');
    }
}
