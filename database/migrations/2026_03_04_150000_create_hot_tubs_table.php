<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hot_tubs', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->string('product_type')->default('hot_tub');  // hot_tub | swim_spa
            $table->string('model');
            $table->string('tier')->nullable();  // entry, mid, luxury etc.
            $table->unsignedSmallInteger('seats')->nullable();
            $table->unsignedSmallInteger('jets')->nullable();
            $table->string('dimensions')->nullable();  // e.g., 221 × 221 × 91 cm
            $table->string('power_requirements')->nullable();  // e.g., 240V, 50A
            $table->enum('status', ['active', 'inactive'])->default('active');

            // Expert review scores (0-5, one decimal)
            $table->decimal('comfort', 3, 1)->nullable();
            $table->decimal('efficiency', 3, 1)->nullable();
            $table->decimal('features', 3, 1)->nullable();
            $table->decimal('quality', 3, 1)->nullable();
            $table->decimal('value', 3, 1)->nullable();
            $table->decimal('overall', 3, 1)->nullable();

            $table->json('pros')->nullable();
            $table->json('cons')->nullable();
            $table->json('images')->nullable();  // store array of URLs for now

            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hot_tubs');
    }
};
