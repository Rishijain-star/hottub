<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AdminTwoFactorController extends Controller
{
    private const OTP_EXPIRY_MINUTES = 5;

    private const OTP_RESEND_COOLDOWN_SECONDS = 60;

    private function cacheKey(): string
    {
        return 'admin_2fa_' . Auth::id();
    }

    private function resendCacheKey(): string
    {
        return 'admin_2fa_resend_' . Auth::id();
    }

    /**
     * SMS destination: sub-admins always use their own phone; full admins use
     * FIRETEXT_ADMIN_2FA_TO when set, otherwise the user’s phone.
     */
    private function admin2faSmsDestination(User $user): string
    {
        if ($user->isSubAdmin()) {
            return (string) $user->phone;
        }

        $override = trim((string) config('services.firetext.admin_2fa_to', ''));

        return $override !== '' ? $override : (string) $user->phone;
    }

    public function show(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        if ((int) session('admin_2fa_ok_user_id') === (int) $user->id) {
            return redirect()->route('admin.overview');
        }

        $override = trim((string) config('services.firetext.admin_2fa_to', ''));
        if (trim((string) $user->phone) === '' && $override === '') {
            return view('admin.two-factor', [
                'phone' => null,
                'missingPhone' => true,
                'devOtp' => null,
            ]);
        }

        $cacheKey = $this->cacheKey();
        $pending = Cache::get($cacheKey);
        $devOtp = null;
        $needsSend = false;

        $smsTo = $this->admin2faSmsDestination($user);

        if ($pending && is_array($pending) && ($pending['expires_at'] ?? 0) >= now()->timestamp) {
            $devOtp = session('admin_2fa_dev_otp');
        } else {
            if ($pending) {
                Cache::forget($cacheKey);
            }
            $needsSend = true;
        }

        return view('admin.two-factor', [
            'phone' => $smsTo,
            'missingPhone' => false,
            'needsSend' => $needsSend,
            'devOtp' => $devOtp,
        ]);
    }

    public function send(Request $request, SmsService $sms)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        $override = trim((string) config('services.firetext.admin_2fa_to', ''));
        if (trim((string) $user->phone) === '' && $override === '') {
            return back()->with('error', 'No mobile number on this account.');
        }

        $pending = Cache::get($this->cacheKey());
        if ($pending && is_array($pending) && ($pending['expires_at'] ?? 0) >= now()->timestamp) {
            return redirect()->route('admin.two-factor.show');
        }

        $issued = $this->issueChallenge($user, $sms);
        if (!$issued['ok']) {
            $msg = $issued['error'] ?? 'Unable to send SMS right now. Try again shortly.';
            $flash = back()->with('error', $msg);
            if ($sms->wasRateLimited()) {
                $flash->with('show_toast', true);
            }

            return $flash;
        }

        return redirect()->route('admin.two-factor.show')
            ->with('success', 'A verification code has been sent.');
    }

    public function verify(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        $override = trim((string) config('services.firetext.admin_2fa_to', ''));
        if (trim((string) $user->phone) === '' && $override === '') {
            return redirect()->route('admin.two-factor.show');
        }

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $pending = Cache::get($this->cacheKey());
        if (!$pending || !is_array($pending)) {
            return back()->withErrors(['code' => 'Code expired. Request a new code.']);
        }

        if (($pending['expires_at'] ?? 0) < now()->timestamp) {
            Cache::forget($this->cacheKey());

            return back()->withErrors(['code' => 'Code expired. Request a new code.']);
        }

        if (!hash_equals((string) $pending['hash'], hash('sha256', $request->input('code')))) {
            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

        Cache::forget($this->cacheKey());
        Cache::forget($this->resendCacheKey());
        session()->forget('admin_2fa_dev_otp');
        $request->session()->put('admin_2fa_ok_user_id', (int) $user->id);

        return redirect()->intended(route('admin.overview'))->with('success', 'Admin signed in.');
    }

    public function resend(Request $request, SmsService $sms)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403);
        }

        $override = trim((string) config('services.firetext.admin_2fa_to', ''));
        if (trim((string) $user->phone) === '' && $override === '') {
            return back()->with('error', 'No mobile number on this account.');
        }

        if (Cache::has($this->resendCacheKey())) {
            return back()->with('error', 'Please wait before requesting another code.');
        }

        $issued = $this->issueChallenge($user, $sms);
        if (!$issued['ok']) {
            $msg = $issued['error'] ?? 'Unable to send SMS right now. Try again shortly.';
            $flash = back()->with('error', $msg);
            if ($sms->wasRateLimited()) {
                $flash->with('show_toast', true);
            }

            return $flash;
        }

        Cache::put($this->resendCacheKey(), true, now()->addSeconds(self::OTP_RESEND_COOLDOWN_SECONDS));

        return back()->with('success', 'A new code has been sent.');
    }

    /**
     * @return array{ok: bool, dev: string|null, error?: string}
     */
    private function issueChallenge(User $user, SmsService $sms): array
    {
        $code = (string) random_int(100000, 999999);
        Cache::put($this->cacheKey(), [
            'hash' => hash('sha256', $code),
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES)->timestamp,
        ], now()->addMinutes(self::OTP_EXPIRY_MINUTES + 1));

        $to = $this->admin2faSmsDestination($user);

        if (!$sms->sendAdminTwoFactorCode($to, $code)) {
            Cache::forget($this->cacheKey());
            $reason = $sms->sendFailureReason($to);

            return ['ok' => false, 'dev' => null, 'error' => $reason];
        }

        $dev = null;
        if (!$sms->hasLiveProvider()) {
            $dev = $code;
            session()->put('admin_2fa_dev_otp', $code);
        } else {
            session()->forget('admin_2fa_dev_otp');
        }

        return ['ok' => true, 'dev' => $dev];
    }
}
