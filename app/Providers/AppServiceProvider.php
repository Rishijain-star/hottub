<?php

namespace App\Providers;

use App\Models\DealerAcademyContent;
use App\Models\Lead;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\URL;
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