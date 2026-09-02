<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminSecurityController extends Controller
{
    public function show(): View
    {
        $user = Auth::user();

        return view('admin.security', [
            'user' => $user,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:255', Rule::unique('users', 'phone')->ignore($user->id)],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation', 'current_password']))
                ->with('open_change_form', true)
                ->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $newEmail = mb_strtolower(trim($validated['email']));
        $newPhone = trim($validated['phone']);
        $newPassword = $validated['password'];

        $emailChanging = $newEmail !== mb_strtolower((string) $user->email);
        $phoneChanging = $newPhone !== trim((string) $user->phone);
        $passwordChanging = ! Hash::check($newPassword, $user->password);

        if (! $emailChanging && ! $phoneChanging && ! $passwordChanging) {
            return back()
                ->withInput($request->except(['password', 'password_confirmation', 'current_password']))
                ->with('open_change_form', true)
                ->withErrors(['email' => 'Enter new details that are different from your current login information.']);
        }

        $user->email = $newEmail;
        $user->phone = $newPhone;
        $user->password = $newPassword;
        $user->remember_token = null;
        $user->save();

        Cache::forget('admin_2fa_' . $user->id);
        Cache::forget('admin_2fa_resend_' . $user->id);
        session()->forget('admin_2fa_dev_otp');
        $request->session()->forget('admin_2fa_ok_user_id');
        $request->session()->regenerate();

        $message = 'Your login details have been updated. Use your new email, mobile number, and password from now on. Complete admin verification to continue.';

        session()->put('url.intended', route('admin.security'));

        return redirect()
            ->route('admin.two-factor.show')
            ->with('success', $message);
    }
}
