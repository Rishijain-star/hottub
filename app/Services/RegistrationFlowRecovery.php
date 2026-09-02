<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegistrationFlowRecovery
{
    public const SESSION_KEY = 'registration_otp';

    public function redirectAfterTokenMismatch(Request $request): RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);

        if (! is_array($pending) || empty($pending['role'])) {
            return redirect()
                ->route('register')
                ->with('show_toast', true)
                ->with('error', 'Your session expired. Please register again.');
        }

        $message = 'This page expired. Please try again — your registration details are still saved.';
        $role = (string) $pending['role'];

        if ($role === User::ROLE_USER) {
            if (! empty($pending['otp_sent']) && ! empty($pending['otp_hash'])) {
                return redirect()->route('register.otp.form')
                    ->with('show_toast', true)
                    ->with('error', $message);
            }

            if (! empty($pending['email_verified'])) {
                return redirect()->route('register.security.check')
                    ->with('show_toast', true)
                    ->with('error', $message);
            }

            if (! empty($pending['email_otp_hash'])) {
                return redirect()->route('register.email.otp.form')
                    ->with('show_toast', true)
                    ->with('error', $message);
            }
        } elseif (! empty($pending['otp_sent']) && ! empty($pending['otp_hash'])) {
            return redirect()->route('register.otp.form')
                ->with('show_toast', true)
                ->with('error', $message);
        }

        return redirect()
            ->route('register')
            ->with('show_toast', true)
            ->with('error', $message);
    }

    public function isRegistrationRequest(Request $request): bool
    {
        return $request->routeIs(
            'register',
            'register.submit',
            'register.email.otp.form',
            'register.email.otp.verify',
            'register.email.otp.resend',
            'register.security.check',
            'register.send.otp',
            'register.otp.form',
            'register.otp.verify',
            'register.otp.resend',
        ) || $request->is('register', 'register/*');
    }
}
