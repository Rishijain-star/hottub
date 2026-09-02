<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
            ]);

            if ($request->hasFile('profile_picture')) {
                $path = $request->file('profile_picture')->store('profile-pictures', 'public');
                self::resizeProfilePictureIfRaster($path);
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
                'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:20480',
            ]);

            if ($request->hasFile('profile_picture')) {
                $path = $request->file('profile_picture')->store('profile-pictures', 'public');
                self::resizeProfilePictureIfRaster($path);
                $user->profile_picture = $path;
                $user->save();
            }
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    /** @param  string  $path  Relative path on the `public` disk (e.g. profile-pictures/…) */
    private static function resizeProfilePictureIfRaster(string $path): void
    {
        $absolute = Storage::disk('public')->path($path);
        $ext = strtolower((string) pathinfo($absolute, PATHINFO_EXTENSION));
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            return;
        }
        $manager = new ImageManager(new Driver());
        $image = $manager->read($absolute);
        $image->cover(120, 120);
        $image->save($absolute);
    }
}
