<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('featured_contents', function (Blueprint $table) {
            $table->id();
            $table->string('content_type'); // product_of_month, delivery_of_week
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('hot_tub_id')->nullable()->constrained('hot_tubs')->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('slug')->unique();
            $table->string('image_url')->nullable();
            $table->date('featured_from')->nullable();
            $table->date('featured_until')->nullable();
            $table->boolean('show_on_homepage')->default(true);
            $table->enum('status', ['active','inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('featured_contents');
    }
};

