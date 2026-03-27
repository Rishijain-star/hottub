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
        Schema::create('credit_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('credits');
            $table->decimal('price', 10, 2);
            $table->integer('validity_days');
            $table->string('badge_type')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('credit_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('credit_plan_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->integer('credits_added');
            $table->string('payment_id')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_purchases');
        Schema::dropIfExists('credit_plans');
    }
};
