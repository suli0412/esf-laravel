<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KompetenzenSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['code' => 'DE_LESEN',    'bezeichnung' => 'Deutsch – Lesen'],
            ['code' => 'DE_HOEREN',   'bezeichnung' => 'Deutsch – Hören'],
            ['code' => 'DE_SCHREIBEN','bezeichnung' => 'Deutsch – Schreiben'],
            ['code' => 'DE_SPRECHEN', 'bezeichnung' => 'Deutsch – Sprechen'],
            ['code' => 'EN_GESAMT',   'bezeichnung' => 'Englisch – Gesamt'],
            ['code' => 'MATHE',       'bezeichnung' => 'Mathematik'],
            ['code' => 'IKT',         'bezeichnung' => 'IKT / Digitale Kompetenzen'],
        ];

        // idempotent einfügen (falls schon vorhanden, überspringen)
        foreach ($items as $it) {
            DB::table('kompetenzen')->updateOrInsert(
                ['code' => $it['code']],
                ['bezeichnung' => $it['bezeichnung']]
            );
        }
    }
}
