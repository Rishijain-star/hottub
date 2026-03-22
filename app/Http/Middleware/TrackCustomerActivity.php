<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackCustomerActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only track GET requests and successful responses
        if ($request->isMethod('get') && $response->getStatusCode() === 200 && !$request->ajax()) {
            $user = auth()->user();
            $sessionId = session()->getId();
            $url = $request->fullUrl();
            $routeName = $request->route() ? $request->route()->getName() : null;
            
            // Skip tracking admin/dealer/manufacturer panels to keep it clean
            if (str_starts_with($request->path(), 'admin') || 
                str_starts_with($request->path(), 'dealer') || 
                str_starts_with($request->path(), 'manufacturer')) {
                return $response;
            }

            $pageName = $this->getPageName($request, $routeName);
            $productId = null;
            $productType = null;

            // Detect product details from route
            if ($routeName === 'hot-tubs.detail' || $routeName === 'swim-spas.detail') {
                $slug = $request->route('slug');
                $hotTub = \App\Models\HotTub::where('slug', $slug)->first();
                if ($hotTub) {
                    $productId = $hotTub->id;
                    $productType = $hotTub->product_type === 'swim_spa' ? 'swim_spa' : 'hot_tub';
                    $pageName = ($productType === 'swim_spa' ? 'Swim Spa: ' : 'Hot Tub: ') . $hotTub->brand . ' ' . $hotTub->model;
                }
            } elseif ($routeName === 'outdoor-products.detail') {
                $slug = $request->route('slug');
                $op = \App\Models\OutdoorProduct::where('slug', $slug)->first();
                if ($op) {
                    $productId = $op->id;
                    $productType = 'outdoor_product';
                    $pageName = 'Outdoor Product: ' . $op->brand . ' ' . $op->model;
                }
            }

            // Avoid duplicate tracking for the same page in the same session within 1 minute
            $recent = \App\Models\CustomerActivity::where(function($q) use ($user, $sessionId) {
                    if ($user) $q->where('user_id', $user->id);
                    else $q->where('session_id', $sessionId);
                })
                ->where('url', $url)
                ->where('created_at', '>', now()->subMinute())
                ->first();

            if (!$recent) {
                \App\Models\CustomerActivity::create([
                    'user_id' => $user ? $user->id : null,
                    'session_id' => $user ? null : $sessionId,
                    'page_name' => $pageName,
                    'url' => $url,
                    'product_id' => $productId,
                    'product_type' => $productType,
                ]);
            }
        }

        return $response;
    }

    private function getPageName($request, $routeName)
    {
        if ($routeName) {
            return ucfirst(str_replace(['.', '-'], ' ', $routeName));
        }
        return $request->path() === '/' ? 'Home' : ucfirst($request->path());
    }
}
