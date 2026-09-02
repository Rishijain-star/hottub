<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registered_users', function (Blueprint $table) {
            $table->id();
            $table->string('status', 32)->default('started')->index();
            $table->string('role', 32)->index();
            $table->string('name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone', 64)->nullable()->index();
            $table->string('postcode', 32)->nullable();
            $table->string('registration_ip', 45)->nullable()->index();
            $table->string('session_id', 128)->nullable()->index();
            $table->string('device_id', 64)->nullable()->index();
            $table->string('persistent_id', 64)->nullable()->index();
            $table->string('hardware_profile_hash', 64)->nullable()->index();
            $table->string('fingerprint_hash', 64)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->string('os_name', 64)->nullable()->index();
            $table->string('browser_name', 64)->nullable();
            $table->string('platform', 64)->nullable();
            $table->unsignedSmallInteger('sms_sent_count')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_sms_sent_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('block_reason')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registered_users');
    }
};
