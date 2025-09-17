<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // nur einfügen, was noch nicht in der Pivot steht
        DB::statement("
            INSERT INTO gruppe_teilnehmer (gruppe_id, teilnehmer_id, created_at, updated_at)
            SELECT t.gruppe_id, t.Teilnehmer_id, NOW(), NOW()
            FROM teilnehmer t
            INNER JOIN gruppen g ON g.gruppe_id = t.gruppe_id
            LEFT JOIN gruppe_teilnehmer gt
                ON gt.gruppe_id = t.gruppe_id AND gt.teilnehmer_id = t.Teilnehmer_id
            WHERE t.gruppe_id IS NOT NULL
              AND gt.id IS NULL
        ");
    }

    public function down(): void
    {
        // optional: wieder löschen
        DB::statement("
            DELETE gt FROM gruppe_teilnehmer gt
            WHERE EXISTS (
              SELECT 1 FROM teilnehmer t
              WHERE t.gruppe_id = gt.gruppe_id
                AND t.Teilnehmer_id = gt.teilnehmer_id
            )
        ");
    }
};
