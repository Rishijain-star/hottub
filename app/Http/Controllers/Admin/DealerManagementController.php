<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\GeocodingService;
use Illuminate\Support\Str;

class DealerManagementController extends Controller
{
    public function index(Request $request)
    {
        $dealers = User::where('role', 'dealer')
            ->when($request->search, function ($q, $search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                       ->orWhere('company_name', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->select(['id', 'name', 'email', 'company_name', 'company_number', 'vat_number', 'phone', 'postcode', 'address', 'website', 'status', 'credits', 'profile_picture', 'type', 'service_offerings', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->paginate(7)
            ->withQueryString();

        return view('admin.dealers.index', compact('dealers'));
    }

    public function create()
    {
        return view('admin.dealers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'        => ['required', 'string', 'min:8'],
            'company_name'    => ['nullable', 'string', 'max:255'],
            'company_number'  => ['nullable', 'string', 'max:255'],
            'vat_number'      => ['nullable', 'string', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:255'],
            'postcode'        => ['nullable', 'string', 'max:255'],
            'address'         => ['nullable', 'string', 'max:1000'],
            'website'         => ['nullable', 'string', 'max:255'],
            'type'            => ['nullable', 'string', 'max:255'],
            'service_offerings' => ['nullable', 'array'],
        ]);

        $dealer = new User();
        $dealer->fill($data);
        $dealer->role   = User::ROLE_DEALER;
        $dealer->status = 'approved';
        $dealer->credits = $dealer->credits ?? 0;
        if (!empty($dealer->postcode)) {
            $geo = app(GeocodingService::class)->geocode($dealer->postcode);
            if ($geo) {
                $dealer->dealer_lat = $geo['lat'];
                $dealer->dealer_lng = $geo['lng'];
                $dealer->timezone = $geo['timezone'];
                $dealer->timezone = $geo['timezone'];
            }
        }
        $dealer->save();

        return redirect()->route('admin.dealers.index')
            ->with('success', 'Dealer created successfully.');
    }

    public function edit(User $dealer)
    {
        return view('admin.dealers.edit', compact('dealer'));
    }

    public function update(Request $request, User $dealer)
    {
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255', 'unique:users,email,' . $dealer->id],
            'password'        => ['nullable', 'string', 'min:8'],
            'company_name'    => ['nullable', 'string', 'max:255'],
            'company_number'  => ['nullable', 'string', 'max:255'],
            'vat_number'      => ['nullable', 'string', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:255'],
            'postcode'        => ['nullable', 'string', 'max:255'],
            'address'         => ['nullable', 'string', 'max:1000'],
            'website'         => ['nullable', 'string', 'max:255'],
            'status'          => ['required', 'in:pending,approved,revoked,paused,frozen'],
            'type'            => ['nullable', 'string', 'max:255'],
            'service_offerings' => ['nullable', 'array'],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        if ($request->has('postcode')) {
            if (!empty($data['postcode'])) {
                $geo = app(GeocodingService::class)->geocode($data['postcode']);
                $data['dealer_lat'] = $geo['lat'] ?? null;
                $data['dealer_lng'] = $geo['lng'] ?? null;
            } else {
                $data['dealer_lat'] = null;
                $data['dealer_lng'] = null;
            }
        }

        $dealer->update($data);

        return redirect()->route('admin.dealers.index')
            ->with('success', 'Dealer updated successfully.');
    }

    public function destroy(User $dealer)
    {
        if ($dealer->role !== User::ROLE_DEALER) {
            abort(403, 'This account cannot be deleted here.');
        }

        $dealer->delete();
        return redirect()->route('admin.dealers.index')->with('success', 'Dealer deleted.');
    }

    public function approve(User $dealer)
    {
        $dealer->update(['status' => 'approved']);
        return back()->with('success', "{$dealer->name} has been approved.");
    }

    public function revoke(User $dealer)
    {
        $dealer->update(['status' => 'revoked']);
        return back()->with('success', "{$dealer->name}'s access has been revoked.");
    }

    public function credits(User $dealer)
    {
        return view('admin.dealers.credits', compact('dealer'));
    }

    public function addCredits(Request $request, User $dealer)
    {
        $request->validate(['amount' => 'required|integer|min:1']);
        $dealer->increment('credits', $request->amount);
        return back()->with('success', "{$request->amount} credits added to {$dealer->name}.");
    }

    public function resetPassword(Request $request, User $dealer)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $dealer->update([
            'password' => $data['password'],
            'remember_token' => Str::random(60),
        ]);

        return back()->with('success', "Password updated successfully for {$dealer->email}.");
    }
}
