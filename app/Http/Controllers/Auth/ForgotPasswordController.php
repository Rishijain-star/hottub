<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordResetOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    public function showEmailForm()
    {
        return view('auth.passwords.email');
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'We can\'t find a user with that email address.']);
        }

        $otp = '123456'; // Dummy OTP as requested
        $expiresAt = Carbon::now()->addMinutes(15);

        PasswordResetOtp::updateOrCreate(
            ['email' => $request->email],
            ['otp' => $otp, 'expires_at' => $expiresAt]
        );

        Session::put('reset_email', $request->email);

        return redirect()->route('password.otp.form');
    }

    public function showOtpForm()
    {
        if (!Session::has('reset_email')) {
            return redirect()->route('password.request');
        }
        return view('auth.passwords.otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate(['otp' => 'required|string|size:6']);

        $email = Session::get('reset_email');
        if (!$email) {
            return redirect()->route('password.request');
        }

        $otpRecord = PasswordResetOtp::where('email', $email)
            ->where('otp', $request->otp)
            ->first();

        if (!$otpRecord || $otpRecord->expires_at->isPast()) {
            return back()->withErrors(['otp' => 'The OTP is invalid or has expired.']);
        }

        Session::put('otp_verified', true);

        return redirect()->route('password.reset.form');
    }

    public function showResetForm()
    {
        if (!Session::has('reset_email') || !Session::get('otp_verified')) {
            return redirect()->route('password.request');
        }
        return view('auth.passwords.reset');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = Session::get('reset_email');
        if (!$email || !Session::get('otp_verified')) {
            return redirect()->route('password.request');
        }

        $user = User::where('email', $email)->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();
        }

        // Invalidate OTP
        PasswordResetOtp::where('email', $email)->delete();

        Session::forget(['reset_email', 'otp_verified']);

        return redirect()->route('password.success');
    }

    public function showSuccess()
    {
        return view('auth.passwords.success');
    }
}
