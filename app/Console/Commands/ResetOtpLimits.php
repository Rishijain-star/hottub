<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class ResetOtpLimits extends Command
{
    protected $signature = 'otp:reset-limits';

    protected $description = 'Clear OTP rate-limit counters and registration OTP throttle (for local/testing recovery)';

    public function handle(): int
    {
        $deleted = DB::table('cache')
            ->where(function ($q) {
                $q->where('key', 'like', '%otp%')
                    ->orWhere('key', 'like', '%registration_otp%');
            })
            ->delete();

        RateLimiter::clear('registration_otp_ip:127.0.0.1');

        Cache::flush();

        $this->info("Cleared {$deleted} OTP-related cache rows and rate limiters.");

        return self::SUCCESS;
    }
}
