<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class ServiceManagementController extends Controller
{
    public function index(Request $request)
    {
        $requests = ServiceRequest::query()
            ->with(['customer', 'dealer'])
            ->when($request->search, function ($q, $search) {
                $q->whereHas('customer', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(7)
            ->withQueryString();

        return view('admin.service-management', compact('requests'));
    }

    public function downloadReport(ServiceRequest $serviceRequest)
    {
        // Simple HTML report for now since dompdf is not installed.
        // We will provide a clean print-friendly HTML view that the browser can save as PDF.
        $serviceRequest->load(['customer', 'dealer']);
        
        return view('admin.service-report-print', ['req' => $serviceRequest]);
    }
}
