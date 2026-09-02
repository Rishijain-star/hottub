<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'registration_ip')) {
                $table->string('registration_ip', 45)->nullable()->after('phone_verified_at');
                $table->index('registration_ip');
            }
            if (! Schema::hasColumn('users', 'registration_device_id')) {
                $table->string('registration_device_id', 64)->nullable()->after('registration_ip');
                $table->index('registration_device_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            foreach (['registration_device_id', 'registration_ip'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropIndex([$column]);
                    $table->dropColumn($column);
                }
            }
        });
    }
};
