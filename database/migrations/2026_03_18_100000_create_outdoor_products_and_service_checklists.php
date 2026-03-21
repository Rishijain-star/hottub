<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('outdoor_products', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->string('product_type')->default('outdoor_product');
            $table->string('model');
            $table->string('tier')->nullable();
            $table->string('dimensions')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            // Expert review scores (0-5, one decimal)
            $table->decimal('quality', 3, 1)->nullable();
            $table->decimal('durability', 3, 1)->nullable();
            $table->decimal('features', 3, 1)->nullable();
            $table->decimal('value', 3, 1)->nullable();
            $table->decimal('overall', 3, 1)->nullable();

            $table->json('pros')->nullable();
            $table->json('cons')->nullable();
            $table->json('images')->nullable();

            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('service_checklists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id'); // Link to the lead/customer
            $table->unsignedBigInteger('dealer_id');
            $table->json('checklist_data');
            $table->text('dealer_notes')->nullable();
            $table->text('customer_signature')->nullable(); // Base64 signature
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
            $table->foreign('dealer_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_checklists');
        Schema::dropIfExists('outdoor_products');
    }
};
