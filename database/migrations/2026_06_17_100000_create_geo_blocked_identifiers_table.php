<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geo_blocked_identifiers', function (Blueprint $table) {
            $table->id();
            $table->string('type', 16); // device | ip
            $table->string('identifier', 128);
            $table->string('reason', 64)->nullable();
            $table->string('last_ip', 45)->nullable();
            $table->timestamps();

            $table->unique(['type', 'identifier']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geo_blocked_identifiers');
    }
};
