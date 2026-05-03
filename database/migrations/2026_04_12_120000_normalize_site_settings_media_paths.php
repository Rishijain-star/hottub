<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Support\PublicMedia;

return new class extends Migration
{
    /**
     * Normalise homepage-related paths in site_settings so they match the public disk layout
     * (same files on disk; only string values in the database may change).
     */
    public function up(): void
    {
        if (!Schema::hasTable('site_settings')) {
            return;
        }

        foreach (['homepage_hero_bg', 'homepage_cta_image'] as $key) {
            $row = DB::table('site_settings')->where('key', $key)->first();
            if (!$row || $row->value === null || $row->value === '') {
                continue;
            }
            $normalized = PublicMedia::normalizeStoredPath($row->value);
            if ($normalized !== null && $normalized !== (string) $row->value) {
                DB::table('site_settings')->where('key', $key)->update([
                    'value' => $normalized,
                    'updated_at' => now(),
                ]);
            }
        }

        $row = DB::table('site_settings')->where('key', 'homepage_hero_images')->first();
        if (!$row || $row->value === null || $row->value === '') {
            return;
        }

        $arr = json_decode($row->value, true);
        if (!is_array($arr)) {
            return;
        }

        $changed = false;
        foreach ($arr as $idx => $item) {
            if (!is_array($item) || empty($item['path']) || !is_string($item['path'])) {
                continue;
            }
            $normalized = PublicMedia::normalizeStoredPath($item['path']);
            if ($normalized !== null && $normalized !== $item['path']) {
                $arr[$idx]['path'] = $normalized;
                $changed = true;
            }
        }

        if ($changed) {
            DB::table('site_settings')->where('key', 'homepage_hero_images')->update([
                'value' => json_encode(array_values($arr)),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * No-op: normalised values still reference the same files on disk.
     */
    public function down(): void
    {
    }
};
