<?php

namespace App\Http\Controllers;

use App\Models\Pruefungstermin;
use App\Models\Niveau;
use App\Models\Teilnehmer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class PruefungsterminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Liste mit optionalen Filtern (ab=upcoming|past|all, q=Suche, niveau_id=Filter) */
    public function index(Request $request)
    {
        $scope = $request->query('ab', 'upcoming'); // upcoming|past|all
        $q     = trim((string)$request->query('q', ''));
        $niv   = $request->query('niveau_id');

        $builder = \App\Models\Pruefungstermin::with('niveau')
            ->withCount('teilnehmer'); // <-- wichtig

        // Zeitraum-Filter
        if ($scope === 'upcoming') {
            $builder->whereDate('start_at', '>=', \Carbon\Carbon::today());
        } elseif ($scope === 'past') {
            $builder->whereDate('start_at', '<', \Carbon\Carbon::today());
        }

        // Textsuche
        if ($q !== '') {
            $builder->where(function ($x) use ($q) {
                $x->where('institut', 'like', "%{$q}%")
                ->orWhere('bezeichnung', 'like', "%{$q}%")
                ->orWhere('titel', 'like', "%{$q}%");
            });
        }

        // Niveau-Filter
        if ($niv) {
            $builder->where('niveau_id', $niv);
        }

        // Sortierung + Pagination (wichtig: den $builder verwenden)
        $rows = $builder
            ->orderBy('start_at', 'asc')
            ->paginate(20)
            ->withQueryString();

        // Niveaus für Filter
        $niveaus = \Illuminate\Support\Facades\Schema::hasColumn('niveau', 'code')
            ? \App\Models\Niveau::orderBy('code')->get()
            : (\Illuminate\Support\Facades\Schema::hasColumn('niveau', 'sort_order')
                ? \App\Models\Niveau::orderBy('sort_order')->get()
                : \App\Models\Niveau::query()->get());

        return view('pruefungstermine.index', [
            'rows'    => $rows,
            'termine' => $rows,   // falls deine View diesen Namen nutzt
            'scope'   => $scope,
            'q'       => $q,
            'niv'     => $niv,
            'niveaus' => $niveaus,
        ]);
    }


        /** Formular: Neuer Termin */
        public function create()
        {
            $niveaus = Schema::hasColumn('niveau', 'sort_order')
                ? Niveau::orderBy('sort_order')->get()
                : Niveau::orderBy('code')->get();

            return view('pruefungstermine.edit', [
                'termin'  => new Pruefungstermin(),
                'niveaus' => $niveaus,
            ]);
        }
    /** Speichern: Neuer Termin */
    public function store(Request $r)
    {
        $data = $r->validate([
            'niveau_id'   => 'required|integer|exists:niveau,niveau_id',
            'bezeichnung' => 'nullable|string|max:150',
            'titel'       => 'nullable|string|max:150',
            'start_at'    => 'required|date',
            'end_at'      => 'required|date|after:start_at',
            'institut'    => 'nullable|string|max:100',
        ]);

        $start = Carbon::parse($data['start_at']);
        $end   = Carbon::parse($data['end_at']);

        if ($this->overlapsExists($start, $end)) {
            throw ValidationException::withMessages([
                'start_at' => 'Es existiert bereits ein Prüfungstermin, der sich zeitlich überschneidet.',
                'end_at'   => 'Bitte wähle einen anderen Zeitraum.',
            ])->redirectTo(url()->previous())->status(422);
        }

        // Kompatibilität: datum (reines Datum) aus start_at ableiten
        $data['datum'] = $start->toDateString();

        $termin = Pruefungstermin::create($data);

        Log::channel('activity')->info('Prüfungstermin angelegt', [
        'termin_id' => $termin->getKey(),
        'titel'     => $termin->titel,
        'start_at'  => $termin->start_at,
        'end_at'    => $termin->end_at,
        'by_user'   => auth()->id(),
    ]);

        return redirect()->route('pruefungstermine.show', $termin)->with('success', 'Termin angelegt.');
    }

    /** Detailseite + schnelle Suche/Buchungen */
    public function show(Pruefungstermin $termin, Request $r)
    {
        $q = trim((string)$r->get('q', ''));
        $treffer = collect();

        if ($q !== '') {
            $treffer = \App\Models\Teilnehmer::select('Teilnehmer_id','Nachname','Vorname')
                ->where('Nachname','like',"%{$q}%")
                ->orWhere('Vorname','like',"%{$q}%")
                ->orderBy('Nachname')->orderBy('Vorname')
                ->limit(25)
                ->get();
        }

        $buchungen = $termin->teilnehmer()->withPivot('bestanden', 'selbstzahler')->get();

        // NEU: Dropdown füllen (ggf. Limit setzen, falls viele Datensätze)
            $alleTn = \App\Models\Teilnehmer::select('Teilnehmer_id','Nachname','Vorname')
            ->orderBy('Nachname')->orderBy('Vorname')
            ->limit(300)
            ->get();

        return view('pruefungstermine.show', compact('termin', 'treffer', 'buchungen', 'q','alleTn'));
    }


        /** Formular: Bearbeiten */
        public function edit(Pruefungstermin $termin)
        {
            $niveaus = Schema::hasColumn('niveau', 'sort_order')
                ? Niveau::orderBy('sort_order')->get()
                : Niveau::orderBy('code')->get();

            return view('pruefungstermine.edit', compact('termin', 'niveaus'));
        }

    /** Speichern: Update */
    public function update(Request $r, Pruefungstermin $termin)
    {
        $data = $r->validate([
            'niveau_id'   => 'required|integer|exists:niveau,niveau_id',
            'bezeichnung' => 'nullable|string|max:150',
            'titel'       => 'nullable|string|max:150',
            'start_at'    => 'required|date',
            'end_at'      => 'required|date|after:start_at',
            'institut'    => 'nullable|string|max:100',
        ]);

        $start = Carbon::parse($data['start_at']);
        $end   = Carbon::parse($data['end_at']);

        if ($this->overlapsExists($start, $end, $termin->getKey())) {
            throw ValidationException::withMessages([
                'start_at' => 'Es existiert bereits ein Prüfungstermin, der sich zeitlich überschneidet.',
                'end_at'   => 'Bitte wähle einen anderen Zeitraum.',
            ])->redirectTo(url()->previous())->status(422);
        }

        // Kompatibilität
        $data['datum'] = $start->toDateString();

        $termin->update($data);
        Log::channel('activity')->info('Prüfungstermin angelegt', [
        'termin_id' => $termin->getKey(),
        'titel'     => $termin->titel,
        'start_at'  => $termin->start_at,
        'end_at'    => $termin->end_at,
        'by_user'   => auth()->id(),
    ]);

        return redirect()->route('pruefungstermine.show', $termin)->with('success', 'Termin aktualisiert.');
    }

    /** Prüft, ob irgendein anderer Termin mit [start,end) kollidiert. */
    private function overlapsExists(Carbon $start, Carbon $end, ?int $ignoreId = null): bool
    {
        $q = Pruefungstermin::query();
        if ($ignoreId) {
            $q->where('termin_id', '!=', $ignoreId);
        }

        // Overlap-Bedingung: A.start < B.end && A.end > B.start
        return $q->where(function ($w) use ($start, $end) {
                $w->where('start_at', '<', $end)
                  ->where('end_at', '>', $start);
            })
            ->exists();
    }

    /** Löschen */
    public function destroy(Pruefungstermin $termin)
    {
        $termin->delete();

        return redirect()->route('pruefungstermine.index')
            ->with('success', 'Termin gelöscht.');
    }

    /** .ics Import (einfacher Parser: SUMMARY/DTSTART/LOCATION) */
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

            // niveau_id: Request-Vorgabe oder Heuristik aus SUMMARY
            $niveauId = $data['niveau_id'] ?? $this->matchNiveauFromSummary($ev['summary'] ?? null);
            if (!$niveauId) continue;

            Pruefungstermin::create([
                'niveau_id'   => $niveauId,
                'bezeichnung' => $ev['summary'] ?? null,
                'titel'       => null,
                'datum'       => $datum,
                'institut'    => $data['institut'] ?? ($ev['location'] ?? null),
            ]);
            $created++;
        }

        Log::channel('activity')->info('Prüfungstermin angelegt', [
        'termin_id' => $termin->getKey(),
        'titel'     => $termin->titel,
        'start_at'  => $termin->start_at,
        'end_at'    => $termin->end_at,
        'by_user'   => auth()->id(),
    ]);

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
            $data['teilnehmer_id'] => [
                'selbstzahler' => $r->boolean('selbstzahler'),
                'bestanden'    => null,
            ]
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

    /* ================== Helfer ================== */

    /** sehr einfacher ICS-Parser */
    private function parseIcs(string $ics): array
    {
        $lines  = preg_split("/\r\n|\n|\r/", $ics);
        $events = [];
        $current = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === 'BEGIN:VEVENT') {
                $current = ['summary' => null, 'date' => null, 'location' => null];
                continue;
            }
            if ($line === 'END:VEVENT') {
                if ($current) $events[] = $current;
                $current = null;
                continue;
            }
            if ($current === null) continue;

            if (str_starts_with($line, 'SUMMARY')) {
                $current['summary'] = $this->splitIcsValue($line);
            } elseif (str_starts_with($line, 'DTSTART')) {
                $raw = $this->splitIcsValue($line);
                $raw = preg_replace('/^TZID=[^:]*:/', '', $raw);
                $raw = preg_replace('/T\d{6}Z?$/', '', $raw); // nur Datum behalten
                if (preg_match('/^\d{8}$/', $raw)) {
                    $current['date'] = Carbon::createFromFormat('Ymd', $raw)->toDateString();
                } else {
                    try { $current['date'] = Carbon::parse($raw)->toDateString(); } catch (\Throwable $e) {}
                }
            } elseif (str_starts_with($line, 'LOCATION')) {
                $current['location'] = $this->splitIcsValue($line);
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

        if (preg_match('/\b(A1|A2|B1|B2|C1|C2)\b/i', $summary, $m)) {
            $code = strtoupper($m[1]);
            $niv = Niveau::where('code', $code)->first();
            return $niv?->niveau_id;
        }
        return null;
    }

    /** Alternative Endpunkte (Alias-Namen beibehalten, falls woanders verlinkt) */
    public function attachTeilnehmer(Pruefungstermin $termin, Request $r)
    {
        return $this->buchen($r, $termin);
    }

    public function detachTeilnehmer(Pruefungstermin $termin, Teilnehmer $teilnehmer)
    {
        return $this->storno($termin, $teilnehmer);
    }
}
