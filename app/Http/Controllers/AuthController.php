<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
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

            if ($request->filled('redirect')) {
                return redirect($request->redirect);
            }

            return match ($user->role) {
                'admin' => redirect()->route('admin.overview'),
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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:customer,dealer,manufacturer',
            'postcode' => 'required|string|max:20',
            'phone' => 'required_if:role,dealer,manufacturer|nullable|string|max:255',
            'company_number' => 'required_if:role,dealer,manufacturer|nullable|string|max:255',
            'vat_number' => 'required_if:role,dealer,manufacturer|nullable|string|max:255',
            'address' => 'required_if:role,dealer,manufacturer|nullable|string|max:1000',
            'terms' => 'accepted',
        ]);

        $dbRole = match ($request->role) {
            'dealer' => 'dealer',
            'manufacturer' => 'manufacturer',
            default => 'user',
        };

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $dbRole,
            'postcode' => $request->postcode,
            'phone' => $request->phone,
            'company_number' => $request->company_number,
            'vat_number' => $request->vat_number,
            'address' => $request->address,
            'status' => ($dbRole === 'user') ? 'active' : 'pending',
        ];

        if ($dbRole === 'dealer' || $dbRole === 'manufacturer') {
            $geo = app(\App\Services\GeocodingService::class)->geocode($request->postcode);
            if ($geo) {
                if ($dbRole === 'dealer') {
                    $userData['dealer_lat'] = $geo['lat'];
                    $userData['dealer_lng'] = $geo['lng'];
                } else {
                    $userData['manufacturer_lat'] = $geo['lat'];
                    $userData['manufacturer_lng'] = $geo['lng'];
                }
            }
        }

        $user = User::create($userData);

        $sessionId = $request->session()->getId();
        Auth::login($user);
        $this->syncGuestActivity($user, $sessionId);

        if ($request->filled('redirect')) {
            return redirect($request->redirect);
        }

        return match ($user->role) {
            'dealer' => redirect()->route('dealer.overview'),
            'manufacturer' => redirect()->route('manufacturer.overview'),
            default => redirect()->route('customer.overview'),
        };
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
}
