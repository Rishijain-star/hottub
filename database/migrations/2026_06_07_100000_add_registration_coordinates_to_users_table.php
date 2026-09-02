<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('registration_lat', 10, 7)->nullable()->after('preferred_currency');
            $table->decimal('registration_lng', 10, 7)->nullable()->after('registration_lat');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['registration_lat', 'registration_lng']);
        });
    }
};
