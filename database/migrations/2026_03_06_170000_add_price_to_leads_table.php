<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('leads') && !Schema::hasColumn('leads', 'price')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->decimal('price', 10, 2)->nullable()->after('message');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('leads') && Schema::hasColumn('leads', 'price')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropColumn('price');
            });
        }
    }
};
