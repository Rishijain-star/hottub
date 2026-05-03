<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\Request;

class ServiceManagementController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        [$view, $routePrefix, $panelSub] = match (true) {
            $request->routeIs('dealer.*') => [
                'dealer.service-management',
                'dealer',
                'View and download service records for your converted customers.',
            ],
            $request->routeIs('manufacturer.*') => [
                'manufacturer.service-management',
                'manufacturer',
                'View and download service records for your converted customers.',
            ],
            default => [
                'admin.service-management',
                'admin',
                'Monitor all service requests, processing status, and customer confirmations',
            ],
        };

        $requests = ServiceRequest::query()
            ->with(['customer', 'dealer'])
            ->when(! $user->isAdmin(), function ($q) use ($user) {
                $q->where('dealer_id', $user->id);
                self::applyConvertedCustomerScope($q, $user);
            })
            ->when($request->search, function ($q, $search) {
                $q->whereHas('customer', function ($sq) use ($search) {
                    $sq
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->status, function ($q, $status) {
                $q->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(7)
            ->withQueryString();

        return view($view, compact('requests', 'routePrefix', 'panelSub'));
    }

    public function downloadReport(ServiceRequest $serviceRequest)
    {
        $user = auth()->user();
        if (! $this->userCanAccessServiceRequest($user, $serviceRequest)) {
            abort(403);
        }

        $serviceRequest->load(['customer', 'dealer']);

        return view('admin.service-report-print', ['req' => $serviceRequest]);
    }

    /**
     * Limit dealer/manufacturer listings to service requests for customers who appear as
     * converted leads assigned to this panel user (same rule as My Customers).
     */
    private static function applyConvertedCustomerScope($query, User $panelUser): void
    {
        $emails = Lead::query()
            ->where('status', 'converted')
            ->where('assigned_dealer_id', $panelUser->id)
            ->pluck('email')
            ->map(fn ($e) => strtolower(trim((string) $e)))
            ->unique()
            ->filter(fn ($e) => $e !== '');

        if ($emails->isEmpty()) {
            $query->whereRaw('1 = 0');

            return;
        }

        $customerIds = User::query()
            ->where('role', User::ROLE_USER)
            ->where(function ($q) use ($emails) {
                foreach ($emails as $email) {
                    $q->orWhereRaw('LOWER(email) = ?', [$email]);
                }
            })
            ->pluck('id');

        if ($customerIds->isEmpty()) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('user_id', $customerIds);
    }

    private function userCanAccessServiceRequest(User $user, ServiceRequest $serviceRequest): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ((int) $serviceRequest->dealer_id !== (int) $user->id) {
            return false;
        }

        $customer = $serviceRequest->relationLoaded('customer')
            ? $serviceRequest->customer
            : User::find($serviceRequest->user_id);

        if (! $customer || $customer->role !== User::ROLE_USER) {
            return false;
        }

        return Lead::query()
            ->where('status', 'converted')
            ->where('assigned_dealer_id', $user->id)
            ->whereRaw('LOWER(email) = ?', [strtolower($customer->email)])
            ->exists();
    }
}
