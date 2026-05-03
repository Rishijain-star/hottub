<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('leads') && !Schema::hasColumn('leads', 'address')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->text('address')->nullable()->after('postcode');
            });
        }

        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table) {
                if (!Schema::hasColumn('brands', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('featured');
                }
                if (!Schema::hasColumn('brands', 'types')) {
                    $table->json('types')->nullable()->after('type');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'phone_verified_at')) {
                    $table->timestamp('phone_verified_at')->nullable()->after('phone');
                }
                if (!Schema::hasColumn('users', 'sms_otp_hash')) {
                    $table->string('sms_otp_hash', 64)->nullable()->after('phone_verified_at');
                }
                if (!Schema::hasColumn('users', 'sms_otp_expires_at')) {
                    $table->timestamp('sms_otp_expires_at')->nullable()->after('sms_otp_hash');
                }
                if (!Schema::hasColumn('users', 'admin_permissions')) {
                    $table->json('admin_permissions')->nullable()->after('role');
                }
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            try {
                DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user','dealer','admin','manufacturer','sub_admin') NOT NULL DEFAULT 'user'");
            } catch (\Throwable $e) {
                // Non-MySQL or already migrated
            }
        }

        if (!Schema::hasTable('site_settings')) {
            Schema::create('site_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        // Grandfather existing accounts at migration time (had no OTP requirement before).
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'phone_verified_at')) {
            DB::table('users')->whereNull('phone_verified_at')->update(['phone_verified_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                foreach (['admin_permissions', 'sms_otp_expires_at', 'sms_otp_hash', 'phone_verified_at'] as $col) {
                    if (Schema::hasColumn('users', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
            try {
                DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('user','dealer','admin','manufacturer') NOT NULL DEFAULT 'user'");
            } catch (\Throwable $e) {
            }
        }

        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $table) {
                if (Schema::hasColumn('brands', 'types')) {
                    $table->dropColumn('types');
                }
                if (Schema::hasColumn('brands', 'is_active')) {
                    $table->dropColumn('is_active');
                }
            });
        }

        if (Schema::hasTable('leads') && Schema::hasColumn('leads', 'address')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropColumn('address');
            });
        }
    }
};
