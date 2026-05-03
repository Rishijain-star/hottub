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
            if (!Schema::hasColumn('package_requests', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('package_requests')) {
            return;
        }

        Schema::table('package_requests', function (Blueprint $table) {
            if (Schema::hasColumn('package_requests', 'cancellation_reason')) {
                $table->dropColumn('cancellation_reason');
            }
        });
    }
};
