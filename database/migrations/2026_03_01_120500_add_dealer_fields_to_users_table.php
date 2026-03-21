<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Core dealer/account state
            $table->enum('status', ['pending', 'approved', 'revoked'])->default('pending')->after('role');
            $table->integer('credits')->default(0)->after('status');

            // Dealer profile fields
            $table->string('company_name')->nullable()->after('name');
            $table->string('company_number')->nullable()->after('company_name');
            $table->string('vat_number')->nullable()->after('company_number');
            $table->string('phone')->nullable()->after('password');
            $table->string('postcode')->nullable()->after('phone');
            $table->string('address', 1000)->nullable()->after('postcode');
            $table->string('website')->nullable()->after('address');

            // Classification
            $table->string('type')->nullable()->after('website');
            $table->json('service_offerings')->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'credits',
                'company_name',
                'company_number',
                'vat_number',
                'phone',
                'postcode',
                'address',
                'website',
                'type',
                'service_offerings',
            ]);
        });
    }
};

