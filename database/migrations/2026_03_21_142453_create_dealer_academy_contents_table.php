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
        Schema::create('dealer_academy_contents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('content_type'); // video, pdf, article, link
            $table->string('file_path')->nullable(); // For uploaded files (PDF/Video)
            $table->string('external_link')->nullable(); // For YouTube/Vimeo links
            $table->string('category'); // Sales Training, Product Info, Installation, Service
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dealer_academy_contents');
    }
};
