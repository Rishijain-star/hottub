<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();
            $table->date('rate_date');
            $table->string('base_currency', 3)->default('GBP');
            $table->json('rates');
            $table->string('source', 64)->default('frankfurter');
            $table->timestamp('fetched_at');
            $table->timestamps();

            $table->unique(['rate_date', 'base_currency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currency_rates');
    }
};
