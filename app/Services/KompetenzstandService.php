<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class KompetenzstandService
{
    /**
     * Speichert (upsert) Niveau je Kompetenz für Eintritt/Austritt.
     * $eintritt/$austritt: [kompetenz_id => niveau_id|null]
     */
    public function saveForTeilnehmer(int $teilnehmerId, array $eintritt, array $austritt): void
    {
        $rows = [];

        $build = function(array $map, string $zeitpunkt) use ($teilnehmerId, &$rows) {
            foreach ($map as $kompetenzId => $niveauId) {
                if ($niveauId === null || $niveauId === '' ) {
                    // Optional: Löschen, falls leer übermittelt wurde
                    DB::table('kompetenzstand')
                        ->where([
                            'teilnehmer_id' => $teilnehmerId,
                            'zeitpunkt'     => $zeitpunkt,
                            'kompetenz_id'  => (int)$kompetenzId,
                        ])->delete();
                    continue;
                }
                $rows[] = [
                    'teilnehmer_id' => $teilnehmerId,
                    'zeitpunkt'     => $zeitpunkt,
                    'kompetenz_id'  => (int)$kompetenzId,
                    'niveau_id'     => (int)$niveauId,
                    // Optional: 'datum' / 'bemerkung' hier befüllen, wenn Felder vorgesehen
                ];
            }
        };

        $build($eintritt, 'Eintritt');
        $build($austritt, 'Austritt');

        if (!empty($rows)) {
            DB::table('kompetenzstand')->upsert(
                $rows,
                ['teilnehmer_id','zeitpunkt','kompetenz_id'],
                ['niveau_id']
            );
        }
    }
}
