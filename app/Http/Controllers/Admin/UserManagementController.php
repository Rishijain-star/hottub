<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::where('role', User::ROLE_USER)
            ->select(['id', 'name', 'email', 'phone', 'postcode', 'status', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->paginate(7);

        return view('admin.users.index', compact('users'));
    }

    public function update(Request $request, User $user)
    {
        if ($user->role !== User::ROLE_USER) {
            abort(403, 'This is not a regular user account.');
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
}
