<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class ServiceManagementController extends Controller
{
    public function index()
    {
        $requests = ServiceRequest::with(['customer', 'dealer'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

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
