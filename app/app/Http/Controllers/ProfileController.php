<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\UserActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'recentActivities' => $user->activities()->limit(10)->get(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Handle avatar removal
        if ($request->boolean('remove_avatar') && $user->avatar) {
            Storage::disk('public')->delete('avatars/' . $user->avatar);
            $validated['avatar'] = null;
        }
        // Handle avatar upload
        elseif ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete('avatars/' . $user->avatar);
            }

            $file = $request->file('avatar');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('avatars', $filename, 'public');
            $validated['avatar'] = $filename;

            UserActivity::log($user->id, UserActivity::ACTION_AVATAR_CHANGE);
        } else {
            unset($validated['avatar']);
        }

        unset($validated['remove_avatar']);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Log profile update if changed
        if ($user->isDirty()) {
            UserActivity::log(
                $user->id,
                UserActivity::ACTION_PROFILE_UPDATE,
                __('Profile information updated'),
                ['changed_fields' => array_keys($user->getDirty())]
            );
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update user preferences.
     */
    public function updatePreferences(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'timezone' => ['nullable', 'string', 'max:50'],
            'date_format' => ['nullable', 'string', 'in:d/m/Y,Y-m-d,m/d/Y,d.m.Y'],
            'language' => ['nullable', 'string', 'in:ro,en'],
            'items_per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
        ]);

        $user = $request->user();
        $settings = $user->settings ?? [];

        foreach ($validated as $key => $value) {
            $settings[$key] = $value;
        }

        $user->settings = $settings;
        $user->save();

        UserActivity::log(
            $user->id,
            UserActivity::ACTION_PREFERENCES_UPDATE,
            __('User preferences updated')
        );

        return Redirect::route('profile.edit')->with('status', 'preferences-updated');
    }

    /**
     * Update notification preferences.
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'notify_domain_expiry' => ['nullable', 'boolean'],
            'notify_subscription_renewal' => ['nullable', 'boolean'],
            'notify_payment_received' => ['nullable', 'boolean'],
            'notify_contract_expiry' => ['nullable', 'boolean'],
            'notify_offer_status' => ['nullable', 'boolean'],
            'email_frequency' => ['nullable', 'string', 'in:instant,daily,weekly'],
        ]);

        $user = $request->user();
        $settings = $user->settings ?? [];

        // Convert checkbox values to boolean
        $notificationKeys = [
            'notify_domain_expiry',
            'notify_subscription_renewal',
            'notify_payment_received',
            'notify_contract_expiry',
            'notify_offer_status',
        ];

        foreach ($notificationKeys as $key) {
            $settings[$key] = $request->boolean($key);
        }

        if (isset($validated['email_frequency'])) {
            $settings['email_frequency'] = $validated['email_frequency'];
        }

        $user->settings = $settings;
        $user->save();

        UserActivity::log(
            $user->id,
            UserActivity::ACTION_PREFERENCES_UPDATE,
            __('Notification preferences updated')
        );

        return Redirect::route('profile.edit')->with('status', 'notifications-updated');
    }

    /**
     * Show all user activities.
     */
    public function activities(Request $request): View
    {
        $activities = $request->user()
            ->activities()
            ->paginate(25);

        return view('profile.activities', [
            'activities' => $activities,
        ]);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
