<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:255',
                'website' => 'nullable|string|max:255',
                'company_number' => 'nullable|string|max:255',
                'vat_number' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($request->hasFile('profile_picture')) {
                $path = $request->file('profile_picture')->store('profile-pictures', 'public');
                // Resize and optimize the image (v3 syntax)
                $manager = new ImageManager(new Driver());
                $image = $manager->read(storage_path('app/public/' . $path));
                $image->cover(120, 120);
                $image->save();
                $user->profile_picture = $path;
            }

            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->phone = $data['phone'];
            $user->website = $data['website'];
            $user->company_number = $data['company_number'];
            $user->vat_number = $data['vat_number'];
            $user->address = $data['address'];
            $user->save();
        } else {
            // Dealers and manufacturers can only update profile picture
            $request->validate([
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            if ($request->hasFile('profile_picture')) {
                $path = $request->file('profile_picture')->store('profile-pictures', 'public');
                // Resize and optimize the image (v3 syntax)
                $manager = new ImageManager(new Driver());
                $image = $manager->read(storage_path('app/public/' . $path));
                $image->cover(120, 120);
                $image->save();
                $user->profile_picture = $path;
                $user->save();
            }
        }

        return back()->with('success', 'Profile updated successfully.');
    }
}
