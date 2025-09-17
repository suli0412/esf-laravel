<?php

namespace App\Http\Controllers;

use App\Models\Pruefungstermin;
use App\Models\Niveau;
use App\Models\Teilnehmer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class PruefungsterminController extends Controller
{
    // App/Http/Controllers/PruefungsterminController.php
    public function index() {
    $termine = \App\Models\Pruefungstermin::with('niveau')->orderBy('datum','desc')->paginate(20);
    return view('pruefungstermine.index', compact('termine'));
    }
    public function create()
    {
        $niveaus = Niveau::orderBy('sort_order')->get();
        return view('pruefungen.termine.edit', ['termin' => new Pruefungstermin(), 'niveaus' => $niveaus]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'niveau_id'   => 'required|integer|exists:niveau,niveau_id',
            'bezeichnung' => 'nullable|string|max:150',
            'datum'       => 'required|date',
            'institut'    => 'nullable|string|max:100',
        ]);
        $termin = Pruefungstermin::create($data);
        return redirect()->route('pruefungstermine.show', $termin)->with('success', 'Termin angelegt.');
    }

    public function show(\App\Models\Pruefungstermin $pruefungstermin, Request $r) {
    $q = trim($r->get('q',''));
    $treffer = collect();
    if ($q !== '') {
        $treffer = \App\Models\Teilnehmer::where('Nachname','like',"%$q%")
        ->orWhere('Vorname','like',"%$q%")
        ->limit(10)->get();
    }
    $buchungen = $pruefungstermin->teilnehmer()->withPivot('bestanden','selbstzahler')->get();
    return view('pruefungstermine.show', compact('pruefungstermin','treffer','buchungen','q'));
    }

    public function edit(Pruefungstermin $termin)
    {
        $niveaus = Niveau::orderBy('sort_order')->get();
        return view('pruefungen.termine.edit', compact('termin','niveaus'));
    }

    public function update(Request $r, Pruefungstermin $termin)
    {
        $data = $r->validate([
            'niveau_id'   => 'required|integer|exists:niveau,niveau_id',
            'bezeichnung' => 'nullable|string|max:150',
            'datum'       => 'required|date',
            'institut'    => 'nullable|string|max:100',
        ]);
        $termin->update($data);
        return redirect()->route('pruefungstermine.show', $termin)->with('success', 'Termin aktualisiert.');
    }

    public function destroy(Pruefungstermin $termin)
    {
        $termin->delete();
        return redirect()->route('pruefungstermine.index')->with('success', 'Termin gelöscht.');
    }

    /** .ics Import */
    public function import(Request $r)
    {
        $data = $r->validate([
            'ics'        => 'required|file|mimes:ics,txt',
            'niveau_id'  => 'nullable|integer|exists:niveau,niveau_id', // optionaler Default
            'institut'   => 'nullable|string|max:100',
        ]);

        $contents = file_get_contents($r->file('ics')->getRealPath());
        $events   = $this->parseIcs($contents);

        $created = 0;
        foreach ($events as $ev) {
            $datum = $ev['date'] ?? null;
            if (!$datum) continue;

            // niveau: Default aus Request oder heuristisch aus SUMMARY (A1/A2/B1/…)
            $niveauId = $data['niveau_id'] ?? $this->matchNiveauFromSummary($ev['summary'] ?? null);
            if (!$niveauId) continue; // ohne Niveau keinen Eintrag erstellen

            Pruefungstermin::create([
                'niveau_id'   => $niveauId,
                'bezeichnung' => $ev['summary'] ?? null,
                'datum'       => $datum,
                'institut'    => $data['institut'] ?? ($ev['location'] ?? null),
            ]);
            $created++;
        }

        return back()->with('success', "{$created} Termine importiert.");
    }

    /** Teilnehmer buchen */
    public function buchen(Request $r, Pruefungstermin $termin)
    {
        $data = $r->validate([
            'teilnehmer_id' => 'required|integer|exists:teilnehmer,Teilnehmer_id',
            'selbstzahler'  => 'sometimes|boolean',
        ]);
        $termin->teilnehmer()->syncWithoutDetaching([
            $data['teilnehmer_id'] => ['selbstzahler' => $r->boolean('selbstzahler'), 'bestanden' => null]
        ]);
        return back()->with('success', 'Teilnehmer gebucht.');
    }

    /** Buchung stornieren */
    public function storno(Pruefungstermin $termin, Teilnehmer $teilnehmer)
    {
        $termin->teilnehmer()->detach($teilnehmer->Teilnehmer_id);
        return back()->with('success', 'Buchung storniert.');
    }

    /** Teilnahme-Status setzen (bestanden ja/nein) */
    public function status(Request $r, Pruefungstermin $termin, Teilnehmer $teilnehmer)
    {
        $data = $r->validate([
            'bestanden' => 'nullable|in:0,1',
        ]);
        $termin->teilnehmer()->updateExistingPivot($teilnehmer->Teilnehmer_id, [
            'bestanden' => $data['bestanden'],
        ]);
        return back()->with('success', 'Status aktualisiert.');
    }

    /** --- Helfer: ICS Parser sehr einfach (SUMMARY/DTSTART/LOCATION) --- */
    private function parseIcs(string $ics): array
    {
        $lines = preg_split("/\r\n|\n|\r/", $ics);
        $events = [];
        $current = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === 'BEGIN:VEVENT') {
                $current = ['summary' => null, 'date' => null, 'location' => null];
            } elseif ($line === 'END:VEVENT') {
                if ($current) $events[] = $current;
                $current = null;
            } elseif ($current !== null) {
                if (str_starts_with($line, 'SUMMARY')) {
                    $current['summary'] = $this->splitIcsValue($line);
                } elseif (str_starts_with($line, 'DTSTART')) {
                    $raw = $this->splitIcsValue($line);
                    // Formate: 20250915 oder 20250915T090000Z / TZID=...
                    $raw = preg_replace('/^TZID=[^:]*:/', '', $raw);
                    $raw = preg_replace('/T\d{6}Z?$/', '', $raw);
                    if (preg_match('/^\d{8}$/', $raw)) {
                        $current['date'] = Carbon::createFromFormat('Ymd', $raw)->toDateString();
                    } else {
                        // Letzter Versuch: parsebar?
                        try { $current['date'] = Carbon::parse($raw)->toDateString(); } catch (\Throwable $e) {}
                    }
                } elseif (str_starts_with($line, 'LOCATION')) {
                    $current['location'] = $this->splitIcsValue($line);
                }
            }
        }
        return $events;
    }

    private function splitIcsValue(string $line): string
    {
        $pos = strpos($line, ':');
        return $pos !== false ? trim(substr($line, $pos + 1)) : '';
    }

    /** Heuristik: Niveau aus SUMMARY wie „Deutsch A2“ -> map auf niveau_id */
    private function matchNiveauFromSummary(?string $summary): ?int
    {
        if (!$summary) return null;
        // suche A1, A2, B1, B2, C1, C2
        if (preg_match('/\b(A1|A2|B1|B2|C1|C2)\b/i', $summary, $m)) {
            $code = strtoupper($m[1]);
            $niv = Niveau::where('code', $code)->first();
            if ($niv) return $niv->niveau_id;
        }
        return null;
    }

    public function attachTeilnehmer(\App\Models\Pruefungstermin $pruefungstermin, Request $r) {
    $data = $r->validate([
        'teilnehmer_id' => ['required','integer','exists:teilnehmer,Teilnehmer_id'],
        'selbstzahler'  => ['sometimes','boolean'],
    ]);
    $pruefungstermin->teilnehmer()->syncWithoutDetaching([
        $data['teilnehmer_id'] => ['selbstzahler' => (bool)$r->boolean('selbstzahler')]
    ]);
    return back()->with('success','Teilnehmer gebucht.');
    }

    public function detachTeilnehmer(\App\Models\Pruefungstermin $pruefungstermin, \App\Models\Teilnehmer $teilnehmer) {
    $pruefungstermin->teilnehmer()->detach($teilnehmer->Teilnehmer_id);
    return back()->with('success','Buchung entfernt.');
    }
}
