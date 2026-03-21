<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lead_purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('lead_purchases', 'stage')) {
                $table->string('stage')->default('New Lead')->after('amount');
            }
            if (!Schema::hasColumn('lead_purchases', 'delivery_details')) {
                $table->json('delivery_details')->nullable()->after('stage');
            }
            if (!Schema::hasColumn('lead_purchases', 'invoice_path')) {
                $table->string('invoice_path')->nullable()->after('delivery_details');
            }
            if (!Schema::hasColumn('lead_purchases', 'warranty_path')) {
                $table->string('warranty_path')->nullable()->after('invoice_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('lead_purchases', function (Blueprint $table) {
            $table->dropColumn(['stage', 'delivery_details', 'invoice_path', 'warranty_path']);
        });
    }
};
