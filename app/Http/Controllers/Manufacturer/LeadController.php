<?php

namespace App\Http\Controllers\Manufacturer;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadPurchase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = $user->purchasedLeads()->with('purchases')->orderBy('created_at', 'desc');
        $items = $query->paginate(7);
        $purchases = $user->leadPurchases()->whereIn('lead_id', $items->pluck('id'))->get()->keyBy('lead_id');
        return view('manufacturer.leads', compact('items', 'purchases'));
    }

    public function available(Request $request)
    {
        $user = auth()->user();
        $purchasedLeadIds = $user->leadPurchases()->pluck('lead_id');
        $query = Lead::where('status', 'new')
            ->whereNotIn('id', $purchasedLeadIds)
            ->orderBy('created_at', 'desc');
        $items = $query->paginate(7);
        return view('manufacturer.leads-available', compact('items'));
    }

    public function purchase(Request $request, Lead $lead)
    {
        $user = auth()->user();
        if ($user->credits < $lead->price) {
            return back()->with('error', 'You do not have enough credits to purchase this lead.');
        }

        DB::beginTransaction();
        try {
            $user->decrement('credits', $lead->price);
            LeadPurchase::create([
                'lead_id' => $lead->id,
                'user_id' => $user->id,
                'price' => $lead->price,
            ]);
            DB::commit();
            return redirect()->route('manufacturer.leads.my-leads')->with('success', 'Lead purchased successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred while purchasing the lead.');
        }
    }

    public function view(Request $request, Lead $lead)
    {
        $user = auth()->user();
        $purchase = $user->leadPurchases()->where('lead_id', $lead->id)->first();
        if (!$purchase) {
            return response()->json(['ok' => false, 'msg' => 'Not authorized.'], 403);
        }
        return response()->json(['ok' => true, 'lead' => $lead, 'purchase' => $purchase]);
    }

    public function updateStage(Request $request, Lead $lead)
    {
        $user = auth()->user();
        $purchase = $user->leadPurchases()->where('lead_id', $lead->id)->first();
        if (!$purchase) {
            return response()->json(['ok' => false, 'msg' => 'Not authorized.'], 403);
        }

        $stage = $request->input('stage');
        $purchase->update(['stage' => $stage]);

        if ($stage === 'Delivered') {
            // Mark as lost for other dealers
            LeadPurchase::where('lead_id', $lead->id)
                ->where('user_id', '!=', $user->id)
                ->update(['stage' => 'Lost']);
        }

        return response()->json(['ok' => true]);
    }

    public function destroyPrivateLead(Lead $lead)
    {
        if ($lead->user_id !== auth()->id() || $lead->type !== 'private') {
            abort(403);
        }

        $lead->delete();

        return redirect()->route('manufacturer.leads.my-leads')->with('success', 'Private lead deleted successfully.');
    }
}
