<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    public function overview()
    {
        $hasUsersTable = Schema::hasTable('users');
        $hasUserRoleCol = $hasUsersTable && Schema::hasColumn('users', 'role');
        $hasUserStatusCol = $hasUsersTable && Schema::hasColumn('users', 'status');

        $dealersTotal = ($hasUserRoleCol) ? User::where('role', 'dealer')->count() : 0;
        $dealersApproved = ($hasUserRoleCol && $hasUserStatusCol) ? User::where('role', 'dealer')->where('status', 'approved')->count() : 0;
        $dealersPending = ($hasUserRoleCol && $hasUserStatusCol) ? User::where('role', 'dealer')->where('status', 'pending')->count() : 0;
        $dealersRevoked = ($hasUserRoleCol && $hasUserStatusCol) ? User::where('role', 'dealer')->where('status', 'revoked')->count() : 0;

        $hasHotTubs = Schema::hasTable('hot_tubs');
        $hasBrands = Schema::hasTable('brands');
        $hasLeads = Schema::hasTable('leads');

        $hotTubs = $hasHotTubs ? (int) DB::table('hot_tubs')->count() : 0;
        $brands = $hasBrands ? (int) DB::table('brands')->count() : 0;

        $leadsTotal = 0;
        $totalPurchased = 0;
        $totalConverted = 0;
        $overallConversionRate = 0.0;
        $dealerConversionRate = 0.0;
        $manufacturerConversionRate = 0.0;
        $revenue = 0.0;

        if ($hasLeads) {
            // 1. Total Lead-Buyer Transactions (Purchases)
            $dealerPurchases = DB::table('lead_purchases')->where('buyer_role', 'dealer');
            $dealerPurchasedCount = $dealerPurchases->count();
            $dealerRevenue = $dealerPurchases->sum('amount');

            $manufacturerPurchases = DB::table('lead_purchases')->where('buyer_role', 'manufacturer');
            $manufacturerPurchasedCount = $manufacturerPurchases->count();
            $manufacturerRevenue = $manufacturerPurchases->sum('amount');

            // 2. Unpurchased Leads (Unique leads not bought by anyone)
            $purchasedLeadIds = DB::table('lead_purchases')->pluck('lead_id')->unique();
            $unpurchasedLeadsCount = DB::table('leads')->whereNotIn('id', $purchasedLeadIds)->count();

            // 3. Total Generated Leads = Total Transactions + Unpurchased Leads
            // This ensures Purchased <= Total Generated always.
            $leadsTotal = $dealerPurchasedCount + $manufacturerPurchasedCount + $unpurchasedLeadsCount;

            // 4. Total Converted Sales (Unique leads that were converted)
            $totalConverted = (int) DB::table('leads')->where('status', 'converted')->count();

            // 5. Dealer Converted Sales
            $dealerConverted = DB::table('leads')
                ->join('users', 'leads.assigned_dealer_id', '=', 'users.id')
                ->where('leads.status', 'converted')
                ->where('users.role', 'dealer')
                ->count();

            // 6. Manufacturer Converted Sales
            $manufacturerConverted = DB::table('leads')
                ->join('users', 'leads.assigned_dealer_id', '=', 'users.id')
                ->where('leads.status', 'converted')
                ->where('users.role', 'manufacturer')
                ->count();

            // 7. Overall Conversion Rate (Total Converted / Total Generated Leads)
            $overallConversionRate = $leadsTotal > 0 
                ? round(($totalConverted / $leadsTotal) * 100, 1) 
                : 0.0;

            // 8. Dealer Conversion Rate (Dealer Converted / Total Generated Leads)
            $dealerConversionRate = $leadsTotal > 0 
                ? round(($dealerConverted / $leadsTotal) * 100, 1) 
                : 0.0;

            // 9. Manufacturer Conversion Rate (Manufacturer Converted / Total Generated Leads)
            $manufacturerConversionRate = $leadsTotal > 0
                ? round(($manufacturerConverted / $leadsTotal) * 100, 1)
                : 0.0;

            // 10. Total Revenue
            $revenue = $dealerRevenue + $manufacturerRevenue;
        }

        return view('admin.overview', compact(
            'dealersTotal', 'dealersApproved', 'dealersPending', 'dealersRevoked',
            'hotTubs', 'brands', 'leadsTotal', 'dealerPurchasedCount', 'manufacturerPurchasedCount', 'totalConverted',
            'overallConversionRate', 'dealerConversionRate', 'manufacturerConversionRate', 'revenue'
        ));
    }

    public function hotTubs()
    {
      
        return view('admin.hot-tubs');
    }

    public function brands()
    {
        return view('admin.brands');
    }

    public function services()
    {
        return view('admin.services');
    }

    public function parts()
    {
        return view('admin.parts');
    }

    public function featured()
    {
        return view('admin.featured');
    }

    public function manufacturers()
    {
        return view('admin.manufacturers');
    }

    public function leads()
    {
        return view('admin.leads');
    }

    public function payments()
    {
        $creditRequests = \App\Models\CreditRequest::with('user')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $revenue = \App\Models\Invoice::where('status', 'paid')->sum('amount');
        $pending = \App\Models\CreditRequest::where('status', 'pending')->count();
        $completed = \App\Models\CreditRequest::where('status', 'approved')->count();
        $failed = \App\Models\CreditRequest::where('status', 'rejected')->count();

        return view('admin.payments', compact('creditRequests', 'revenue', 'pending', 'completed', 'failed'));
    }

    public function approveCreditRequest(\App\Models\CreditRequest $request)
    {
        if ($request->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $user = $request->user;
        $user->credits += $request->credits;
        $user->save();

        $request->status = 'approved';
        $request->save();

        // Also create an invoice for record
        \App\Models\Invoice::create([
            'invoice_number' => 'INV-' . time() . '-' . strtolower(\Illuminate\Support\Str::random(6)),
            'dealer_id' => $user->id,
            'credits' => $request->credits,
            'amount' => $request->amount ?: 0,
            'status' => 'paid',
            'payment_id' => 'MANUAL-CREDIT-' . $request->id,
        ]);

        return back()->with('success', 'Credit request approved and credits added to account.');
    }

    public function rejectCreditRequest(\App\Models\CreditRequest $request)
    {
        if ($request->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $request->status = 'rejected';
        $request->save();

        return back()->with('success', 'Credit request has been rejected.');
    }
}
