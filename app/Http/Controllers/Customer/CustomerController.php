<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function overview()
    {
        $user = auth()->user();
        $lead = \App\Models\Lead::where('email', $user->email)
            ->where('status', 'converted')
            ->whereNotNull('assigned_dealer_id')
            ->first();

        $dealer = null;
        $packages = collect();
        if ($lead) {
            $dealer = \App\Models\User::find($lead->assigned_dealer_id);
            if ($dealer) {
                $packages = \App\Models\MaintenancePackage::where('dealer_id', $dealer->id)
                    ->where('status', 'active')
                    ->get();
            }
        }

        return view('customer.overview', compact('dealer', 'packages'));
    }

    public function storePackageRequest(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'package_id' => 'required|exists:maintenance_packages,id',
            'message' => 'nullable|string',
        ]);

        $package = \App\Models\MaintenancePackage::find($data['package_id']);
        
        \App\Models\PackageRequest::create([
            'user_id' => $user->id,
            'dealer_id' => $package->dealer_id,
            'package_id' => $package->id,
            'message' => $data['message'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Package request submitted to your dealer.');
    }

    public function myHotTub()
    {
        $user = auth()->user();
        $lead = \App\Models\Lead::where('email', $user->email)
            ->where('status', 'converted')
            ->orderBy('updated_at', 'desc')
            ->first();

        return view('customer.my-hot-tub', compact('lead'));
    }
    public function serviceRequests()
    {
        $user = auth()->user();
        $requests = \App\Models\ServiceRequest::where('user_id', $user->id)
            ->where('status', '!=', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $parts = \App\Models\Part::where('status', 'active')->get();
        $services = \App\Models\Service::where('status', 'active')->get();

        return view('customer.service-requests', compact('requests', 'parts', 'services'));
    }

    public function confirmServiceRequest(\Illuminate\Http\Request $request, \App\Models\ServiceRequest $serviceRequest)
    {
        $user = auth()->user();
        if ($serviceRequest->user_id !== $user->id) abort(403);

        $data = $request->validate([
            'customer_review' => 'nullable|string',
            'customer_signature' => 'required|string|max:255',
        ]);

        $serviceRequest->update([
            'customer_review' => $data['customer_review'],
            'customer_signature' => $data['customer_signature'],
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Service confirmed and marked as completed.');
    }

    public function storeServiceRequest(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'type' => 'required|in:part,service',
            'product_id' => 'required|integer',
            'message' => 'nullable|string',
        ]);

        // Find linked dealer via lead/delivery
        $lead = \App\Models\Lead::where('email', $user->email)
            ->where('status', 'converted')
            ->whereNotNull('assigned_dealer_id')
            ->first();

        $dealerId = $lead ? $lead->assigned_dealer_id : null;

        $productName = '';
        if ($data['type'] === 'part') {
            $p = \App\Models\Part::find($data['product_id']);
            $productName = $p ? $p->name : 'Unknown Part';
        } else {
            $s = \App\Models\Service::find($data['product_id']);
            $productName = $s ? $s->name : 'Unknown Service';
        }

        \App\Models\ServiceRequest::create([
            'user_id' => $user->id,
            'dealer_id' => $dealerId,
            'type' => $data['type'],
            'product_id' => $data['product_id'],
            'product_name' => $productName,
            'message' => $data['message'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Service request submitted successfully.');
    }

    public function requestHistory()
    {
        $user = auth()->user();
        $history = \App\Models\ServiceRequest::where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->get();
        return view('customer.request-history', compact('history'));
    }
    public function messages()
    {
        return view('customer.messages');
    }
    public function profile()
    {
        return view('customer.profile');
    }

    public function serviceHistory()
    {
        
        $user = auth()->user();
        $lead = \App\Models\Lead::where('email', $user->email)->first();
         $history = [];
        if (!$lead)   return view('customer.service-history', compact('history'));

        $history = \App\Models\ServiceChecklist::where('lead_id', $lead->id)
            ->orderBy('completed_at', 'desc')
            ->get();
         
        return view('customer.service-history', compact('history'));
    }

    public function signService(Request $request, \App\Models\ServiceChecklist $checklist)
    {
        $user = auth()->user();
        $lead = \App\Models\Lead::where('email', $user->email)->first();
        if (!$lead || $checklist->lead_id !== $lead->id) abort(403);

        $request->validate(['signature' => 'required|string']);
        $checklist->update(['customer_signature' => $request->signature]);
        
        return response()->json(['ok' => true]);
    }
}
