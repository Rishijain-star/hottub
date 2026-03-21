<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('lead_purchases') && !Schema::hasColumn('lead_purchases', 'buyer_role')) {
            Schema::table('lead_purchases', function (Blueprint $table) {
                $table->enum('buyer_role', ['dealer','manufacturer'])->default('dealer')->after('dealer_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lead_purchases') && Schema::hasColumn('lead_purchases', 'buyer_role')) {
            Schema::table('lead_purchases', function (Blueprint $table) {
                $table->dropColumn('buyer_role');
            });
        }
    }
};

