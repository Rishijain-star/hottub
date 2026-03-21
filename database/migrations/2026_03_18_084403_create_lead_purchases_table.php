<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('lead_purchases')) {
            Schema::create('lead_purchases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
                $table->foreignId('dealer_id')->constrained('users')->cascadeOnDelete();
                $table->enum('buyer_role', ['dealer','manufacturer'])->default('dealer');
                $table->decimal('amount', 10, 2)->nullable();
                $table->timestamps();
                $table->unique(['lead_id','dealer_id']);
            });
        } else {
            Schema::table('lead_purchases', function (Blueprint $table) {
                if (!Schema::hasColumn('lead_purchases', 'lead_id')) {
                    $table->foreignId('lead_id')->after('id')->constrained('leads')->cascadeOnDelete();
                }
                if (!Schema::hasColumn('lead_purchases', 'dealer_id')) {
                    $table->foreignId('dealer_id')->after('lead_id')->constrained('users')->cascadeOnDelete();
                }
                if (!Schema::hasColumn('lead_purchases', 'buyer_role')) {
                    $table->enum('buyer_role', ['dealer','manufacturer'])->default('dealer')->after('dealer_id');
                }
                if (!Schema::hasColumn('lead_purchases', 'amount')) {
                    $table->decimal('amount', 10, 2)->nullable()->after('buyer_role');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_purchases');
    }
};
