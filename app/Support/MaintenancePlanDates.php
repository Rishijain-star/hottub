<?php

namespace App\Support;

use Carbon\CarbonImmutable;

class MaintenancePlanDates
{
    public const TYPE_MONTHLY = 'monthly';
    public const TYPE_YEARLY = 'yearly';

    public static function normalizeType(?string $type): string
    {
        $raw = strtolower(trim((string) $type));
        if ($raw === self::TYPE_YEARLY) {
            return self::TYPE_YEARLY;
        }

        return self::TYPE_MONTHLY;
    }

    /**
     * @return array{start_date:\Carbon\CarbonImmutable, expiry_date:\Carbon\CarbonImmutable, next_due_date:\Carbon\CarbonImmutable}
     */
    public static function calculate(string $planType, ?\DateTimeInterface $startAt = null): array
    {
        $type = self::normalizeType($planType);
        $start = $startAt
            ? CarbonImmutable::instance(\Carbon\Carbon::instance($startAt))
            : CarbonImmutable::now();

        $expiry = $type === self::TYPE_YEARLY
            ? $start->addYear()
            : $start->addMonth();

        return [
            'start_date' => $start,
            'expiry_date' => $expiry,
            'next_due_date' => $expiry,
        ];
    }
}
