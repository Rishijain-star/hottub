<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('maintenance_packages')) {
            Schema::table('maintenance_packages', function (Blueprint $table) {
                if (!Schema::hasColumn('maintenance_packages', 'plan_type')) {
                    $table->string('plan_type', 20)->default('yearly')->after('price');
                }
                if (!Schema::hasColumn('maintenance_packages', 'is_most_popular')) {
                    $table->boolean('is_most_popular')->default(false)->after('plan_type');
                }
            });
        }

        if (Schema::hasTable('package_requests')) {
            Schema::table('package_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('package_requests', 'start_date')) {
                    $table->timestamp('start_date')->nullable()->after('status');
                }
                if (!Schema::hasColumn('package_requests', 'expiry_date')) {
                    $table->timestamp('expiry_date')->nullable()->after('start_date');
                }
                if (!Schema::hasColumn('package_requests', 'next_due_date')) {
                    $table->timestamp('next_due_date')->nullable()->after('expiry_date');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('maintenance_packages')) {
            Schema::table('maintenance_packages', function (Blueprint $table) {
                if (Schema::hasColumn('maintenance_packages', 'is_most_popular')) {
                    $table->dropColumn('is_most_popular');
                }
                if (Schema::hasColumn('maintenance_packages', 'plan_type')) {
                    $table->dropColumn('plan_type');
                }
            });
        }

        if (Schema::hasTable('package_requests')) {
            Schema::table('package_requests', function (Blueprint $table) {
                if (Schema::hasColumn('package_requests', 'next_due_date')) {
                    $table->dropColumn('next_due_date');
                }
                if (Schema::hasColumn('package_requests', 'expiry_date')) {
                    $table->dropColumn('expiry_date');
                }
                if (Schema::hasColumn('package_requests', 'start_date')) {
                    $table->dropColumn('start_date');
                }
            });
        }
    }
};
