<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Display the 2FA challenge form.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // If user doesn't have 2FA enabled, redirect to dashboard
        if (!$user || !$user->hasTwoFactorEnabled()) {
            return redirect()->intended(route('dashboard'));
        }

        // If already verified, redirect to intended location
        if ($request->session()->get('2fa_verified')) {
            return redirect()->intended(route('dashboard'));
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Verify the 2FA code and mark session as verified.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'nullable|string',
            'recovery_code' => 'nullable|string',
        ]);

        $user = $request->user();

        if (!$user || !$user->hasTwoFactorEnabled()) {
            return redirect()->intended(route('dashboard'));
        }

        // Try OTP code first
        if ($request->filled('code')) {
            $secret = decrypt($user->two_factor_secret);
            $valid = $this->google2fa->verifyKey($secret, $request->code);

            if ($valid) {
                $request->session()->put('2fa_verified', true);
                $request->session()->put('2fa_verified_at', now()->timestamp);

                return redirect()->intended(route('dashboard'));
            }

            throw ValidationException::withMessages([
                'code' => __('The provided two-factor authentication code is invalid.'),
            ]);
        }

        // Try recovery code
        if ($request->filled('recovery_code')) {
            $recoveryCodes = $user->two_factor_recovery_codes ?? [];
            $recoveryCode = $request->recovery_code;

            if (in_array($recoveryCode, $recoveryCodes)) {
                // Remove used recovery code
                $user->two_factor_recovery_codes = array_values(
                    array_filter($recoveryCodes, fn($code) => $code !== $recoveryCode)
                );
                $user->save();

                $request->session()->put('2fa_verified', true);
                $request->session()->put('2fa_verified_at', now()->timestamp);

                return redirect()->intended(route('dashboard'));
            }

            throw ValidationException::withMessages([
                'recovery_code' => __('The provided recovery code is invalid.'),
            ]);
        }

        throw ValidationException::withMessages([
            'code' => __('Please provide a two-factor authentication code or recovery code.'),
        ]);
    }

    /**
     * Log the user out and clear 2FA verification.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
