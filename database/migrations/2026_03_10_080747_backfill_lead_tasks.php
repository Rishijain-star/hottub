<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Lead;

return new class extends Migration
{
    public function up(): void
    {
        $leads = Lead::all();
        foreach ($leads as $lead) {
            // Check if a 'Lead Created' activity exists
            $creationEventExists = DB::table('lead_activities')
                ->where('lead_id', $lead->id)
                ->where('content', 'like', 'Lead created%')
                ->exists();

            if (!$creationEventExists) {
                DB::table('lead_activities')->insert([
                    'lead_id' => $lead->id,
                    'type' => 'activity',
                    'content' => 'Lead created (backfilled)',
                    'created_at' => $lead->created_at,
                    'updated_at' => $lead->created_at,
                ]);
            }

            // Check if the 'Call Customer' task exists
            $callTaskExists = DB::table('lead_activities')
                ->where('lead_id', $lead->id)
                ->where('type', 'task')
                ->where('content', 'Call Customer')
                ->exists();

            if (!$callTaskExists) {
                DB::table('lead_activities')->insert([
                    'lead_id' => $lead->id,
                    'type' => 'task',
                    'content' => 'Call Customer',
                    'due_date' => $lead->created_at->addHours(2),
                    'created_at' => $lead->created_at,
                    'updated_at' => $lead->created_at,
                ]);
            }

            // Check if the 'Follow Up Customer' task exists
            $followUpTaskExists = DB::table('lead_activities')
                ->where('lead_id', $lead->id)
                ->where('type', 'task')
                ->where('content', 'Follow Up Customer')
                ->exists();

            if (!$followUpTaskExists) {
                DB::table('lead_activities')->insert([
                    'lead_id' => $lead->id,
                    'type' => 'task',
                    'content' => 'Follow Up Customer',
                    'due_date' => $lead->created_at->addDays(7),
                    'created_at' => $lead->created_at,
                    'updated_at' => $lead->created_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        // This migration is for data backfilling and is not reversible.
    }
};
