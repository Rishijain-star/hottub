<?php

use App\Models\Lead;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Lead::query()->orderBy('id')->chunk(100, function ($leads) {
            foreach ($leads as $lead) {
                $interests = $lead->interests;
                if (! is_array($interests) || $interests === []) {
                    continue;
                }
                $next = array_values(array_unique(array_map(
                    fn ($v) => $v === 'outdoor_kitchen' ? 'outdoor_product' : $v,
                    $interests
                )));
                if ($next !== $interests) {
                    $lead->interests = $next;
                    $lead->save();
                }
            }
        });
    }

    public function down(): void
    {
        // Intentionally empty: cannot distinguish legacy `outdoor_kitchen` rows from native `outdoor_product`.
    }
};
