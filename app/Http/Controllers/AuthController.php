<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\GeocodingService;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private const REGISTRATION_OTP_SESSION_KEY = 'registration_otp';
    private const REGISTRATION_OTP_EXPIRY_MINUTES = 5;
    private const REGISTRATION_OTP_RESEND_COOLDOWN_SECONDS = 60;

    public function showLogin()
    {
        return view('pages.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $sessionId = $request->session()->getId();
            $request->session()->regenerate();

            $user = Auth::user();
            $this->syncGuestActivity($user, $sessionId);

            if ($user->isDealer() && $user->status === 'pending') {
                return redirect()->route('dealer.account.pending');
            }
            if ($user->isManufacturer() && $user->status === 'pending') {
                return redirect()->route('manufacturer.account.pending');
            }

            if ($user->isUser() && !$user->phone_verified_at && $user->phone) {
            
                if (!PhoneVerificationController::issueOtp($user, app(SmsService::class))) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('login')->withErrors([
                        'email' => 'Unable to send OTP right now. Please try again shortly.',
                    ]);
                }

                return redirect()->route('verify.phone');
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

    public function showRegister()
    {
        return view('pages.register');
    }

    public function register(Request $request)
    {
        $roleInput = (string) $request->input('role', 'customer');
        $role = match ($roleInput) {
            'dealer' => User::ROLE_DEALER,
            'manufacturer' => User::ROLE_MANUFACTURER,
            'customer', 'user' => User::ROLE_USER,
            default => User::ROLE_USER,
        };

        if (in_array($role, [User::ROLE_DEALER, User::ROLE_MANUFACTURER], true)) {
            return $this->registerPartnerAccount($request, $role);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'postcode' => 'required|string|max:20',
            'phone' => 'required|string|max:255',
            'terms' => 'accepted',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => User::ROLE_USER,
            'postcode' => $request->postcode,
            'phone' => $request->phone,
            'status' => 'active',
            'phone_verified_at' => null,
        ]);

        $sessionId = $request->session()->getId();
        Auth::login($user);
        $this->syncGuestActivity($user, $sessionId);

        if (!PhoneVerificationController::issueOtp($user, app(SmsService::class))) {
            Auth::logout();

            return redirect()->route('register')->withErrors([
                'phone' => 'Unable to send OTP right now. Please try again shortly.',
            ])->withInput($request->except(['password', 'password_confirmation']));
        }

        if ($request->filled('redirect')) {
            return redirect($request->redirect);
        }

        return redirect()->route('verify.phone');
    }

    public function resendRegistrationOtp(Request $request, SmsService $sms)
    {
        $pending = $request->session()->get(self::REGISTRATION_OTP_SESSION_KEY);
        if (!$pending || empty($pending['phone']) || empty($pending['role'])) {
            return redirect()->route('register')->with('error', 'No pending verification found. Please fill the registration form again.');
        }
        if ($this->registrationOtpOnCooldown($pending)) {
            return back()
                ->with('error', 'Please wait before requesting another OTP.')
                ->with('otp_pending', true);
        }

        $code = (string) random_int(100000, 999999);
        $pending['otp_hash'] = hash('sha256', $code);
        $pending['otp_expires_at'] = now()->addMinutes(self::REGISTRATION_OTP_EXPIRY_MINUTES)->toIso8601String();
        $pending['otp_sent_at'] = now()->toIso8601String();

        if (!$sms->sendVerificationCode($pending['phone'], $code)) {
            return back()
                ->with('error', 'Unable to send OTP right now. Please try again shortly.')
                ->with('otp_pending', true);
        }

        $request->session()->put(self::REGISTRATION_OTP_SESSION_KEY, $pending);
        if (!$sms->hasLiveProvider()) {
            $request->session()->flash('dev_registration_otp_code', $code);
        }

        return back()
            ->with('success', 'A new OTP has been sent to your phone.')
            ->with('otp_pending', true);
    }

    public function showRegistrationOtpForm(Request $request)
    {
        $pending = $request->session()->get(self::REGISTRATION_OTP_SESSION_KEY);
        if (!$pending || empty($pending['phone']) || empty($pending['role'])) {
            return redirect()->route('register')->with('error', 'No pending verification found. Please register again.');
        }

        return view('pages.register-otp', [
            'phone' => $pending['phone'],
            'role' => $pending['role'],
            'devOtp' => $request->session()->get('dev_registration_otp_code'),
        ]);
    }

    public function verifyRegistrationOtp(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $pending = $request->session()->get(self::REGISTRATION_OTP_SESSION_KEY);
        if (!$pending || empty($pending['role'])) {
            return redirect()->route('register')->with('error', 'No pending registration found. Please start again.');
        }

        $role = (string) $pending['role'];
        $expiresAt = !empty($pending['otp_expires_at']) ? Carbon::parse($pending['otp_expires_at']) : null;
        if (!$expiresAt || $expiresAt->isPast()) {
            return back()->withErrors(['code' => 'OTP has expired. Please request a new code.']);
        }

        if (!hash_equals((string) $pending['otp_hash'], hash('sha256', $request->input('code')))) {
            return back()->withErrors(['code' => 'Invalid OTP code.']);
        }

        if (User::where('email', $pending['email'])->exists()) {
            return redirect()->route('login')->withErrors(['email' => 'This email is already registered. Please log in instead.']);
        }

        $plainPassword = Crypt::decryptString($pending['password_encrypted']);
        $user = User::create([
            'name' => $pending['name'],
            'email' => $pending['email'],
            'password' => Hash::make($plainPassword),
            'role' => $role,
            'postcode' => $pending['postcode'],
            'phone' => $pending['phone'],
            'vat_number' => $pending['vat_number'] ?? null,
            'company_number' => $pending['company_number'] ?? null,
            'status' => 'pending',
            'phone_verified_at' => now(),
            'sms_otp_hash' => null,
            'sms_otp_expires_at' => null,
        ]);

        $this->geocodePartnerPostcode($user);

        $request->session()->forget(self::REGISTRATION_OTP_SESSION_KEY);

        $sessionId = $request->session()->getId();
        Auth::login($user);
        $this->syncGuestActivity($user, $sessionId);

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

    private function registerPartnerAccount(Request $request, string $role)
    {
        $step = (string) $request->input('registration_step', 'request_otp');

        if ($step !== 'verify_otp') {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8|confirmed',
                'postcode' => 'required|string|max:20',
                'phone' => 'required|string|max:255',
                'vat_number' => 'nullable|string|max:255',
                'company_number' => 'nullable|string|max:255',
                'terms' => 'accepted',
            ]);

            $code = (string) random_int(100000, 999999);
            $pending = [
                'name' => $request->name,
                'email' => $request->email,
                'password_encrypted' => Crypt::encryptString($request->password),
                'postcode' => $request->postcode,
                'phone' => $request->phone,
                'vat_number' => $request->filled('vat_number') ? trim((string) $request->vat_number) : null,
                'company_number' => $request->filled('company_number') ? trim((string) $request->company_number) : null,
                'role' => $role,
                'otp_hash' => hash('sha256', $code),
                'otp_expires_at' => now()->addMinutes(self::REGISTRATION_OTP_EXPIRY_MINUTES)->toIso8601String(),
                'otp_sent_at' => now()->toIso8601String(),
                'redirect' => $request->input('redirect'),
            ];

            $sms = app(SmsService::class);
            if (!$sms->sendVerificationCode($request->phone, $code)) {
                return back()
                    ->withInput()
                    ->withErrors(['phone' => 'Unable to send OTP right now. Please try again shortly.']);
            }

            $request->session()->put(self::REGISTRATION_OTP_SESSION_KEY, $pending);
            if (!$sms->hasLiveProvider()) {
                $request->session()->flash('dev_registration_otp_code', $code);
            }

            return redirect()->route('register.otp.form')
                ->with('success', 'OTP sent. Please verify the code to complete registration.');
        }

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $pending = $request->session()->get(self::REGISTRATION_OTP_SESSION_KEY);
        if (!$pending || ($pending['role'] ?? null) !== $role) {
            return back()->withErrors(['code' => 'No pending registration found. Please start again.']);
        }

        $expiresAt = !empty($pending['otp_expires_at']) ? Carbon::parse($pending['otp_expires_at']) : null;
        if (!$expiresAt || $expiresAt->isPast()) {
            return back()
                ->withInput()
                ->with('otp_pending', true)
                ->withErrors(['code' => 'OTP has expired. Please request a new code.']);
        }

        if (!hash_equals((string) $pending['otp_hash'], hash('sha256', $request->input('code')))) {
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

        $plainPassword = Crypt::decryptString($pending['password_encrypted']);
        $user = User::create([
            'name' => $pending['name'],
            'email' => $pending['email'],
            'password' => Hash::make($plainPassword),
            'role' => $role,
            'postcode' => $pending['postcode'],
            'phone' => $pending['phone'],
            'vat_number' => $pending['vat_number'] ?? null,
            'company_number' => $pending['company_number'] ?? null,
            'status' => 'pending',
            'phone_verified_at' => now(),
            'sms_otp_hash' => null,
            'sms_otp_expires_at' => null,
        ]);

        $this->geocodePartnerPostcode($user);

        $request->session()->forget(self::REGISTRATION_OTP_SESSION_KEY);

        $sessionId = $request->session()->getId();
        Auth::login($user);
        $this->syncGuestActivity($user, $sessionId);

        return $role === User::ROLE_DEALER
            ? redirect()->route('dealer.account.pending')->with('success', 'Your account is under review. The admin will approve it shortly.')
            : redirect()->route('manufacturer.account.pending')->with('success', 'Your account is under review. The admin will approve it shortly.');
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
}
