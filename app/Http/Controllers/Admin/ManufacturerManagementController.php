<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Services\GeocodingService;

class ManufacturerManagementController extends Controller
{
    public function index()
    {
        $manufacturers = User::where('role', User::ROLE_MANUFACTURER)
            ->select(['id', 'name', 'email', 'company_name', 'company_number', 'vat_number', 'phone', 'postcode', 'address', 'website', 'status', 'credits', 'profile_picture', 'created_at'])
            ->orderBy('created_at','desc')
            ->paginate(6);
        return view('admin.manufacturers', compact('manufacturers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => ['required','string','max:255'],
            'email'           => ['required','email','max:255','unique:users,email'],
            'password'        => ['required','string','min:8'],
            'company_name'    => ['nullable','string','max:255'],
            'company_number'  => ['nullable','string','max:255'],
            'vat_number'      => ['nullable','string','max:255'],
            'phone'           => ['nullable','string','max:255'],
            'postcode'        => ['nullable','string','max:255'],
            'address'         => ['nullable','string','max:1000'],
            'website'         => ['nullable','string','max:255'],
        ]);
        $user = new User();
        $user->fill($data);
        $user->role = User::ROLE_MANUFACTURER;
        $user->status = 'approved';
        $user->credits = 0;
        if (!empty($user->postcode)) {
            $geo = app(GeocodingService::class)->geocode($user->postcode);
            if ($geo) {
                $user->manufacturer_lat = $geo['lat'];
                $user->manufacturer_lng = $geo['lng'];
            }
        }
        $user->save();
        return redirect()->route('admin.manufacturers')->with('success', 'Manufacturer created.');
    }

    public function edit(User $manufacturer)
    {
        return view('admin.manufacturers-edit', compact('manufacturer'));
    }

    public function update(Request $request, User $manufacturer)
    {
        $data = $request->validate([
            'name'            => ['required','string','max:255'],
            'email'           => ['required','email','max:255','unique:users,email,'.$manufacturer->id],
            'password'        => ['nullable','string','min:8'],
            'company_name'    => ['nullable','string','max:255'],
            'company_number'  => ['nullable','string','max:255'],
            'vat_number'      => ['nullable','string','max:255'],
            'phone'           => ['nullable','string','max:255'],
            'postcode'        => ['nullable','string','max:255'],
            'address'         => ['nullable','string','max:1000'],
            'website'         => ['nullable','string','max:255'],
            'status'          => ['required','in:pending,approved,revoked,paused,frozen'],
        ]);
        if (empty($data['password'])) unset($data['password']);
        
        if ($request->has('postcode')) {
            if (!empty($data['postcode'])) {
                $geo = app(GeocodingService::class)->geocode($data['postcode']);
                $data['manufacturer_lat'] = $geo['lat'] ?? null;
                $data['manufacturer_lng'] = $geo['lng'] ?? null;
            } else {
                // Postcode was submitted but empty, so null the coordinates
                $data['manufacturer_lat'] = null;
                $data['manufacturer_lng'] = null;
            }
        }
        
        $manufacturer->update($data);
        
        return redirect()->route('admin.manufacturers')->with('success', 'Manufacturer updated.');
    }

    public function destroy(User $manufacturer)
    {
        $manufacturer->delete();
        return back()->with('success','Manufacturer deleted.');
    }

    public function approve(User $manufacturer)
    {
        $manufacturer->update(['status' => 'approved']);
        return back()->with('success', 'Manufacturer approved.');
    }

    public function revoke(User $manufacturer)
    {
        $manufacturer->update(['status' => 'revoked']);
        return back()->with('success', 'Manufacturer revoked.');
    }

    public function credits(User $manufacturer)
    {
        return view('admin.manufacturers-credits', compact('manufacturer'));
    }

    public function addCredits(Request $request, User $manufacturer)
    {
        $request->validate(['amount' => 'required|integer|min:1']);
        $manufacturer->increment('credits', $request->amount);
        return back()->with('success', "{$request->amount} credits added.");
    }
}
