<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $column) {
            $column->boolean('deposit_confirmed')->default(false)->after('stage');
            $column->timestamp('deposit_requested_at')->nullable()->after('deposit_confirmed');
        });

        Schema::table('notifications', function (Blueprint $column) {
            $column->string('type')->nullable()->after('message');
            $column->json('data')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $column) {
            $column->dropColumn(['deposit_confirmed', 'deposit_requested_at']);
        });

        Schema::table('notifications', function (Blueprint $column) {
            $column->dropColumn(['type', 'data']);
        });
    }
};
