<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'dealer_lat')) {
                    $table->decimal('dealer_lat', 10, 7)->nullable()->after('postcode');
                }
                if (!Schema::hasColumn('users', 'dealer_lng')) {
                    $table->decimal('dealer_lng', 10, 7)->nullable()->after('dealer_lat');
                }
                if (!Schema::hasColumn('users', 'manufacturer_lat')) {
                    $table->decimal('manufacturer_lat', 10, 7)->nullable()->after('dealer_lng');
                }
                if (!Schema::hasColumn('users', 'manufacturer_lng')) {
                    $table->decimal('manufacturer_lng', 10, 7)->nullable()->after('manufacturer_lat');
                }
            });
        }
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                if (!Schema::hasColumn('leads', 'lead_postcode')) {
                    $table->string('lead_postcode')->nullable()->after('postcode');
                }
                if (!Schema::hasColumn('leads', 'lead_lat')) {
                    $table->decimal('lead_lat', 10, 7)->nullable()->after('lead_postcode');
                }
                if (!Schema::hasColumn('leads', 'lead_lng')) {
                    $table->decimal('lead_lng', 10, 7)->nullable()->after('lead_lat');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'dealer_lat')) $table->dropColumn('dealer_lat');
                if (Schema::hasColumn('users', 'dealer_lng')) $table->dropColumn('dealer_lng');
                if (Schema::hasColumn('users', 'manufacturer_lat')) $table->dropColumn('manufacturer_lat');
                if (Schema::hasColumn('users', 'manufacturer_lng')) $table->dropColumn('manufacturer_lng');
            });
        }
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                if (Schema::hasColumn('leads', 'lead_postcode')) $table->dropColumn('lead_postcode');
                if (Schema::hasColumn('leads', 'lead_lat')) $table->dropColumn('lead_lat');
                if (Schema::hasColumn('leads', 'lead_lng')) $table->dropColumn('lead_lng');
            });
        }
    }
};

