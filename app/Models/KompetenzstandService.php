<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class KompetenzstandService
{
    public function saveForTeilnehmer(int $teilnehmerId, array $eintrittMap, array $austrittMap): void
    {
        // Eintritt-Werte speichern
        $this->persist($teilnehmerId, $eintrittMap, 'Eintritt');

        // Austritt-Werte speichern
        $this->persist($teilnehmerId, $austrittMap, 'Austritt');
    }

    private function persist(int $teilnehmerId, array $map, string $zeitpunkt): void
    {
        // vorhandene Kompetenzen fürs Cleanup merken
        $kompetenzIds = array_keys($map);

        foreach ($map as $kompetenzId => $niveauId) {
            DB::table('kompetenzstand')->updateOrInsert(
                [
                    'teilnehmer_id' => $teilnehmerId,
                    'zeitpunkt'     => $zeitpunkt,          // <-- HIER setzt du $zeitpunkt
                    'kompetenz_id'  => (int)$kompetenzId,
                ],
                [
                    'niveau_id' => (int)$niveauId,
                    // optional: Datum/Bemerkung, falls du die aus dem Formular mitlieferst
                    // 'datum'     => $datum ?? now()->toDateString(),
                    // 'bemerkung' => $bemerkung ?? null,
                ]
            );
        }

        // Optionales Aufräumen: Einträge entfernen, die jetzt nicht mehr gesetzt sind
        DB::table('kompetenzstand')
            ->where('teilnehmer_id', $teilnehmerId)
            ->where('zeitpunkt', $zeitpunkt)
            ->when(!empty($kompetenzIds), fn($q) => $q->whereNotIn('kompetenz_id', $kompetenzIds))
            ->delete();
    }
}
