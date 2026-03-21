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
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'creator_id')) {
                $table->unsignedBigInteger('creator_id')->nullable()->after('assigned_dealer_id');
                $table->string('lead_source')->nullable()->after('creator_id');
                $table->boolean('is_private')->default(false)->after('lead_source');
                
                $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
            }
        });

        Schema::create('maintenance_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dealer_id');
            $table->string('name'); // Basic, Premium, Platinum, or Custom
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->foreign('dealer_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('package_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Customer
            $table->unsignedBigInteger('dealer_id'); // Linked Dealer
            $table->unsignedBigInteger('package_id');
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'responded', 'closed'])->default('pending');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('dealer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('package_id')->references('id')->on('maintenance_packages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_requests');
        Schema::dropIfExists('maintenance_packages');
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['creator_id', 'lead_source', 'is_private']);
        });
    }
};
