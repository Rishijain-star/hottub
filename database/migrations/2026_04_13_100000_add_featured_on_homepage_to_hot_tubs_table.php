<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hot_tubs') && !Schema::hasColumn('hot_tubs', 'featured_on_homepage')) {
            Schema::table('hot_tubs', function (Blueprint $table) {
                $table->boolean('featured_on_homepage')->default(false)->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('hot_tubs') && Schema::hasColumn('hot_tubs', 'featured_on_homepage')) {
            Schema::table('hot_tubs', function (Blueprint $table) {
                $table->dropColumn('featured_on_homepage');
            });
        }
    }
};
