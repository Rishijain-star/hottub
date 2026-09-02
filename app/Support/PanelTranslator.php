<?php

namespace App\Support;

use App\Models\Notification;
use App\Models\User;

class PanelTranslator
{
    public static function notificationMessage(Notification $notification): string
    {
        $data = is_array($notification->data)
            ? $notification->data
            : (json_decode((string) $notification->data, true) ?: []);

        if ($notification->type === 'deposit_confirmation' && ! empty($data['dealer_id'])) {
            $actor = User::find($data['dealer_id']);
            if ($actor) {
                $roleKey = $actor->isManufacturer() ? 'manufacturer' : 'dealer';

                return __('panel.notifications.deposit_confirmation', [
                    'role' => __('panel.roles.'.$roleKey),
                    'name' => $actor->businessDisplayName(),
                ]);
            }
        }

        $key = match ($notification->type) {
            'available_leads' => 'panel.notifications.available_leads',
            'dealer_academy' => 'panel.notifications.dealer_academy',
            'dealer_academy_updated' => 'panel.notifications.dealer_academy_updated',
            default => null,
        };

        if ($key !== null) {
            $translated = __($key);

            return $translated !== $key ? $translated : (string) $notification->message;
        }

        return (string) $notification->message;
    }

    public static function interestLabel(string $tag): string
    {
        $slug = strtolower(str_replace(['-', ' '], '_', trim($tag)));
        $key = 'panel.interests.'.$slug;
        $translated = __($key);

        return $translated !== $key ? $translated : ucwords(str_replace('_', ' ', $slug));
    }

    public static function statusLabel(string $status): string
    {
        $slug = strtolower(str_replace(['-', ' '], '_', trim($status)));
        $key = 'panel.status.'.$slug;
        $translated = __($key);

        return $translated !== $key ? $translated : ucfirst(str_replace('_', ' ', $slug));
    }
}
