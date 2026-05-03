<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hot_tubs')) {
            Schema::table('hot_tubs', function (Blueprint $table) {
                if (!Schema::hasColumn('hot_tubs', 'old_price')) {
                    $table->decimal('old_price', 10, 2)->nullable()->after('power_requirements');
                }
                if (!Schema::hasColumn('hot_tubs', 'new_price')) {
                    $table->decimal('new_price', 10, 2)->nullable()->after('old_price');
                }
            });
        }

        if (Schema::hasTable('outdoor_products')) {
            Schema::table('outdoor_products', function (Blueprint $table) {
                if (!Schema::hasColumn('outdoor_products', 'old_price')) {
                    $table->decimal('old_price', 10, 2)->nullable()->after('dimensions');
                }
                if (!Schema::hasColumn('outdoor_products', 'new_price')) {
                    $table->decimal('new_price', 10, 2)->nullable()->after('old_price');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('hot_tubs')) {
            Schema::table('hot_tubs', function (Blueprint $table) {
                if (Schema::hasColumn('hot_tubs', 'new_price')) {
                    $table->dropColumn('new_price');
                }
                if (Schema::hasColumn('hot_tubs', 'old_price')) {
                    $table->dropColumn('old_price');
                }
            });
        }

        if (Schema::hasTable('outdoor_products')) {
            Schema::table('outdoor_products', function (Blueprint $table) {
                if (Schema::hasColumn('outdoor_products', 'new_price')) {
                    $table->dropColumn('new_price');
                }
                if (Schema::hasColumn('outdoor_products', 'old_price')) {
                    $table->dropColumn('old_price');
                }
            });
        }
    }
};
