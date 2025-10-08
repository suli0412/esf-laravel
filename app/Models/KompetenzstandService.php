<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class KompetenzstandService
{
    /**
     * @param int   $teilnehmerId
     * @param array $eintrittMap  Map [kompetenz_id => niveau_id]
     * @param array $austrittMap  Map [kompetenz_id => niveau_id]
     * @param array $meta         Optional: ['datum' => 'Y-m-d', 'bemerkung' => '...']
     * @param bool  $cleanupMissing Wenn true: entferne nicht gesendete Kompetenzen dieses Zeitpunktes
     */
    public function saveForTeilnehmer(
        int $teilnehmerId,
        array $eintrittMap,
        array $austrittMap,
        array $meta = [],
        bool $cleanupMissing = false
    ): void {
        DB::transaction(function () use ($teilnehmerId, $eintrittMap, $austrittMap, $meta, $cleanupMissing) {
            $this->persist($teilnehmerId, $this->cleanMap($eintrittMap),  'Eintritt', $meta, $cleanupMissing);
            $this->persist($teilnehmerId, $this->cleanMap($austrittMap), 'Austritt', $meta, $cleanupMissing);
        });
    }

    /**
     * Speichere/aktualisiere alle Einträge eines Zeitpunktes.
     */
    private function persist(
        int $teilnehmerId,
        array $map,
        string $zeitpunkt,
        array $meta,
        bool $cleanupMissing
    ): void {
        // Normiertes ENUM (sicher)
        $zeitpunkt = ($zeitpunkt === 'Austritt') ? 'Austritt' : 'Eintritt';

        // Optionales Zusatz-Meta
        $datum     = Arr::get($meta, 'datum');       // 'Y-m-d' (wenn mitgeben)
        $bemerkung = Arr::get($meta, 'bemerkung');   // string|null

        $touchedKomps = [];

        foreach ($map as $kompetenzId => $niveauId) {
            // nur sinnvolle Werte schreiben
            $kompetenzId = (int) $kompetenzId;
            $niveauId    = (int) $niveauId;
            if ($kompetenzId <= 0 || $niveauId <= 0) {
                continue;
            }

            $update = ['niveau_id' => $niveauId];
            if (!empty($datum))     $update['datum']     = $datum;
            if (!is_null($bemerkung)) $update['bemerkung'] = $bemerkung;

            DB::table('kompetenzstand')->updateOrInsert(
                [
                    'teilnehmer_id' => $teilnehmerId,
                    'zeitpunkt'     => $zeitpunkt,   // exakt ENUM
                    'kompetenz_id'  => $kompetenzId,
                ],
                $update
            );

            $touchedKomps[] = $kompetenzId;
        }

        // Cleanup NUR wenn explizit gewünscht – und nur wenn wir *irgendwas* gesendet haben
        if ($cleanupMissing && !empty($touchedKomps)) {
            DB::table('kompetenzstand')
                ->where('teilnehmer_id', $teilnehmerId)
                ->where('zeitpunkt', $zeitpunkt)
                ->whereNotIn('kompetenz_id', $touchedKomps)
                ->delete();
        }
    }

    /**
     * Normalisiert die Map [kompetenz_id => niveau_id]
     * - entfernt leere/0/null
     * - castet auf int
     */
    private function cleanMap(array $map): array
    {
        $out = [];
        foreach ($map as $kId => $nId) {
            $kId = (int) $kId;
            $nId = (int) $nId;
            if ($kId > 0 && $nId > 0) {
                $out[$kId] = $nId;
            }
        }
        return $out;
    }
}
