<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class KompetenzstandService
{
    // neu: $pruneMissing optional
    public function saveForTeilnehmer(int $teilnehmerId, array $eintrittMap, array $austrittMap, bool $pruneMissing = false): void
    {
        $this->persist($teilnehmerId, $eintrittMap, 'Eintritt', $pruneMissing);
        $this->persist($teilnehmerId, $austrittMap, 'Austritt', $pruneMissing);
    }

    private function persist(int $teilnehmerId, array $map, string $zeitpunkt, bool $pruneMissing = false): void
    {
        $seen = []; // nur für optionales Aufräumen

        foreach ($map as $kompetenzId => $niveauId) {
            $kid = (int) $kompetenzId;
            $nid = $niveauId !== null ? (int) $niveauId : null;

            // leere/ungültige Werte: überspringen (NICHT löschen)
            if ($kid <= 0 || $nid === null || $nid <= 0) {
                continue;
            }

            $seen[] = $kid;

            DB::table('kompetenzstand')->updateOrInsert(
                ['teilnehmer_id' => $teilnehmerId, 'zeitpunkt' => $zeitpunkt, 'kompetenz_id' => $kid],
                ['niveau_id' => $nid]
            );
        }

        // nur wenn ausdrücklich gewünscht
        if ($pruneMissing && !empty($seen)) {
            DB::table('kompetenzstand')
                ->where('teilnehmer_id', $teilnehmerId)
                ->where('zeitpunkt', $zeitpunkt)
                ->whereNotIn('kompetenz_id', $seen)
                ->delete();
        }
    }
}
