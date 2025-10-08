<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Teilnehmer;

class KompetenzstandController extends Controller
{
    /**
     * Einzelnes Speichern einer Kompetenz-Zeile.
     * Erwartet: kompetenz_id, niveau_id (nullable), zeitpunkt in ['Eintritt','Austritt'], optional datum, bemerkung
     */
    public function store(Request $r, Teilnehmer $teilnehmer)
    {
        $data = $r->validate([
            'kompetenz_id' => ['required','integer','exists:kompetenzen,kompetenz_id'],
            'niveau_id'    => ['nullable','integer','exists:niveau,niveau_id'],
            'zeitpunkt'    => ['required','in:Eintritt,Austritt'],
            'datum'        => ['nullable','date'],
            'bemerkung'    => ['nullable','string','max:5000'],
        ]);

        DB::table('kompetenzstand')->updateOrInsert(
            [
                'teilnehmer_id' => (int)$teilnehmer->Teilnehmer_id,
                'kompetenz_id'  => (int)$data['kompetenz_id'],
                'zeitpunkt'     => $data['zeitpunkt'], // ENUM → zeitpunkt_norm wird automatisch gesetzt
            ],
            [
                'niveau_id' => isset($data['niveau_id']) && $data['niveau_id'] !== '' ? (int)$data['niveau_id'] : null,
                'datum'     => $data['datum'] ?? null,
                'bemerkung' => $data['bemerkung'] ?? null,
            ]
        );

        return back()->with('success', 'Kompetenzstand gespeichert.');
    }

    /**
     * Bulk-Speichern via "rows[*]" Struktur (wenn du eine generische Bulk-Form hast).
     * Erwartet: zeitpunkt, rows[*].kompetenz_id, rows[*].niveau_id, rows[*].datum, rows[*].bemerkung
     */
    public function bulkStore(Request $r, Teilnehmer $teilnehmer)
    {
        $data = $r->validate([
            'zeitpunkt'           => ['required','in:Eintritt,Austritt'],
            'rows'                => ['required','array'],
            'rows.*.kompetenz_id' => ['required','integer','exists:kompetenzen,kompetenz_id'],
            'rows.*.niveau_id'    => ['nullable','integer','exists:niveau,niveau_id'],
            'rows.*.datum'        => ['nullable','date'],
            'rows.*.bemerkung'    => ['nullable','string','max:5000'],
        ]);

        $affected = 0;

        DB::transaction(function () use ($data, $teilnehmer, &$affected) {
            foreach ($data['rows'] as $row) {
                // leere Auswahl überspringen
                if (!isset($row['niveau_id']) || $row['niveau_id'] === '' || $row['niveau_id'] === null) {
                    continue;
                }

                $ok = DB::table('kompetenzstand')->updateOrInsert(
                    [
                        'teilnehmer_id' => (int)$teilnehmer->Teilnehmer_id,
                        'kompetenz_id'  => (int)$row['kompetenz_id'],
                        'zeitpunkt'     => $data['zeitpunkt'],
                    ],
                    [
                        'niveau_id' => (int)$row['niveau_id'],
                        'datum'     => $row['datum'] ?? null,
                        'bemerkung' => $row['bemerkung'] ?? null,
                    ]
                );

                // updateOrInsert gibt bool zurück; wir zählen hier „versuchte“ Änderungen
                $affected += 1;
            }
        });

        return back()->with('success', "Kompetenzstände gespeichert ({$affected} Einträge verarbeitet).");
    }

    /**
     * Bulk-Speichern exakt passend zu deinem Partial:
     * - kompetenz[Eintritt][{kompetenz_id}] = niveau_id (optional)
     * - kompetenz[Austritt][{kompetenz_id}] = niveau_id (optional)
     * - kompetenz[datum][Eintritt|Austritt] = 'YYYY-MM-DD' (optional, global pro Zeitpunkt)
     * - kompetenz[bemerkung][Eintritt|Austritt] = string (optional, global pro Zeitpunkt)
     */
    public function bulkFromKompetenzForm(Request $r, Teilnehmer $teilnehmer)
    {
        $payload = $r->validate([
            'kompetenz' => ['required','array'],
            'kompetenz.Eintritt'   => ['nullable','array'],
            'kompetenz.Austritt'   => ['nullable','array'],
            'kompetenz.datum'      => ['nullable','array'],
            'kompetenz.datum.Eintritt' => ['nullable','date'],
            'kompetenz.datum.Austritt' => ['nullable','date'],
            'kompetenz.bemerkung'  => ['nullable','array'],
            'kompetenz.bemerkung.Eintritt' => ['nullable','string','max:5000'],
            'kompetenz.bemerkung.Austritt' => ['nullable','string','max:5000'],
        ]);

        $eintrittMap = (array)($payload['kompetenz']['Eintritt']   ?? []);
        $austrittMap = (array)($payload['kompetenz']['Austritt']   ?? []);
        $dateEin     = $payload['kompetenz']['datum']['Eintritt']  ?? null;
        $dateAus     = $payload['kompetenz']['datum']['Austritt']  ?? null;
        $bemEin      = $payload['kompetenz']['bemerkung']['Eintritt'] ?? null;
        $bemAus      = $payload['kompetenz']['bemerkung']['Austritt'] ?? null;

        $affected = 0;

        DB::transaction(function () use ($teilnehmer, $eintrittMap, $austrittMap, $dateEin, $dateAus, $bemEin, $bemAus, &$affected) {
            // Eintritt
            foreach ($eintrittMap as $kompetenzId => $niveauId) {
                if ($niveauId === null || $niveauId === '' ) continue;

                DB::table('kompetenzstand')->updateOrInsert(
                    [
                        'teilnehmer_id' => (int)$teilnehmer->Teilnehmer_id,
                        'kompetenz_id'  => (int)$kompetenzId,
                        'zeitpunkt'     => 'Eintritt',
                    ],
                    [
                        'niveau_id' => (int)$niveauId,
                        'datum'     => $dateEin,
                        'bemerkung' => $bemEin,
                    ]
                );
                $affected++;
            }

            // Austritt
            foreach ($austrittMap as $kompetenzId => $niveauId) {
                if ($niveauId === null || $niveauId === '' ) continue;

                DB::table('kompetenzstand')->updateOrInsert(
                    [
                        'teilnehmer_id' => (int)$teilnehmer->Teilnehmer_id,
                        'kompetenz_id'  => (int)$kompetenzId,
                        'zeitpunkt'     => 'Austritt',
                    ],
                    [
                        'niveau_id' => (int)$niveauId,
                        'datum'     => $dateAus,
                        'bemerkung' => $bemAus,
                    ]
                );
                $affected++;
            }
        });

        return back()->with('success', "Kompetenzstände gespeichert ({$affected} Einträge verarbeitet).");
    }
}
