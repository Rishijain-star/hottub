<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserManagementController extends Controller
{
    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:active,paused,frozen'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::ROLE_SUB_ADMIN,
            'admin_permissions' => [],
            'status' => $data['status'],
            'phone' => $data['phone'] ?? null,
            'postcode' => $data['postcode'] ?? null,
            'address' => $data['address'] ?? null,
            'phone_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Sub-admin account created. They sign in to the admin panel with the email and password you set.');
    }

    public function index(Request $request)
    {
        $users = User::whereIn('role', [User::ROLE_USER, User::ROLE_SUB_ADMIN])
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->role, function ($q, $role) {
                $q->where('role', $role);
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->select(['id', 'name', 'email', 'phone', 'postcode', 'status', 'role', 'profile_picture', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->paginate(7)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function update(Request $request, User $user)
    {
        if (! in_array($user->role, [User::ROLE_USER, User::ROLE_SUB_ADMIN], true)) {
            abort(403, 'This account cannot be edited here.');
        }

        if ($user->role === User::ROLE_SUB_ADMIN && ! $request->user()->isFullAdmin()) {
            abort(403, 'Only the main admin can edit sub-admin accounts.');
        }

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone'    => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:255'],
            'status'   => ['required', 'in:active,paused,frozen'],
        ]);

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function resetPassword(Request $request, User $user)
    {
        if (! in_array($user->role, [User::ROLE_USER, User::ROLE_SUB_ADMIN], true)) {
            abort(403, 'This account cannot be edited here.');
        }

        if ($user->role === User::ROLE_SUB_ADMIN && ! $request->user()->isFullAdmin()) {
            abort(403, 'Only the main admin can edit sub-admin accounts.');
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => $data['password'],
            'remember_token' => Str::random(60),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Password updated successfully for {$user->email}.");
    }

    public function destroy(Request $request, User $user)
    {
        if (! in_array($user->role, [User::ROLE_USER, User::ROLE_SUB_ADMIN], true)) {
            abort(403, 'This account cannot be deleted here.');
        }

        if ($user->id === $request->user()->id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        if ($user->role === User::ROLE_SUB_ADMIN && ! $request->user()->isFullAdmin()) {
            abort(403, 'Only the main admin can delete sub-admin accounts.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted.');
    }
}
