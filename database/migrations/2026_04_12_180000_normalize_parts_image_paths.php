<?php

use App\Support\PublicMedia;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix part image paths that incorrectly include "app/public/" or "storage/app/public/".
     * Files stay in storage/app/public/...; only the JSON strings are canonicalised.
     */
    public function up(): void
    {
        if (!Schema::hasTable('parts')) {
            return;
        }

        $rows = DB::table('parts')->select('id', 'images')->get();
        foreach ($rows as $row) {
            $images = json_decode($row->images ?? '[]', true);
            if (!is_array($images) || $images === []) {
                continue;
            }
            $changed = false;
            $next = [];
            foreach ($images as $img) {
                if (!is_string($img) || $img === '') {
                    $next[] = $img;
                    continue;
                }
                $norm = PublicMedia::normalizeStoredPath($img);
                if ($norm !== null && $norm !== $img) {
                    $changed = true;
                    $next[] = $norm;
                } else {
                    $next[] = $img;
                }
            }
            if ($changed) {
                DB::table('parts')->where('id', $row->id)->update([
                    'images' => json_encode(array_values($next)),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
    }
};
