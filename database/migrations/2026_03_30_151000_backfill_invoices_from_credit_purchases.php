<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('invoices') || !Schema::hasTable('credit_purchases')) {
            return;
        }

        if (!Schema::hasColumn('invoices', 'source_credit_purchase_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->unsignedBigInteger('source_credit_purchase_id')->nullable()->after('credit_plan_id');
                $table->index('source_credit_purchase_id');
            });
        }

        $hasPlanColumns = Schema::hasColumn('invoices', 'plan_name')
            && Schema::hasColumn('invoices', 'plan_description')
            && Schema::hasColumn('invoices', 'currency')
            && Schema::hasColumn('invoices', 'payment_details');

        DB::table('credit_purchases')
            ->orderBy('id')
            ->chunkById(200, function ($purchases) use ($hasPlanColumns) {
                foreach ($purchases as $purchase) {
                    $existing = DB::table('invoices')
                        ->where('source_credit_purchase_id', $purchase->id)
                        ->first();

                    if (!$existing && !empty($purchase->payment_id)) {
                        $existing = DB::table('invoices')
                            ->where('dealer_id', $purchase->user_id)
                            ->where('payment_id', $purchase->payment_id)
                            ->first();
                    }

                    if ($existing) {
                        continue;
                    }

                    $plan = DB::table('credit_plans')
                        ->select('id', 'name', 'description')
                        ->where('id', $purchase->credit_plan_id)
                        ->first();

                    $invoiceNumber = 'INV-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));
                    $purchaseStatus = strtolower((string) $purchase->status);
                    $invoiceStatus = in_array($purchaseStatus, ['paid', 'success', 'completed'], true)
                        ? 'paid'
                        : ($purchaseStatus === 'pending' ? 'pending' : 'failed');

                    $insert = [
                        'invoice_number' => $invoiceNumber,
                        'dealer_id' => $purchase->user_id,
                        'credits' => (int) $purchase->credits_added,
                        'amount' => (float) $purchase->amount,
                        'status' => $invoiceStatus,
                        'payment_id' => $purchase->payment_id,
                        'credit_plan_id' => $purchase->credit_plan_id,
                        'source_credit_purchase_id' => $purchase->id,
                        'created_at' => $purchase->created_at ?? now(),
                        'updated_at' => $purchase->updated_at ?? now(),
                    ];

                    if ($hasPlanColumns) {
                        $insert['plan_name'] = $plan?->name;
                        $insert['plan_description'] = $plan?->description;
                        $insert['currency'] = 'GBP';
                        $insert['payment_details'] = json_encode([
                            'backfilled' => true,
                            'source' => 'credit_purchases',
                            'source_credit_purchase_id' => $purchase->id,
                            'purchase_status' => $purchase->status,
                        ]);
                    }

                    DB::table('invoices')->insert($insert);
                }
            });
    }

    public function down(): void
    {
        if (!Schema::hasTable('invoices')) {
            return;
        }

        if (Schema::hasColumn('invoices', 'source_credit_purchase_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropIndex(['source_credit_purchase_id']);
                $table->dropColumn('source_credit_purchase_id');
            });
        }
    }
};

