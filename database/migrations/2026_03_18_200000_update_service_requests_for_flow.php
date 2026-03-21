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
        Schema::table('service_requests', function (Blueprint $table) {
            $table->json('checklist_data')->nullable()->after('message');
            $table->text('customer_review')->nullable()->after('checklist_data');
            $table->string('customer_signature')->nullable()->after('customer_review');
            // We can't easily update enum in SQLite/MySQL without doctrine/dbal or raw SQL
            // but for this task I will assume a standard Laravel environment.
            // Using raw SQL to ensure 'under_review' is added.
        });

        // Update enum status
        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM('pending', 'processing', 'under_review', 'completed') DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn(['checklist_data', 'customer_review', 'customer_signature']);
        });
        
        if (config('database.default') === 'mysql') {
            DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM('pending', 'processing', 'completed') DEFAULT 'pending'");
        }
    }
};
