<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('hot_tubs') && !Schema::hasColumn('hot_tubs', 'brand_id')) {
            Schema::table('hot_tubs', function (Blueprint $table) {
                $table->foreignId('brand_id')->nullable()->after('brand')->constrained('brands')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('hot_tubs') && Schema::hasColumn('hot_tubs', 'brand_id')) {
            Schema::table('hot_tubs', function (Blueprint $table) {
                $table->dropConstrainedForeignId('brand_id');
            });
        }
    }
};

