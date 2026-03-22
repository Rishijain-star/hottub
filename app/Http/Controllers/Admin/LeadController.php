<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $total = Lead::count();
        $converted = Lead::where('status', 'converted')->count();
        $totalValue = 0.0;  // placeholder metric
        $conversionRate = $total ? round(($converted / $total) * 100, 1) : 0.0;

        $query = Lead::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q
                    ->where('name', 'like', "%$s%")
                    ->orWhere('email', 'like', "%$s%")
                    ->orWhere('phone', 'like', "%$s%");
            });
        }

        // Show all leads, including private ones
        $items = $query->orderBy('created_at', 'desc')->paginate(15);

        $buyers = \App\Models\LeadPurchase::select('lead_id', 'dealer_id')
            ->join('users', 'users.id', '=', 'lead_purchases.dealer_id')
            ->selectRaw('lead_purchases.lead_id, users.name as dealer_name, users.id as dealer_id')
            ->get()
            ->groupBy('lead_id');
        $winners = \App\Models\User::whereIn('id', $items->pluck('assigned_dealer_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        return view('admin.leads', compact('items', 'total', 'converted', 'totalValue', 'conversionRate', 'buyers', 'winners'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data['interests'] = $request->input('interests', []);
        $data['is_national'] = $request->boolean('is_national');

        // Geocode the postcode
        $geo = app(\App\Services\GeocodingService::class)->geocode($data['postcode']);
        if ($geo) {
            $data['lead_postcode'] = $data['postcode'];
            $data['lead_lat'] = $geo['lat'];
            $data['lead_lng'] = $geo['lng'];
        }

        $lead = Lead::create($data);

        // Log creation and add auto-tasks
        if ($lead) {
            $this->logInitialLeadActivities($lead);
        }

        return redirect()->route('admin.leads')->with('success', 'Lead created.');
    }

    private function logInitialLeadActivities(Lead $lead): void
    {
        // Activity: Lead Created
        $lead->activities()->create([
            'type' => 'activity',
            'content' => 'Lead created by Admin.',
        ]);

        // Auto-Task: Call Customer
        $lead->activities()->create([
            'type' => 'task',
            'content' => 'Call Customer',
            'due_date' => now()->addHours(2),
        ]);

        // Auto-Task: Follow Up
        $lead->activities()->create([
            'type' => 'task',
            'content' => 'Follow Up Customer',
            'due_date' => now()->addDays(7),
        ]);
    }

    public function edit(Lead $lead)
    {
        $items = Lead::orderBy('created_at', 'desc')->paginate(6);
        return view('admin.leads-edit', ['item' => $lead, 'items' => $items]);
    }

    public function update(Request $request, Lead $lead)
    {
        $data = $this->validateData($request);
        $data['interests'] = $request->input('interests', []);
        $data['is_national'] = $request->boolean('is_national');

        // Geocode if postcode changed
        if ($data['postcode'] !== $lead->postcode) {
            $geo = app(\App\Services\GeocodingService::class)->geocode($data['postcode']);
            if ($geo) {
                $data['lead_postcode'] = $data['postcode'];
                $data['lead_lat'] = $geo['lat'];
                $data['lead_lng'] = $geo['lng'];
            }
        }

        $lead->update($data);
        return redirect()->route('admin.leads')->with('success', 'Lead updated.');
    }

    // public function destroy(Lead $lead)
    // {
    //     $lead->delete();
    //     return back()->with('success', 'Lead deleted.');
    // }

    public function activity(Lead $lead)
    {
        $activities = $lead->activities()->with('dealer')->get();
        return view('admin.lead-activity', compact('lead', 'activities'));
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'postcode' => ['required', 'string', 'max:50'],
            'interests' => ['nullable', 'array'],
            'timeframe' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:new,contacted,converted,closed'],
        ]);
    }
}
