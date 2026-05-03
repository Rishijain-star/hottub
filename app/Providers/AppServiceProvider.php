<?php

namespace App\Providers;

use App\Models\DealerAcademyContent;
use App\Models\Lead;
use App\Models\Notification;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // URL::forceScheme('https');

        // Share business details & social links with all views (footer, invoices, etc.).
        // Guard with Schema check so migrations can run without a populated table.
        View::composer('*', function ($view) {
            static $cache = null;
            if ($cache === null) {
                $cache = [
                    'businessDetails' => [
                        'company_name' => 'Hot Tub Buyer Ltd',
                        'company_email' => 'support@hottubbuyer.com',
                        'company_address' => null,
                        'vat_number' => '842368419',
                        'company_number' => '049947',
                        'fca_number' => null,
                    ],
                    'socialLinks' => [
                        'facebook' => null,
                        'twitter' => null,
                        'instagram' => null,
                        'tiktok' => null,
                    ],
                ];

                try {
                    if (Schema::hasTable('site_settings')) {
                        $cache['businessDetails'] = [
                            'company_name' => SiteSetting::get('company_name', $cache['businessDetails']['company_name']),
                            'company_email' => SiteSetting::get('company_email', $cache['businessDetails']['company_email']),
                            'company_address' => SiteSetting::get('company_address'),
                            'vat_number' => SiteSetting::get('company_vat_number', $cache['businessDetails']['vat_number']),
                            'company_number' => SiteSetting::get('company_number', $cache['businessDetails']['company_number']),
                            'fca_number' => SiteSetting::get('company_fca_number'),
                        ];
                        $cache['socialLinks'] = [
                            'facebook' => SiteSetting::get('social_facebook_url'),
                            'twitter' => SiteSetting::get('social_twitter_url'),
                            'instagram' => SiteSetting::get('social_instagram_url'),
                            'tiktok' => SiteSetting::get('social_tiktok_url'),
                        ];
                    }
                } catch (\Throwable $e) {
                    // Swallow — defaults above remain usable.
                }
            }

            $view->with('siteBusinessDetails', $cache['businessDetails']);
            $view->with('siteSocialLinks', $cache['socialLinks']);
        });

        Lead::created(function (Lead $lead): void {
            if ($lead->is_private) {
                return;
            }

            $now = now();
            $targetUserIds = User::query()
                ->whereIn('role', [User::ROLE_DEALER, User::ROLE_MANUFACTURER])
                ->where(function ($q) {
                    $q->whereNull('status')
                        ->orWhereIn('status', ['active', 'approved']);
                })
                ->pluck('id');

            if ($targetUserIds->isEmpty()) {
                return;
            }

            $rows = $targetUserIds->map(fn ($userId) => [
                'user_id' => $userId,
                'message' => 'A new lead is available.',
                'type' => 'available_leads',
                'data' => json_encode(['lead_id' => $lead->id]),
                'read' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            Notification::insert($rows);
        });

        DealerAcademyContent::created(function (DealerAcademyContent $content): void {
            $this->notifyDealersForAcademyUpdate($content->id, 'New dealer academy content is available.');
        });

        DealerAcademyContent::updated(function (DealerAcademyContent $content): void {
            $this->notifyDealersForAcademyUpdate($content->id, 'Dealer academy content has been updated.');
        });
    }

    private function notifyDealersForAcademyUpdate(int $contentId, string $message): void
    {
        $now = now();
        $dealerIds = User::query()
            ->where('role', User::ROLE_DEALER)
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhereIn('status', ['active', 'approved']);
            })
            ->pluck('id');

        if ($dealerIds->isEmpty()) {
            return;
        }

        $rows = $dealerIds->map(fn ($dealerId) => [
            'user_id' => $dealerId,
            'message' => $message,
            'type' => 'dealer_academy',
            'data' => json_encode(['content_id' => $contentId]),
            'read' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        Notification::insert($rows);
    }
}