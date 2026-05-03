<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('invoices')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'credit_plan_id')) {
                $table->unsignedBigInteger('credit_plan_id')->nullable()->after('dealer_id');
            }

            if (!Schema::hasColumn('invoices', 'plan_name')) {
                $table->string('plan_name')->nullable()->after('credit_plan_id');
            }

            if (!Schema::hasColumn('invoices', 'plan_description')) {
                $table->text('plan_description')->nullable()->after('plan_name');
            }

            if (!Schema::hasColumn('invoices', 'currency')) {
                $table->string('currency', 10)->default('GBP')->after('plan_description');
            }

            if (!Schema::hasColumn('invoices', 'stripe_session_id')) {
                $table->string('stripe_session_id')->nullable()->after('payment_id');
            }

            if (!Schema::hasColumn('invoices', 'payment_details')) {
                $table->json('payment_details')->nullable()->after('stripe_session_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('invoices')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            foreach ([
                'credit_plan_id',
                'plan_name',
                'plan_description',
                'currency',
                'stripe_session_id',
                'payment_details',
            ] as $col) {
                if (Schema::hasColumn('invoices', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

