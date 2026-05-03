<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('package_requests')) {
            return;
        }

        Schema::table('package_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('package_requests', 'cancellation_type')) {
                $table->string('cancellation_type', 30)->nullable()->after('next_due_date');
            }
            if (!Schema::hasColumn('package_requests', 'cancellation_requested_at')) {
                $table->timestamp('cancellation_requested_at')->nullable()->after('cancellation_type');
            }
            if (!Schema::hasColumn('package_requests', 'cancellation_effective_at')) {
                $table->timestamp('cancellation_effective_at')->nullable()->after('cancellation_requested_at');
            }
            if (!Schema::hasColumn('package_requests', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancellation_effective_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('package_requests')) {
            return;
        }

        Schema::table('package_requests', function (Blueprint $table) {
            if (Schema::hasColumn('package_requests', 'cancelled_at')) {
                $table->dropColumn('cancelled_at');
            }
            if (Schema::hasColumn('package_requests', 'cancellation_effective_at')) {
                $table->dropColumn('cancellation_effective_at');
            }
            if (Schema::hasColumn('package_requests', 'cancellation_requested_at')) {
                $table->dropColumn('cancellation_requested_at');
            }
            if (Schema::hasColumn('package_requests', 'cancellation_type')) {
                $table->dropColumn('cancellation_type');
            }
        });
    }
};
