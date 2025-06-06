<?php

namespace App\Http\Controllers;

use App\Http\Requests\Settings\SettingsUpdateRequest;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    /**
     * Show the user's general settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $settings = $user->settings()->firstOrCreate(
            ['user_id' => $user->id],
            Setting::getDefaults()
        );

        return Inertia::render('settings/General', [
            'settings' => $settings,
            'timezones' => $this->getTimezones(),
        ]);
    }

    /**
     * Update the user's general settings.
     */
    public function update(SettingsUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $settings = $user->settings()->firstOrCreate(
            ['user_id' => $user->id],
            Setting::getDefaults()
        );

        $settings->update($request->validated());

        return to_route('settings.edit')->with('status', 'Settings updated successfully.');
    }

    /**
     * Detect and update user's timezone via AJAX.
     */
    public function updateTimezone(Request $request): JsonResponse
    {
        $request->validate([
            'timezone' => 'required|string|max:255',
        ]);

        $user = $request->user();
        $settings = $user->settings()->firstOrCreate(
            ['user_id' => $user->id],
            Setting::getDefaults()
        );

        $settings->update(['timezone' => $request->timezone]);

        return response()->json(['success' => true]);
    }

    /**
     * Get list of available timezones.
     *
     * @return array<string, string>
     */
    private function getTimezones(): array
    {
        $timezones = [];
        $identifiers = timezone_identifiers_list();

        foreach ($identifiers as $identifier) {
            $timezones[$identifier] = $identifier;
        }

        return $timezones;
    }
}
