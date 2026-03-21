<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'stage')) {
                $table->string('stage')->default('New Lead')->after('status');
            }
            if (!Schema::hasColumn('leads', 'delivery_details')) {
                $table->json('delivery_details')->nullable()->after('stage');
            }
            if (!Schema::hasColumn('leads', 'invoice_path')) {
                $table->string('invoice_path')->nullable()->after('delivery_details');
            }
            if (!Schema::hasColumn('leads', 'warranty_path')) {
                $table->string('warranty_path')->nullable()->after('invoice_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'stage')) {
                $table->dropColumn('stage');
            }
            if (Schema::hasColumn('leads', 'delivery_details')) {
                $table->dropColumn('delivery_details');
            }
            if (Schema::hasColumn('leads', 'invoice_path')) {
                $table->dropColumn('invoice_path');
            }
            if (Schema::hasColumn('leads', 'warranty_path')) {
                $table->dropColumn('warranty_path');
            }
        });
    }
};
