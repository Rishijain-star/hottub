<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\EmailOtpService;
use App\Services\EmailValidationService;
use App\Services\GeocodingService;
use App\Services\GeoRestrictionService;
use App\Services\LocalizationService;
use App\Services\RegisteredUserTracker;
use App\Services\RegistrationSecurityService;
use App\Services\OtpAbuseBlockService;
use App\Services\OtpIdentifierLockService;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private const REGISTRATION_OTP_SESSION_KEY = 'registration_otp';
    private const REGISTRATION_OTP_EXPIRY_MINUTES = 5;
    private const REGISTRATION_OTP_RESEND_COOLDOWN_SECONDS = 60;
    private const REGISTRATION_EMAIL_OTP_RESEND_COOLDOWN_SECONDS = 60;

    public function showLogin()
    {
        return view('pages.login');
    }

    public function login(Request $request)
    {
        if (app(GeoRestrictionService::class)->isAccessDenied($request)) {
            return back()->withErrors([
                'email' => app(GeoRestrictionService::class)->genericDenyMessage(),
            ])->withInput($request->only('email'));
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $sessionId = $request->session()->getId();
            $request->session()->regenerate();

            $user = Auth::user();
            if (app(GeoRestrictionService::class)->isBlockedCountryCode($user->country_code)
                || app(GeoRestrictionService::class)->isBlockedPhone($user->phone)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                app(GeoRestrictionService::class)->markDenied();

                return back()->withErrors([
                    'email' => app(GeoRestrictionService::class)->genericDenyMessage(),
                ])->withInput($request->only('email'));
            }

            app(LocalizationService::class)->applyForUser($user);
            $this->syncGuestActivity($user, $sessionId);

            if ($user->isDealer() && $user->status === 'pending') {
                return redirect()->route('dealer.account.pending');
            }
            if ($user->isManufacturer() && $user->status === 'pending') {
                return redirect()->route('manufacturer.account.pending');
            }

            if ($user->isUser() && !$user->phone_verified_at && $user->phone) {
                $sms = app(SmsService::class);
                if ($sms->isSupportedUkMobile($user->phone)) {
                    if (!PhoneVerificationController::issueOtp($user, $sms)) {
                        Auth::logout();
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();

                        $reason = $sms->sendFailureReason($user->phone);
                        $message = $reason !== '' ? $reason : 'Unable to send OTP right now. Please try again shortly.';
                        $redirect = redirect()->route('login')->withErrors(['email' => $message]);
                        if ($sms->wasRateLimited() || app(OtpIdentifierLockService::class)->isLockMessage($message)) {
                            $redirect->with('error', $message)->with('show_toast', true);
                        }

                        return $redirect;
                    }

                    return redirect()->route('verify.phone');
                }
            }

            if ($request->filled('redirect')) {
                return redirect($request->redirect);
            }

            return match ($user->role) {
                'admin', 'sub_admin' => redirect()->route('admin.overview'),
                'dealer' => redirect()->route('dealer.overview'),
                'manufacturer' => redirect()->route('manufacturer.overview'),
                default => redirect()->route('customer.overview'),
            };
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    public function showRegister(Request $request)
    {
        if ($request->boolean('restart')) {
            $request->session()->forget(self::REGISTRATION_OTP_SESSION_KEY);
            $request->session()->forget('otp_pending');
        }

        $pending = $request->session()->get(self::REGISTRATION_OTP_SESSION_KEY);
        if ($pending && ($pending['role'] ?? null) === User::ROLE_USER) {
            if (! empty($pending['otp_sent']) && ! empty($pending['otp_hash'])) {
                return redirect()->route('register.otp.form');
            }
            if (! empty($pending['email_verified'])) {
                return redirect()->route('register.security.check');
            }
            if (! empty($pending['email_otp_hash'])) {
                return redirect()->route('register.email.otp.form');
            }
        }

        $otpPending = false;
        if ($pending && in_array($pending['role'] ?? '', [User::ROLE_DEALER, User::ROLE_MANUFACTURER], true)) {
            $otpPending = session('otp_pending') || ! empty($pending['otp_sent']);
        }

        if (app(OtpIdentifierLockService::class)->isLocked($request)) {
            session()->flash('error', app(OtpIdentifierLockService::class)->lockMessage());
            session()->flash('show_toast', true);
        }

        return view('pages.register', compact('otpPending'));
    }

    public function register(Request $request)
    {
        $geo = app(GeoRestrictionService::class);
        if ($geo->isAccessDenied($request)) {
            return back()->withErrors([
                'email' => $geo->genericDenyMessage(),
            ])->withInput($request->except(['password', 'password_confirmation']));
        }

        if ($geo->isBlockedPhone($request->input('phone'))) {
            $geo->persistBlock($request, 'pk_phone');

            return back()->withErrors([
                'phone' => $geo->genericDenyMessage(),
            ])->withInput($request->except(['password', 'password_confirmation']));
        }

        if ($geo->isBlockedPostcode($request->input('postcode'))) {
            return back()->withErrors([
                'postcode' => $geo->genericDenyMessage(),
            ])->withInput($request->except(['password', 'password_confirmation']));
        }

        if ($request->filled('latitude') && $request->filled('longitude')
            && $geo->isBlockedCoordinates((float) $request->latitude, (float) $request->longitude)) {
            $geo->persistBlock($request, 'register_gps');

            return back()->withErrors([
                'email' => $geo->genericDenyMessage(),
            ])->withInput($request->except(['password', 'password_confirmation']));
        }

        if ($lockMessage = app(OtpIdentifierLockService::class)->assertCanRequestOtp($request, $request->input('phone'))) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->with('error', $lockMessage)
                ->with('show_toast', true);
        }

        $roleInput = (string) $request->input('role', 'customer');
        $role = match ($roleInput) {
            'dealer' => User::ROLE_DEALER,
            'manufacturer' => User::ROLE_MANUFACTURER,
            'customer', 'user' => User::ROLE_USER,
            default => User::ROLE_USER,
        };

        return $this->registerWithPhoneVerification($request, $role);
    }

    public function resendRegistrationOtp(Request $request, SmsService $sms, RegistrationSecurityService $registrationSecurity)
    {
        $pending = $request->session()->get(self::REGISTRATION_OTP_SESSION_KEY);
        if (!$pending || empty($pending['phone']) || empty($pending['role'])) {
            return $this->redirectRegisterSessionExpired($request);
        }

        if ($redirect = $this->guardRegistrationEmailVerified($pending)) {
            return $redirect;
        }

        $tracker = app(RegisteredUserTracker::class);
        if ($blockMessage = $tracker->assertCanSendSms($request)) {
            if ($record = $tracker->current($request)) {
                $tracker->markBlocked($record, 'sms_limit');
            }

            return back()
                ->with('error', $blockMessage)
                ->with('show_toast', app(OtpIdentifierLockService::class)->isLockMessage($blockMessage))
                ->with('otp_pending', true);
        }

        if ($this->registrationOtpOnCooldown($pending)) {
            return back()
                ->with('error', 'Please wait before requesting another OTP.')
                ->with('otp_pending', true);
        }

        try {
            $registrationSecurity->assertCanRequestOtp($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->with('otp_pending', true);
        }

        $code = (string) random_int(100000, 999999);
        $pending['otp_hash'] = hash('sha256', $code);
        $pending['otp_expires_at'] = now()->addMinutes(self::REGISTRATION_OTP_EXPIRY_MINUTES)->toIso8601String();
        $pending['otp_sent_at'] = now()->toIso8601String();
        $pending['otp_attempts'] = 0;

        if (!$sms->sendVerificationCode($pending['phone'], $code)) {
            $reason = $sms->sendFailureReason($pending['phone']);
            $message = $reason !== '' ? $reason : 'Unable to send OTP right now. Please try again shortly.';
            $response = back()
                ->with('error', $message)
                ->with('otp_pending', true);
            if ($sms->wasRateLimited()) {
                $response->with('show_toast', true);
            }
            if (app(OtpAbuseBlockService::class)->isPermanentlyBlocked($request)) {
                return back()
                    ->with('error', app(OtpAbuseBlockService::class)->userBlockMessage($request))
                    ->with('show_toast', true)
                    ->with('otp_pending', true);
            }

            return $response;
        }

        $request->session()->put(self::REGISTRATION_OTP_SESSION_KEY, $pending);
        if ($record = $tracker->current($request)) {
            $tracker->recordSmsSent($record);
        }
        if (!$sms->hasLiveProvider()) {
            $request->session()->flash('dev_registration_otp_code', $code);
        }

        return back()
            ->with('success', 'A new OTP has been sent to your phone.')
            ->with('otp_pending', true);
    }

    public function showRegistrationEmailOtpForm(Request $request)
    {
        $pending = $request->session()->get(self::REGISTRATION_OTP_SESSION_KEY);
        if (!$pending || empty($pending['email']) || empty($pending['role'])) {
            return redirect()->route('register')->with('error', 'Please complete the registration form first.');
        }

        if (!$this->registrationRequiresEmailOtp((string) $pending['role'])) {
            return redirect()->route('register.security.check');
        }

        if (!empty($pending['email_verified'])) {
            return redirect()->route('register.security.check');
        }

        if (empty($pending['email_otp_hash'])) {
            return redirect()->route('register')->with('error', 'No email verification in progress. Please register again.');
        }

        return view('pages.register-email-otp', [
            'email' => $pending['email'],
            'resendSeconds' => $this->registrationEmailOtpResendSecondsRemaining($pending),
            'devOtp' => $request->session()->get('dev_registration_email_otp_code'),
        ]);
    }

    public function verifyRegistrationEmailOtp(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $pending = $request->session()->get(self::REGISTRATION_OTP_SESSION_KEY);
        if (!$pending || empty($pending['email']) || empty($pending['role'])) {
            return $this->redirectRegisterSessionExpired($request);
        }

        if (!$this->registrationRequiresEmailOtp((string) $pending['role'])) {
            return redirect()->route('register.security.check');
        }

        if (!empty($pending['email_otp_locked'])) {
            return back()->withErrors(['code' => 'Too many incorrect attempts. Please request a new OTP.']);
        }

        $expiresAt = !empty($pending['email_otp_expires_at']) ? Carbon::parse($pending['email_otp_expires_at']) : null;
        if (!$expiresAt || $expiresAt->isPast()) {
            return back()->withErrors(['code' => 'OTP has expired. Please request a new OTP.']);
        }

        $attempts = (int) ($pending['email_otp_attempts'] ?? 0);
        $maxAttempts = max(1, (int) config('email_otp.max_verify_attempts', 5));
        if ($attempts >= $maxAttempts) {
            $pending['email_otp_locked'] = true;
            unset($pending['email_otp_hash'], $pending['email_otp_expires_at']);
            $request->session()->put(self::REGISTRATION_OTP_SESSION_KEY, $pending);
            $this->logEmailOtpEvent('verify_locked', $pending, $request);

            return back()->withErrors(['code' => 'Too many incorrect attempts. Please request a new OTP.']);
        }

        if (empty($pending['email_otp_hash']) || !hash_equals((string) $pending['email_otp_hash'], hash('sha256', $request->input('code')))) {
            $pending['email_otp_attempts'] = $attempts + 1;
            $request->session()->put(self::REGISTRATION_OTP_SESSION_KEY, $pending);
            $this->logEmailOtpEvent('verify_failed', $pending, $request);

            return back()->withErrors(['code' => 'Invalid verification code.']);
        }

        $pending['email_verified'] = true;
        $pending['email_otp_attempts'] = 0;
        $pending['email_otp_locked'] = false;
        unset($pending['email_otp_hash'], $pending['email_otp_expires_at'], $pending['email_otp_sent_at']);
        $request->session()->put(self::REGISTRATION_OTP_SESSION_KEY, $pending);
        $request->session()->forget('dev_registration_email_otp_code');
        $this->logEmailOtpEvent('verify_success', $pending, $request);

        $tracker = app(RegisteredUserTracker::class);
        if ($record = $tracker->current($request)) {
            $tracker->markEmailVerified($record);
        }

        return redirect()->route('register.security.check')
            ->with('success', 'Email verified. Complete security verification to receive your mobile OTP.');
    }

    public function resendRegistrationEmailOtp(Request $request, EmailOtpService $emailOtp)
    {
        $pending = $request->session()->get(self::REGISTRATION_OTP_SESSION_KEY);
        if (!$pending || empty($pending['email']) || empty($pending['role'])) {
            return $this->redirectRegisterSessionExpired($request);
        }

        if (!$this->registrationRequiresEmailOtp((string) $pending['role'])) {
            return redirect()->route('register.security.check');
        }

        if (!empty($pending['email_verified'])) {
            return redirect()->route('register.security.check');
        }

        if ($this->registrationEmailOtpOnCooldown($pending)) {
            return back()->with('error', 'Please wait before requesting another code.');
        }

        $result = $emailOtp->sendRegistrationOtp($pending['email'], $request);
        if (!$result['ok']) {
            return back()->with('error', $result['error'] ?? 'Unable to send verification email right now.');
        }

        $plainCode = $result['code'];
        $pending['email_otp_hash'] = hash('sha256', $plainCode);
        $pending['email_otp_expires_at'] = now()->addMinutes((int) config('email_otp.otp_expiry_minutes', 10))->toIso8601String();
        $pending['email_otp_sent_at'] = now()->toIso8601String();
        $pending['email_otp_attempts'] = 0;
        $pending['email_otp_locked'] = false;
        $request->session()->put(self::REGISTRATION_OTP_SESSION_KEY, $pending);

        $response = back()->with('success', 'A new verification code has been sent to your email.');
        if ($emailOtp->lastSendWasSimulated()) {
            $response->with('dev_registration_email_otp_code', $plainCode);
        }

        return $response;
    }

    public function showRegistrationSecurityCheck(Request $request)
    {
        $pending = $request->session()->get(self::REGISTRATION_OTP_SESSION_KEY);
        if (!$pending || empty($pending['phone']) || empty($pending['role'])) {
            return redirect()->route('register')->with('error', 'Please complete the registration form first.');
        }

        if ($redirect = $this->guardRegistrationEmailVerified($pending)) {
            return $redirect;
        }

        if (!empty($pending['otp_sent'])) {
            return redirect()->route('register.otp.form');
        }

        return view('pages.register-security-check', [
            'phone' => $pending['phone'],
        ]);
    }

    public function sendRegistrationOtp(Request $request, SmsService $sms, RegistrationSecurityService $registrationSecurity)
    {
        $pending = $request->session()->get(self::REGISTRATION_OTP_SESSION_KEY);
        if (!$pending || empty($pending['phone']) || empty($pending['role'])) {
            return redirect()->route('register')->with('error', 'Please complete the registration form first.');
        }

        if ($redirect = $this->guardRegistrationEmailVerified($pending)) {
            return $redirect;
        }

        $tracker = app(RegisteredUserTracker::class);
        if ($blockMessage = $tracker->assertCanSendSms($request)) {
            if ($record = $tracker->current($request)) {
                $tracker->markBlocked($record, 'sms_limit');
            }

            return redirect()->route('register.security.check')
                ->with('error', $blockMessage)
                ->with('show_toast', app(OtpIdentifierLockService::class)->isLockMessage($blockMessage));
        }

        if (!empty($pending['otp_sent']) && !empty($pending['otp_hash'])) {
            return redirect()->route('register.otp.form');
        }

        try {
            $registrationSecurity->assertCanRequestOtp($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('register.security.check')->withErrors($e->errors());
        }

        $code = (string) random_int(100000, 999999);
        $pending['otp_hash'] = hash('sha256', $code);
        $pending['otp_expires_at'] = now()->addMinutes(self::REGISTRATION_OTP_EXPIRY_MINUTES)->toIso8601String();
        $pending['otp_sent_at'] = now()->toIso8601String();
        $pending['otp_attempts'] = 0;
        $pending['otp_sent'] = true;

        if (! $sms->isSupportedUkMobile($pending['phone']) && $sms->hasLiveProvider()) {
            return redirect()->route('register.security.check')
                ->with('error', $sms->sendFailureReason($pending['phone']));
        }

        if (!$sms->sendVerificationCode($pending['phone'], $code)) {
            $pending['otp_sent'] = false;
            unset($pending['otp_hash'], $pending['otp_expires_at'], $pending['otp_sent_at']);
            $request->session()->put(self::REGISTRATION_OTP_SESSION_KEY, $pending);

            $reason = $sms->sendFailureReason($pending['phone']);
            $message = $reason !== '' ? $reason : 'Unable to send OTP right now. Please try again shortly.';
            $response = redirect()->route('register.security.check')->with('error', $message);
            if ($sms->wasRateLimited()) {
                $response->with('show_toast', true);
            }
            if (app(OtpAbuseBlockService::class)->isPermanentlyBlocked($request)) {
                return back()
                    ->with('error', app(OtpAbuseBlockService::class)->userBlockMessage($request))
                    ->with('show_toast', true)
                    ->with('otp_pending', true);
            }

            return $response;
        }

        $request->session()->put(self::REGISTRATION_OTP_SESSION_KEY, $pending);

        if ($record = $tracker->current($request)) {
            $tracker->recordSmsSent($record);
        }

        if ($sms->lastSendWasSimulated() || ! $sms->hasLiveProvider()) {
            $request->session()->flash('dev_registration_otp_code', $code);
        }

        $successMessage = $sms->lastSendWasSimulated()
            ? 'SMS not sent (no FireText credit). Use the test code shown below.'
            : 'OTP sent. Please verify the code to complete registration.';

        return redirect()->route('register.otp.form')
            ->with('success', $successMessage);
    }

    public function showRegistrationOtpForm(Request $request)
    {
        $pending = $request->session()->get(self::REGISTRATION_OTP_SESSION_KEY);
        if (!$pending || empty($pending['phone']) || empty($pending['role'])) {
            return $this->redirectRegisterSessionExpired($request);
        }

        if ($redirect = $this->guardRegistrationEmailVerified($pending)) {
            return $redirect;
        }

        if (empty($pending['otp_sent']) || empty($pending['otp_hash'])) {
            return redirect()->route('register.security.check');
        }

        return view('pages.register-otp', [
            'phone' => $pending['phone'],
            'role' => $pending['role'],
            'devOtp' => $request->session()->get('dev_registration_otp_code'),
        ]);
    }

    public function verifyRegistrationOtp(Request $request, RegistrationSecurityService $registrationSecurity)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $pending = $request->session()->get(self::REGISTRATION_OTP_SESSION_KEY);
        if (!$pending || empty($pending['role'])) {
            return $this->redirectRegisterSessionExpired($request);
        }

        if ($redirect = $this->guardRegistrationEmailVerified($pending)) {
            return $redirect;
        }

        $role = (string) $pending['role'];
        $expiresAt = !empty($pending['otp_expires_at']) ? Carbon::parse($pending['otp_expires_at']) : null;
        if (!$expiresAt || $expiresAt->isPast()) {
            return back()->withErrors(['code' => 'OTP has expired. Please request a new code.']);
        }

        $attempts = (int) ($pending['otp_attempts'] ?? 0);
        $maxAttempts = max(1, (int) config('registration.max_otp_verify_attempts', 5));
        if ($attempts >= $maxAttempts) {
            $request->session()->forget(self::REGISTRATION_OTP_SESSION_KEY);

            return redirect()->route('register')->withErrors([
                'code' => 'Too many invalid attempts. Please register again.',
            ]);
        }

        if (!hash_equals((string) $pending['otp_hash'], hash('sha256', $request->input('code')))) {
            $pending['otp_attempts'] = $attempts + 1;
            $request->session()->put(self::REGISTRATION_OTP_SESSION_KEY, $pending);

            return back()->withErrors(['code' => 'Invalid OTP code.']);
        }

        if (User::where('email', $pending['email'])->exists()) {
            return redirect()->route('login')->withErrors(['email' => 'This email is already registered. Please log in instead.']);
        }

        try {
            $registrationSecurity->assertCanCreateAccount($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        $user = $this->createVerifiedUserFromPending($pending, $request);
        $request->session()->forget(self::REGISTRATION_OTP_SESSION_KEY);

        $sessionId = $request->session()->getId();
        Auth::login($user);
        $this->syncGuestActivity($user, $sessionId);

        if ($role === User::ROLE_USER) {
            $redirect = !empty($pending['redirect']) ? redirect($pending['redirect']) : redirect()->route('customer.overview');

            return $redirect->with('success', 'Your account has been created successfully.');
        }

        return $role === User::ROLE_DEALER
            ? redirect()->route('dealer.account.pending')->with('success', 'Your account is under review. The admin will approve it shortly.')
            : redirect()->route('manufacturer.account.pending')->with('success', 'Your account is under review. The admin will approve it shortly.');
    }

    /**
     * Geocode a partner's postcode (dealer or manufacturer) and persist coordinates.
     * Used for postcode + 30-mile lead-matching logic.
     */
    private function geocodePartnerPostcode(User $user): void
    {
        if (empty($user->postcode)) {
            return;
        }
        try {
            $geo = app(GeocodingService::class)->geocode($user->postcode);
        } catch (\Throwable $e) {
            return;
        }
        if (!$geo) {
            return;
        }

        if ($user->role === User::ROLE_DEALER) {
            $user->dealer_lat = $geo['lat'] ?? null;
            $user->dealer_lng = $geo['lng'] ?? null;
        } elseif ($user->role === User::ROLE_MANUFACTURER) {
            $user->manufacturer_lat = $geo['lat'] ?? null;
            $user->manufacturer_lng = $geo['lng'] ?? null;
        }
        if (!empty($geo['timezone']) && array_key_exists('timezone', $user->getAttributes())) {
            $user->timezone = $geo['timezone'];
        } elseif (!empty($geo['timezone'])) {
            // Some user tables include "timezone" without it being in $fillable — set directly when the column exists.
            try {
                $user->timezone = $geo['timezone'];
            } catch (\Throwable $e) {
                // Ignore if column doesn't exist.
            }
        }
        $user->save();
    }

    private function registerWithPhoneVerification(Request $request, string $role)
    {
        $step = (string) $request->input('registration_step', 'request_otp');

        if ($step !== 'verify_otp') {
            $duplicateMessage = $this->registrationDuplicateMessage($request);
            if ($duplicateMessage !== null) {
                return $this->backWithRegistrationToast(
                    $request,
                    $duplicateMessage,
                    $this->registrationDuplicateFieldErrors($request)
                );
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8|confirmed',
                'postcode' => 'required|string|max:20',
                'phone' => 'required|string|max:255|unique:users,phone',
                'vat_number' => 'nullable|string|max:255',
                'company_number' => 'nullable|string|max:255',
                'terms' => 'accepted',
            ], $this->registrationValidationMessages());

            if ($validator->fails()) {
                return $this->backWithRegistrationValidatorErrors($request, $validator);
            }

            try {
                app(RegistrationSecurityService::class)->assertCanRequestOtp($request);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return $this->backWithRegistrationValidationException($request, $e);
            }

            $code = (string) random_int(100000, 999999);
            $security = app(RegistrationSecurityService::class);
            $pending = array_merge([
                'name' => $request->name,
                'email' => $request->email,
                'password_encrypted' => Crypt::encryptString($request->password),
                'postcode' => $request->postcode,
                'phone' => $request->phone,
                'vat_number' => $request->filled('vat_number') ? trim((string) $request->vat_number) : null,
                'company_number' => $request->filled('company_number') ? trim((string) $request->company_number) : null,
                'role' => $role,
                'otp_sent' => false,
                'otp_attempts' => 0,
                'redirect' => $request->input('redirect'),
                'latitude' => $request->input('latitude'),
                'longitude' => $request->input('longitude'),
            ], $security->registrationMeta($request));

            $sms = app(SmsService::class);
            if (! $sms->isSupportedUkMobile($request->phone) && $sms->hasLiveProvider()) {
                return $this->backWithRegistrationToast(
                    $request,
                    $sms->sendFailureReason($request->phone),
                    ['phone' => $sms->sendFailureReason($request->phone)]
                );
            }

            $request->session()->put(self::REGISTRATION_OTP_SESSION_KEY, $pending);
            app(OtpAbuseBlockService::class)->trackAttempt($request, $request->phone);

            $tracker = app(RegisteredUserTracker::class);
            $registered = $tracker->start($request, [
                'role' => $role,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'postcode' => $request->postcode,
                'meta' => [
                    'latitude' => $request->input('latitude'),
                    'longitude' => $request->input('longitude'),
                ],
            ]);
            $tracker->attachToSession($request, $registered);

            if ($this->registrationRequiresEmailOtp($role)) {
                try {
                    app(EmailValidationService::class)->assertCanReceiveOtp($request->email);
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return $this->backWithRegistrationValidationException($request, $e);
                }

                $emailOtp = app(EmailOtpService::class);
                $result = $emailOtp->sendRegistrationOtp($request->email, $request);
                if (!$result['ok']) {
                    return $this->backWithRegistrationToast(
                        $request,
                        $result['error'] ?? 'Unable to send verification email. Please try again.',
                        ['email' => $result['error'] ?? 'Unable to send verification email. Please try again.']
                    );
                }

                $plainCode = $result['code'];
                $pending['email_verified'] = false;
                $pending['email_otp_hash'] = hash('sha256', $plainCode);
                $pending['email_otp_expires_at'] = now()->addMinutes((int) config('email_otp.otp_expiry_minutes', 10))->toIso8601String();
                $pending['email_otp_sent_at'] = now()->toIso8601String();
                $pending['email_otp_attempts'] = 0;
                $pending['email_otp_locked'] = false;
                $request->session()->put(self::REGISTRATION_OTP_SESSION_KEY, $pending);
                $tracker->markEmailPending($registered);

                $redirect = redirect()->route('register.email.otp.form')
                    ->with('success', 'Verification code sent to your email.');

                if ($emailOtp->lastSendWasSimulated()) {
                    $redirect->with('dev_registration_email_otp_code', $plainCode);
                }

                return $redirect;
            }

            return redirect()->route('register.security.check')
                ->with('success', 'Details saved. Complete security verification to receive your OTP.');
        }

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $pending = $request->session()->get(self::REGISTRATION_OTP_SESSION_KEY);
        if (!$pending || ($pending['role'] ?? null) !== $role) {
            return $this->backWithRegistrationToast(
                $request,
                'Your verification session expired. Please register again.',
                ['code' => 'Your verification session expired. Please register again.']
            )->with('otp_pending', false);
        }

        $expiresAt = !empty($pending['otp_expires_at']) ? Carbon::parse($pending['otp_expires_at']) : null;
        if (!$expiresAt || $expiresAt->isPast()) {
            return back()
                ->withInput()
                ->with('otp_pending', true)
                ->withErrors(['code' => 'OTP has expired. Please request a new code.']);
        }

        $attempts = (int) ($pending['otp_attempts'] ?? 0);
        $maxAttempts = max(1, (int) config('registration.max_otp_verify_attempts', 5));
        if ($attempts >= $maxAttempts) {
            $request->session()->forget(self::REGISTRATION_OTP_SESSION_KEY);

            return back()->withErrors(['code' => 'Too many invalid attempts. Please register again.']);
        }

        if (!hash_equals((string) $pending['otp_hash'], hash('sha256', $request->input('code')))) {
            $pending['otp_attempts'] = $attempts + 1;
            $request->session()->put(self::REGISTRATION_OTP_SESSION_KEY, $pending);

            return back()
                ->withInput()
                ->with('otp_pending', true)
                ->withErrors(['code' => 'Invalid OTP code.']);
        }

        if (User::where('email', $pending['email'])->exists()) {
            return back()
                ->withInput()
                ->withErrors(['email' => 'This email is already registered. Please log in instead.']);
        }

        try {
            app(RegistrationSecurityService::class)->assertCanCreateAccount($request);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->with('otp_pending', true)->withErrors($e->errors());
        }

        $user = $this->createVerifiedUserFromPending($pending, $request);
        $request->session()->forget(self::REGISTRATION_OTP_SESSION_KEY);

        $sessionId = $request->session()->getId();
        Auth::login($user);
        $this->syncGuestActivity($user, $sessionId);

        if ($role === User::ROLE_USER) {
            $redirect = !empty($pending['redirect']) ? redirect($pending['redirect']) : redirect()->route('customer.overview');

            return $redirect->with('success', 'Your account has been created successfully.');
        }

        return $role === User::ROLE_DEALER
            ? redirect()->route('dealer.account.pending')->with('success', 'Your account is under review. The admin will approve it shortly.')
            : redirect()->route('manufacturer.account.pending')->with('success', 'Your account is under review. The admin will approve it shortly.');
    }

    private function createVerifiedUserFromPending(array $pending, Request $request): User
    {
        $role = (string) $pending['role'];
        $plainPassword = Crypt::decryptString($pending['password_encrypted']);
        $security = app(RegistrationSecurityService::class);

        $user = User::create(array_merge([
            'name' => $pending['name'],
            'email' => $pending['email'],
            'password' => Hash::make($plainPassword),
            'role' => $role,
            'postcode' => $pending['postcode'],
            'phone' => $pending['phone'],
            'vat_number' => $pending['vat_number'] ?? null,
            'company_number' => $pending['company_number'] ?? null,
            'status' => $role === User::ROLE_USER ? 'active' : 'pending',
            'phone_verified_at' => now(),
            'email_verified_at' => ($role === User::ROLE_USER && !empty($pending['email_verified'])) ? now() : null,
            'sms_otp_hash' => null,
            'sms_otp_expires_at' => null,
        ], $security->registrationMeta($request)));

        if (in_array($role, [User::ROLE_DEALER, User::ROLE_MANUFACTURER], true)) {
            $this->geocodePartnerPostcode($user);
        }

        $lat = isset($pending['latitude']) && $pending['latitude'] !== '' ? (float) $pending['latitude'] : null;
        $lng = isset($pending['longitude']) && $pending['longitude'] !== '' ? (float) $pending['longitude'] : null;
        if ($lat !== null && $lng !== null) {
            app(LocalizationService::class)->resolveAndPersistForUser($user, (string) $user->postcode, $lat, $lng);
        } else {
            $this->persistUserLocalization($user, null, (string) $user->postcode);
        }

        $tracker = app(RegisteredUserTracker::class);
        if ($record = $tracker->current($request)) {
            $tracker->markCompleted($record, (int) $user->id);
            $request->session()->forget('registered_user_id');
        }

        return $user;
    }

    private function persistUserLocalization(User $user, ?Request $request = null, ?string $postcode = null): void
    {
        $lat = null;
        $lng = null;

        if ($request) {
            if ($request->filled('latitude') && $request->filled('longitude')) {
                $lat = (float) $request->input('latitude');
                $lng = (float) $request->input('longitude');
            } elseif (session()->has('geo_lat') && session()->has('geo_lng')) {
                $lat = (float) session('geo_lat');
                $lng = (float) session('geo_lng');
            }
            $postcode = $postcode ?? $request->postcode;
        }

        app(LocalizationService::class)->resolveAndPersistForUser(
            $user,
            $postcode,
            $lat,
            $lng,
        );
    }

    private function syncGuestActivity($user, $sessionId)
    {
        if ($sessionId) {
            \App\Models\CustomerActivity::where('session_id', $sessionId)
                ->whereNull('user_id')
                ->update([
                    'user_id' => $user->id,
                    'session_id' => null
                ]);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function registrationOtpOnCooldown(array $pending): bool
    {
        if (empty($pending['otp_sent_at'])) {
            return false;
        }

        try {
            return Carbon::parse($pending['otp_sent_at'])->addSeconds(self::REGISTRATION_OTP_RESEND_COOLDOWN_SECONDS)->isFuture();
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function registrationRequiresEmailOtp(string $role): bool
    {
        return $role === User::ROLE_USER;
    }

    private function guardRegistrationEmailVerified(array $pending)
    {
        if (!$this->registrationRequiresEmailOtp((string) ($pending['role'] ?? ''))) {
            return null;
        }

        if (empty($pending['email_verified'])) {
            return redirect()->route('register.email.otp.form')
                ->with('error', 'Please verify your email before continuing.');
        }

        return null;
    }

    private function registrationEmailOtpOnCooldown(array $pending): bool
    {
        if (empty($pending['email_otp_sent_at'])) {
            return false;
        }

        try {
            $cooldown = max(10, (int) config('email_otp.resend_cooldown_seconds', self::REGISTRATION_EMAIL_OTP_RESEND_COOLDOWN_SECONDS));

            return Carbon::parse($pending['email_otp_sent_at'])->addSeconds($cooldown)->isFuture();
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function registrationEmailOtpResendSecondsRemaining(array $pending): int
    {
        if (empty($pending['email_otp_sent_at'])) {
            return 0;
        }

        try {
            $cooldown = max(10, (int) config('email_otp.resend_cooldown_seconds', self::REGISTRATION_EMAIL_OTP_RESEND_COOLDOWN_SECONDS));
            $until = Carbon::parse($pending['email_otp_sent_at'])->addSeconds($cooldown);
            if ($until->isPast()) {
                return 0;
            }

            return (int) now()->diffInSeconds($until);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function logEmailOtpEvent(string $event, array $pending, Request $request): void
    {
        Log::info('Registration email OTP', [
            'event' => $event,
            'email_hash' => hash('sha256', strtolower(trim((string) ($pending['email'] ?? '')))),
            'ip' => app(GeoRestrictionService::class)->clientIp($request),
            'device' => substr((string) app(RegistrationSecurityService::class)->resolveDeviceId($request), 0, 8),
            'attempts' => (int) ($pending['email_otp_attempts'] ?? 0),
        ]);
    }

    private function registrationValidationMessages(): array
    {
        return [
            'name.required' => 'Please enter your full name.',
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered. Please log in or use a different email.',
            'phone.required' => 'Please enter your mobile number.',
            'phone.unique' => 'This mobile number is already registered. Please log in or use a different number.',
            'password.required' => 'Please enter a password.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Passwords do not match. Please check and try again.',
            'postcode.required' => 'Please enter your postcode.',
            'terms.accepted' => 'You must agree to the Privacy Policy to continue.',
        ];
    }

    private function registrationDuplicateMessage(Request $request): ?string
    {
        $emailTaken = User::where('email', $request->input('email'))->exists();
        $phoneTaken = User::where('phone', $request->input('phone'))->exists();

        if ($emailTaken && $phoneTaken) {
            return 'An account with these details is already registered. Please log in instead.';
        }

        if ($phoneTaken) {
            return 'This mobile number is already registered. Please log in or use a different number.';
        }

        if ($emailTaken) {
            return 'This email address is already registered. Please log in or use a different email.';
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    private function registrationDuplicateFieldErrors(Request $request): array
    {
        $errors = [];
        if (User::where('email', $request->input('email'))->exists()) {
            $errors['email'] = 'This email address is already registered. Please log in or use a different email.';
        }
        if (User::where('phone', $request->input('phone'))->exists()) {
            $errors['phone'] = 'This mobile number is already registered. Please log in or use a different number.';
        }

        return $errors;
    }

    private function backWithRegistrationValidatorErrors(Request $request, \Illuminate\Contracts\Validation\Validator $validator): RedirectResponse
    {
        $toast = $validator->errors()->first('phone')
            ?? $validator->errors()->first('email')
            ?? $validator->errors()->first();

        return back()
            ->withInput($request->except(['password', 'password_confirmation']))
            ->withErrors($validator)
            ->with('show_toast', true)
            ->with('error', $toast);
    }

    private function backWithRegistrationValidationException(Request $request, \Illuminate\Validation\ValidationException $e): RedirectResponse
    {
        $toast = collect($e->errors())->flatten()->first() ?? 'Please check your details and try again.';

        return back()
            ->withInput($request->except(['password', 'password_confirmation']))
            ->withErrors($e->errors())
            ->with('show_toast', true)
            ->with('error', $toast);
    }

    /**
     * @param  array<string, string>  $fieldErrors
     */
    private function backWithRegistrationToast(Request $request, string $toast, array $fieldErrors = []): RedirectResponse
    {
        $response = back()
            ->withInput($request->except(['password', 'password_confirmation']))
            ->with('show_toast', true)
            ->with('error', $toast);

        if ($fieldErrors !== []) {
            $response->withErrors($fieldErrors);
        }

        return $response;
    }

    private function redirectRegisterSessionExpired(Request $request): RedirectResponse
    {
        $request->session()->forget(self::REGISTRATION_OTP_SESSION_KEY);
        $request->session()->forget('otp_pending');

        return redirect()
            ->route('register')
            ->with('show_toast', true)
            ->with('error', 'Your registration session expired. Please fill in the form again.');
    }
}
