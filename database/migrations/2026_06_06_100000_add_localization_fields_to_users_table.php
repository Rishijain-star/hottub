<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('country_code', 2)->nullable()->after('postcode');
            $table->string('preferred_locale', 10)->nullable()->after('country_code');
            $table->string('preferred_currency', 3)->nullable()->after('preferred_locale');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['country_code', 'preferred_locale', 'preferred_currency']);
        });
    }
};
