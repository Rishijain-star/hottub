<?php

namespace App\Http\Controllers;

use App\Services\SmsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PhoneVerificationController extends Controller
{
    private const OTP_EXPIRY_MINUTES = 5;
    private const OTP_RESEND_COOLDOWN_SECONDS = 60;

    public function show()
    {
        $user = Auth::user();
        if ($user->phone_verified_at) {
            return redirect()->route('customer.overview');
        }

        return view('pages.verify-phone', [
            'phone' => $user->phone,
            'devOtp' => session('dev_otp_code'),
        ]);
    }

    public function submit(Request $request, SmsService $sms)
    {
        $user = Auth::user();
        $data = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        if (!$user->sms_otp_hash || !$user->sms_otp_expires_at || $user->sms_otp_expires_at->isPast()) {
            return back()->withErrors(['code' => 'Code expired. Request a new code from the login page.']);
        }

        if (!hash_equals((string) $user->sms_otp_hash, hash('sha256', $data['code']))) {
            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

        $user->forceFill([
            'phone_verified_at' => now(),
            'sms_otp_hash' => null,
            'sms_otp_expires_at' => null,
        ])->save();

        return redirect()->route('customer.overview')->with('success', 'Phone verified successfully.');
    }

    public function resend(SmsService $sms)
    {
        $user = Auth::user();
        if ($user->phone_verified_at || !$user->phone) {
            return back()->with('error', 'Unable to resend.');
        }

        $cacheKey = 'otp_resend_user_' . $user->id;
        if (Cache::has($cacheKey)) {
            return back()->with('error', 'Please wait before requesting another OTP.');
        }

        if (!self::issueOtp($user, $sms)) {
            return back()->with('error', 'Unable to send OTP right now. Please try again shortly.');
        }

        Cache::put($cacheKey, true, now()->addSeconds(self::OTP_RESEND_COOLDOWN_SECONDS));

        return back()->with('success', 'A new code has been sent.');
    }

    public static function issueOtp(\App\Models\User $user, SmsService $sms): bool
    {
        $code = (string) random_int(100000, 999999);
        $user->forceFill([
            'sms_otp_hash' => hash('sha256', $code),
            'sms_otp_expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
        ])->save();

        if (!$sms->sendVerificationCode($user->phone, $code)) {
            $user->forceFill([
                'sms_otp_hash' => null,
                'sms_otp_expires_at' => null,
            ])->save();

            return false;
        }

        if (!$sms->hasLiveProvider()) {
            session()->flash('dev_otp_code', $code);
        }

        return true;
    }
}
