<?php

namespace App\Http\Controllers\Manufacturer;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\LeadPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManufacturerController extends Controller
{
    public function overview()
    {
        $me = Auth::user();

        // 1. Available Credits
        $availableCredits = $me->credits;

        // 2. Available Leads (not purchased by me, and < 3 manufacturer purchases)
        $myPurchasedIds = LeadPurchase::where('dealer_id', $me->id)->where('buyer_role', 'manufacturer')->pluck('lead_id');
        $fullLeadIds = LeadPurchase::where('buyer_role', 'manufacturer')
            ->select('lead_id', \DB::raw('count(*) as count'))
            ->groupBy('lead_id')
            ->having('count', '>=', 3)
            ->pluck('lead_id');
        $excludeIds = $myPurchasedIds->merge($fullLeadIds)->unique();
        $availableLeads = Lead::whereNotIn('id', $excludeIds)->count();

        // 3. My Leads (purchased by me)
        $myPurchasedLeadIds = LeadPurchase::where('dealer_id', $me->id)->pluck('lead_id');
        $purchasedLeadsCount = $myPurchasedLeadIds->count();

        // 4. Active Leads (purchased by me, not delivered or lost)
        $activeLeads = Lead::whereIn('id', $myPurchasedLeadIds)
            ->whereNotIn('stage', ['Delivered', 'Lost'])
            ->where(function ($query) use ($me) {
                $query
                    ->whereNull('assigned_dealer_id')
                    ->orWhere('assigned_dealer_id', $me->id);
            })
            ->count();

        // 5. Converted Leads (won by me)
        $convertedLeads = Lead::whereIn('id', $myPurchasedLeadIds)
            ->where('stage', 'Delivered')
            ->where('assigned_dealer_id', $me->id)
            ->count();

        // 6. Lost Leads (lost to another dealer)
        $lostLeads = Lead::whereIn('id', $myPurchasedLeadIds)
            ->where('stage', 'Lost')
            ->count();

        // 7. Conversion %
        $conversionRate = $purchasedLeadsCount > 0
            ? round(($convertedLeads / $purchasedLeadsCount) * 100, 1)
            : 0.0;

        return view('manufacturer.overview', compact(
            'availableCredits',
            'availableLeads',
            'purchasedLeadsCount',
            'activeLeads',
            'convertedLeads',
            'lostLeads',
            'conversionRate'
        ));
    }

    public function leads()
    {
        $manufacturer = Auth::user();
        $perPage = 6;
        $leadIds = LeadPurchase::where('dealer_id', $manufacturer->id)
            ->where('buyer_role', 'manufacturer')
            ->orderBy('created_at', 'desc')
            ->pluck('lead_id');
        $items = Lead::whereIn('id', $leadIds)
            ->where(function ($q) use ($manufacturer) {
                $q->whereNull('assigned_dealer_id')->orWhere('assigned_dealer_id', $manufacturer->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        $purchases = LeadPurchase::where('dealer_id', $manufacturer->id)->where('buyer_role', 'manufacturer')->get()->keyBy('lead_id');
        return view('manufacturer.leads', compact('items', 'purchases'));
    }

    public function quotes()
    {
        $manufacturer = Auth::user();
        $perPage = 6;

        // 1. Leads I already purchased
        $myPurchasedIds = LeadPurchase::where('dealer_id', $manufacturer->id)->where('buyer_role', 'manufacturer')->pluck('lead_id');

        // 2. Leads that reached the manufacturer purchase limit (3)
        $fullLeadIds = LeadPurchase::where('buyer_role', 'manufacturer')
            ->select('lead_id', \DB::raw('count(*) as count'))
            ->groupBy('lead_id')
            ->having('count', '>=', 3)
            ->pluck('lead_id');

        // 3. Exclude my purchased leads and full leads
        $excludeIds = $myPurchasedIds->merge($fullLeadIds)->unique();

        // 4. Manufacturers see ALL leads (no postcode restriction)
        $items = Lead::whereNotIn('id', $excludeIds)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // We also need the purchase counts to show in the view
        $counts = LeadPurchase::where('buyer_role', 'manufacturer')
            ->whereIn('lead_id', $items->pluck('id'))
            ->select('lead_id', \DB::raw('count(*) as count'))
            ->groupBy('lead_id')
            ->pluck('count', 'lead_id');

        $mine = $myPurchasedIds->toArray();

        return view('manufacturer.quotes', compact('items', 'counts', 'mine'));
    }

    public function inventory()
    {
        $me = Auth::user();
        $inventoryCount = \App\Models\HotTub::where('brand_id', $me->id)->count();
        return view('manufacturer.inventory', compact('inventoryCount'));
    }

    public function credits()
    {
        $me = Auth::user();
        $creditRequests = \App\Models\CreditRequest::where('user_id', $me->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return view('manufacturer.credits', compact('me', 'creditRequests'));
    }

    public function requestCredits(Request $request)
    {
        $me = Auth::user();
        $data = $request->validate([
            'credits' => 'required|integer|min:1',
            'amount' => 'nullable|numeric|min:0',
        ]);

        \App\Models\CreditRequest::create([
            'user_id' => $me->id,
            'credits' => $data['credits'],
            'amount' => $data['amount'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Credit request submitted successfully. It is now awaiting admin approval.');
    }

    public function profile()
    {
        $manufacturer = Auth::user();
        return view('manufacturer.profile', compact('manufacturer'));
    }

    public function buyLead(Request $request, Lead $lead)
    {
        $manufacturer = Auth::user();

        // Check if the manufacturer has enough credits
        if ($manufacturer->credits < $lead->price) {
            return response()->json(['ok' => false, 'msg' => 'Insufficient credits'], 422);
        }

        $purchased = LeadPurchase::where('lead_id', $lead->id)->where('buyer_role', 'manufacturer')->count();
        if ($purchased >= 3) {
            return response()->json(['ok' => false, 'msg' => 'Lead limit reached', 'count' => $purchased], 422);
        }
        $exists = LeadPurchase::where('lead_id', $lead->id)->where('dealer_id', $manufacturer->id)->where('buyer_role', 'manufacturer')->exists();
        if ($exists) {
            return response()->json(['ok' => false, 'msg' => 'Already purchased', 'count' => $purchased], 422);
        }

        // Deduct credits and save the purchase
        $manufacturer->credits -= $lead->price;
        $manufacturer->save();

        LeadPurchase::create([
            'lead_id' => $lead->id,
            'dealer_id' => $manufacturer->id,
            'buyer_role' => 'manufacturer',
            'amount' => $lead->price,
        ]);
        $lead->stage = $lead->stage ?: 'New Lead';
        $lead->save();
        $count = LeadPurchase::where('lead_id', $lead->id)->where('buyer_role', 'manufacturer')->count();
        return response()->json([
            'ok' => true,
            'count' => $count,
            'limitReached' => $count >= 3,
            'lead' => [
                'id' => $lead->id,
                'name' => $lead->name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'postcode' => $lead->postcode,
                'message' => $lead->message,
                'price' => $lead->price,
                'interests' => $lead->interests,
                'stage' => $lead->stage ?: 'New Lead',
                'status' => $lead->status,
                'delivery_details' => $lead->delivery_details,
            ],
        ]);
    }

    public function leadDetail(Lead $lead)
    {
        $manufacturer = Auth::user();
        $has = LeadPurchase::where('lead_id', $lead->id)->where('dealer_id', $manufacturer->id)->where('buyer_role', 'manufacturer')->exists();
        if ($lead->assigned_dealer_id && $lead->assigned_dealer_id !== $manufacturer->id) {
            return response()->json(['ok' => false, 'msg' => 'Lead closed'], 403);
        }
        if (!$has) {
            return response()->json(['ok' => false, 'msg' => 'Not authorized'], 403);
        }

        $activities = \App\Models\LeadActivity::where('lead_id', $lead->id)
            ->where('dealer_id', $manufacturer->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'ok' => true,
            'lead' => [
                'id' => $lead->id,
                'name' => $lead->name,
                'email' => $lead->email,
                'phone' => $lead->phone,
                'postcode' => $lead->postcode,
                'message' => $lead->message,
                'price' => $lead->price,
                'interests' => $lead->interests,
                'stage' => $lead->stage ?: 'New Lead',
                'status' => $lead->status,
                'delivery_details' => $lead->delivery_details,
            ],
            'activities' => $activities
        ]);
    }

    public function viewLead(Lead $lead)
    {
        $manufacturer = Auth::user();
        $has = LeadPurchase::where('lead_id', $lead->id)->where('dealer_id', $manufacturer->id)->where('buyer_role', 'manufacturer')->exists();
        if ($lead->assigned_dealer_id && $lead->assigned_dealer_id !== $manufacturer->id) {
            abort(403);
        }
        abort_unless($has, 403);

        // Automatically create tasks if they don't exist
        $this->createDefaultTasks($lead, $manufacturer->id);

        if (empty($manufacturer->timezone) && !empty($manufacturer->postcode)) {
            $geo = app(\App\Services\GeocodingService::class)->geocode($manufacturer->postcode);
            if ($geo && !empty($geo['timezone'])) {
                $manufacturer->timezone = $geo['timezone'];
                $manufacturer->save();
            }
        }

        return view('manufacturer.lead', compact('lead'));
    }

    private function createDefaultTasks(Lead $lead, int $manufacturerId)
    {
        $existingTasks = \App\Models\LeadActivity::where('lead_id', $lead->id)
            ->where('dealer_id', $manufacturerId)
            ->where('type', 'task')
            ->whereIn('content', ['Call Customer', 'Follow Up Customer'])
            ->count();

        if ($existingTasks === 0) {
            \App\Models\LeadActivity::create([
                'lead_id' => $lead->id,
                'dealer_id' => $manufacturerId,
                'type' => 'task',
                'content' => 'Call Customer',
                'due_date' => now()->addHours(2),
            ]);

            \App\Models\LeadActivity::create([
                'lead_id' => $lead->id,
                'dealer_id' => $manufacturerId,
                'type' => 'task',
                'content' => 'Follow Up Customer',
                'due_date' => now()->addDays(7),
            ]);
        }
    }

    public function downloadGuidance(Lead $lead)
    {
        $manufacturer = Auth::user();
        $has = LeadPurchase::where('lead_id', $lead->id)->where('dealer_id', $manufacturer->id)->where('buyer_role', 'manufacturer')->exists();
        if ($lead->assigned_dealer_id && $lead->assigned_dealer_id !== $manufacturer->id) {
            abort(403);
        }
        abort_unless($has, 403);
        $files = [
            public_path('docs/Water-Treatment-Chemical-Liability-Disclaimer.pdf'),
            public_path('docs/Delivery-Policy.pdf'),
            public_path('docs/Hot-Tub-Installation-Safety-Disclaimer.pdf'),
            public_path('docs/Hot-Tub-Installation-Disclaimer.pdf'),
        ];
        $zipName = 'customer-guidance-' . $lead->id . '.zip';
        $tmp = tempnam(sys_get_temp_dir(), 'cgzip_');
        $zipPath = $tmp . '.zip';
        @unlink($zipPath);
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            abort(500, 'Unable to create archive');
        }
        foreach ($files as $path) {
            if (is_file($path)) {
                $zip->addFile($path, basename($path));
            }
        }
        $zip->close();
        return response()->download($zipPath, $zipName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    public function updateLeadStage(Request $request, Lead $lead)
    {
        $manufacturer = Auth::user();
        $purchase = LeadPurchase::where('lead_id', $lead->id)->where('dealer_id', $manufacturer->id)->where('buyer_role', 'manufacturer')->first();
        if (!$purchase)
            return response()->json(['ok' => false, 'msg' => 'Not authorized'], 403);
        $data = $request->validate([
            'stage' => ['required', 'string', 'in:New Lead,Contacted,Nurturing,Sale Pending,Site Visit,Delivered,Lost'],
        ]);

        $current = $purchase->stage ?: 'New Lead';

        // Log activity
        \App\Models\LeadActivity::create([
            'lead_id' => $lead->id,
            'dealer_id' => $manufacturer->id,
            'type' => 'status_change',
            'old_value' => $current,
            'new_value' => $data['stage'],
            'content' => "Stage changed from {$current} to {$data['stage']}"
        ]);

        $purchase->stage = $data['stage'];
        $purchase->save();

        if ($data['stage'] === 'Delivered') {
            $lead->status = 'closed';
            $lead->assigned_dealer_id = $manufacturer->id;
            $lead->save();
        }

        return response()->json(['ok' => true, 'stage' => $purchase->stage, 'status' => $lead->status]);
    }

    public function addLeadActivity(Request $request, Lead $lead)
    {
        $manufacturer = Auth::user();
        $has = LeadPurchase::where('lead_id', $lead->id)->where('dealer_id', $manufacturer->id)->where('buyer_role', 'manufacturer')->exists();
        if (!$has)
            return response()->json(['ok' => false, 'msg' => 'Not authorized'], 403);

        $data = $request->validate([
            'type' => 'required|in:note,task,activity',
            'content' => 'required|string',
            'due_date' => 'nullable|date',
        ]);

        $activity = \App\Models\LeadActivity::create([
            'lead_id' => $lead->id,
            'dealer_id' => $manufacturer->id,
            'type' => $data['type'],
            'content' => $data['content'],
            'due_date' => $data['due_date'] ?? null,
        ]);

        if ($data['type'] === 'task') {
            \App\Models\LeadActivity::create([
                'lead_id' => $lead->id,
                'dealer_id' => $manufacturer->id,
                'type' => 'task_created',
                'content' => 'Task Created - ' . $data['content'],
            ]);
        }

        return response()->json(['ok' => true, 'activity' => $activity]);
    }

    public function toggleTask(Request $request, \App\Models\LeadActivity $activity)
    {
        $manufacturer = Auth::user();
        if ($activity->dealer_id !== $manufacturer->id)
            return response()->json(['ok' => false], 403);

        $activity->is_completed = !$activity->is_completed;
        $activity->save();

        if ($activity->is_completed) {
            \App\Models\LeadActivity::create([
                'lead_id' => $activity->lead_id,
                'dealer_id' => $manufacturer->id,
                'type' => 'task_completion',
                'content' => 'Task Completed - ' . $activity->content,
            ]);
        }

        return response()->json(['ok' => true, 'is_completed' => $activity->is_completed]);
    }

    public function deliverLead(Request $request, Lead $lead)
    {
        $manufacturer = Auth::user();
        $purchase = LeadPurchase::where('lead_id', $lead->id)->where('dealer_id', $manufacturer->id)->where('buyer_role', 'manufacturer')->first();
        if (!$purchase)
            return response()->json(['ok' => false, 'msg' => 'Not authorized'], 403);
        if ($lead->assigned_dealer_id && $lead->assigned_dealer_id !== $manufacturer->id) {
            return response()->json(['ok' => false, 'msg' => 'Lead already converted'], 422);
        }
        $data = $request->validate([
            'make' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'shell_colour' => ['nullable', 'string', 'max:255'],
            'cabinet_colour' => ['nullable', 'string', 'max:255'],
            'accessories' => ['nullable', 'string', 'max:500'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'invoice' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'warranty' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);
        $details = [
            'make' => $data['make'],
            'model' => $data['model'],
            'shell_colour' => $data['shell_colour'] ?? null,
            'cabinet_colour' => $data['cabinet_colour'] ?? null,
            'accessories' => $data['accessories'] ?? null,
            'sale_price' => $data['sale_price'] ?? null,
            'delivery_date' => now()->format('Y-m-d'),
        ];
        if ($request->hasFile('invoice')) {
            $path = $request->file('invoice')->store('leads', 'public');
            $purchase->invoice_path = $path;
        }
        if ($request->hasFile('warranty')) {
            $path = $request->file('warranty')->store('leads', 'public');
            $purchase->warranty_path = $path;
        }

        $purchase->delivery_details = $details;
        $purchase->stage = 'Delivered';
        $purchase->save();

        $lead->status = 'converted';
        $lead->assigned_dealer_id = $manufacturer->id;
        $lead->save();

        // Mark as lost for other manufacturers who purchased this lead
        LeadPurchase::where('lead_id', $lead->id)
            ->where('dealer_id', '!=', $manufacturer->id)
            ->where('buyer_role', 'manufacturer')
            ->update(['stage' => 'Lost']);

        // Sync to Customer Account
        $customer = \App\Models\User::where('email', $lead->email)->where('role', 'user')->first();

        if (!$customer) {
            // Create a customer account if it doesn't exist
            $customer = \App\Models\User::create([
                'name' => $lead->name,
                'email' => $lead->email,
                'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(12)),
                'role' => 'user',
                'phone' => $lead->phone,
                'postcode' => $lead->postcode,
            ]);
        }

        if ($customer) {
            // Create initial chat message
            \App\Models\Message::create([
                'sender_id' => $manufacturer->id,
                'receiver_id' => $customer->id,
                'lead_id' => $lead->id,
                'content' => "Hello, I have successfully processed your hot tub purchase for the {$details['make']} {$details['model']}. You can use this chat for any future work, service, or support regarding your new hot tub.",
            ]);
        }

        return response()->json(['ok' => true, 'stage' => $lead->stage, 'status' => $lead->status, 'details' => $lead->delivery_details]);
    }

    public function payments()
    {
        $me = Auth::user();
        $invoices = \App\Models\Invoice::where('dealer_id', $me->id)
            ->orderBy('created_at', 'desc')
            ->get();
        return view('manufacturer.payments', compact('invoices'));
    }

    public function invoice(string $invoice)
    {
        $data = [
            'invoice' => $invoice,
            'date' => now()->format('d/m/Y'),
            'time' => now()->format('H:i:s'),
            'customer' => auth()->user()->company_name ?? auth()->user()->name,
            'status' => 'failed',
            'items' => [
                ['title' => 'Lead Generation Credits', 'desc' => 'Hot Tub Buyer Platform Access', 'qty' => 100, 'unit' => 3.0, 'total' => 300.0],
            ],
            'currency' => 'GBP',
            'total' => 300.0,
        ];
        return view('manufacturer.invoice', $data);
    }

    public function invoiceDownload(string $invoice)
    {
        $html = view('manufacturer.invoice', [
            'invoice' => $invoice,
            'date' => now()->format('d/m/Y'),
            'time' => now()->format('H:i:s'),
            'customer' => auth()->user()->company_name ?? auth()->user()->name,
            'status' => 'failed',
            'items' => [
                ['title' => 'Lead Generation Credits', 'desc' => 'Hot Tub Buyer Platform Access', 'qty' => 100, 'unit' => 3.0, 'total' => 300.0],
            ],
            'currency' => 'GBP',
            'total' => 300.0,
        ])->render();
        $filename = 'invoice-' . $invoice . '.html';
        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
