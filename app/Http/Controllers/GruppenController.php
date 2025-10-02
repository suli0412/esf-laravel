<?php

namespace App\Http\Controllers;

use App\Models\Gruppe;
use App\Models\Projekt;
use App\Models\Mitarbeiter;
use App\Models\Teilnehmer;
use App\Models\TeilnehmerAnwesenheit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GruppenController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Liste: standardmäßig nur aktive, optional Filter via ?show=all|inactive */
    public function index(Request $request)
    {
        $show = $request->query('show'); // all|inactive|null

        $q = Gruppe::query()->with(['projekt','standardMitarbeiter'])->orderBy('name');

        if (Schema::hasColumn('gruppen', 'aktiv')) {
            if ($show === 'inactive') {
                $q->where('aktiv', 0);
            } elseif ($show === 'all') {
                // kein Filter
            } else {
                $q->where('aktiv', 1);
            }
        }

        $rows = $q->paginate(20);

        return view('gruppen.index', compact('rows'));
    }

    /** Formular anzeigen: Gruppe anlegen */
    public function create()
    {
        // Falls du Dropdowns brauchst, hier laden und an View übergeben
        return view('gruppen.create');
    }

    /**
     * Wochenansicht einer Gruppe (Mo–Fr anzeigen, Mo–So summieren)
     * Navigation via ?w=YYYY-Www oder ?kw=YYYY-Www
     */
    public function show(Gruppe $gruppe)
    {
        $kwParam = request('w') ?? request('kw');

        if ($kwParam && preg_match('/^(\d{4})-W(\d{1,2})$/', $kwParam, $m)) {
            $year      = (int) $m[1];
            $week      = (int) $m[2];
            $weekStart = Carbon::now()->setISODate($year, $week)->startOfWeek(Carbon::MONDAY);
        } else {
            $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
            $year      = $weekStart->isoWeekYear;
            $week      = $weekStart->isoWeek;
            $kwParam   = sprintf('%04d-W%02d', $year, $week);
        }

        $weekDays = collect(range(0, 4))->map(fn ($i) => $weekStart->copy()->addDays($i));
        $dateFrom = $weekStart->toDateString();
        $dateTo   = $weekStart->copy()->addDays(6)->toDateString();

        $members = $gruppe->teilnehmer()
            ->select('teilnehmer.Teilnehmer_id','teilnehmer.Nachname','teilnehmer.Vorname')
            ->orderBy('teilnehmer.Nachname')->orderBy('teilnehmer.Vorname')
            ->get();

        $ids = $members->pluck('Teilnehmer_id');

        $raw = TeilnehmerAnwesenheit::whereIn('teilnehmer_id', $ids)
            ->whereBetween('datum', [$dateFrom, $dateTo])
            ->get();

        $att = [];
        foreach ($raw as $r) {
            $dateKey = $r->datum instanceof Carbon
                ? $r->datum->toDateString()
                : (is_string($r->datum) ? substr($r->datum, 0, 10) : (string) $r->datum);

            $att[$r->teilnehmer_id][$dateKey] = [
                'status'      => $r->status,
                'fehlminuten' => (int) $r->fehlminuten,
            ];
        }

        $sumFehl = TeilnehmerAnwesenheit::selectRaw('teilnehmer_id, SUM(fehlminuten) AS sum_fm')
            ->whereIn('teilnehmer_id', $ids)
            ->whereBetween('datum', [$dateFrom, $dateTo])
            ->groupBy('teilnehmer_id')
            ->pluck('sum_fm', 'teilnehmer_id');

        $prevKw = sprintf('%04d-W%02d', $weekStart->copy()->subWeek()->isoWeekYear, $weekStart->copy()->subWeek()->isoWeek);
        $nextKw = sprintf('%04d-W%02d', $weekStart->copy()->addWeek()->isoWeekYear, $weekStart->copy()->addWeek()->isoWeek);

        $stati = ['anwesend','anwesend_verspaetet','abwesend','entschuldigt','religiöser_feiertag'];

        return view('gruppen.week', [
            'gruppe'    => $gruppe,
            'members'   => $members,
            'weekStart' => $weekStart,
            'weekDays'  => $weekDays,
            'kw'        => $kwParam,
            'prevKw'    => $prevKw,
            'nextKw'    => $nextKw,
            'att'       => $att,
            'sumFehl'   => $sumFehl,
            'stati'     => $stati,
        ]);
    }

    // Alias: /gruppen/{gruppe}/anwesenheit -> redirect auf show mit ?w / ?kw
    public function weekAlias(Gruppe $gruppe, Request $request)
    {
        $w = $request->query('w') ?? $request->query('kw');
        return redirect()->route('gruppen.show', ['gruppe' => $gruppe, 'w' => $w]);
    }

    /**
     * Speichert Wochen-Anwesenheit. Nichts wird gelöscht.
     */
    public function saveWeek(Gruppe $gruppe, Request $request)
    {
        $kw = $request->input('w') ?? $request->input('kw');
        if ($kw && preg_match('/^(\d{4})-W(\d{1,2})$/', $kw, $m)) {
            $weekStart = Carbon::now()->setISODate((int)$m[1], (int)$m[2])->startOfWeek(Carbon::MONDAY);
        } else {
            $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        }
        $dateFrom = $weekStart->toDateString();
        $dateTo   = $weekStart->copy()->addDays(6)->toDateString();

        $allowedInput = [
            'anwesend','anwesend_verspaetet','abwesend','entschuldigt','religiöser_feiertag',
            // Toleranz / alte Keys:
            'verspaetet','anwesend_mit_verspaetung','religioeser_feiertag',
        ];

        $toDb = static function (string $s): string {
            $map = [
                'anwesend_verspaetet'      => 'verspaetet',
                'anwesend_mit_verspaetung' => 'verspaetet',
                'verspaetet'               => 'verspaetet',
                'religiöser_feiertag'      => 'religioeser_feiertag',
                'religioeser_feiertag'     => 'religioeser_feiertag',
                'abwesend'                 => 'abwesend',
                'entschuldigt'             => 'entschuldigt',
                'anwesend'                 => 'anwesend',
            ];
            return $map[$s] ?? 'anwesend';
        };

        $isLate = static fn (string $s): bool => in_array($s, ['anwesend_verspaetet','anwesend_mit_verspaetung','verspaetet'], true);

        $anw        = (array) $request->input('anwesenheit', []);
        $fmTotalRaw = (array) $request->input('fehlminuten_total', []);

        $clampToQuarter = function (int $mins): int {
            if ($mins <= 0)   return 0;
            if ($mins >= 300) return 300;
            return max(0, min(300, (int) (round($mins / 15) * 15)));
        };

        // --- Logging: Zähler & Stichprobe sammeln
        $counterSetLate = 0;
        $counterSetNorm = 0;
        $touchedTeilnehmer = [];

        foreach ($anw as $teilnehmerId => $tage) {
            $teilnehmerId = (int) $teilnehmerId;
            $weekMins     = $clampToQuarter((int) ($fmTotalRaw[$teilnehmerId] ?? 0));

            foreach ((array) $tage as $datum => $statusInput) {
                if (!in_array($statusInput, $allowedInput, true)) {
                    continue;
                }

                $statusDb = $toDb($statusInput);

                // NICHTS löschen – nur setzen/erhöhen
                if ($isLate($statusInput)) {
                    $rec = TeilnehmerAnwesenheit::firstOrNew([
                        'teilnehmer_id' => $teilnehmerId,
                        'datum'         => $datum,
                    ]);
                    $rec->status      = 'verspaetet';
                    // Setze die Minuten exakt auf weekMins (idempotent pro Speichervorgang)
                    $rec->fehlminuten = $weekMins;
                    $rec->save();
                    $counterSetLate++;
                } else {
                    TeilnehmerAnwesenheit::updateOrCreate(
                        ['teilnehmer_id' => $teilnehmerId, 'datum' => $datum],
                        ['status' => $statusDb, 'fehlminuten' => 0]
                    );
                    $counterSetNorm++;
                }

                $touchedTeilnehmer[$teilnehmerId] = true;
            }
        }

        // ✨ Aktivitäts-Log: kompakte Zusammenfassung
        Log::channel('activity')->info('gruppe.week.saved', [
            'gruppe_id'        => $gruppe->getKey(),
            'kw'               => $kw ?: $weekStart->isoWeekYear.'-W'.str_pad((string)$weekStart->isoWeek, 2, '0', STR_PAD_LEFT),
            'date_range'       => [$dateFrom, $dateTo],
            'members_touched'  => count($touchedTeilnehmer),
            'rows_set_late'    => $counterSetLate,
            'rows_set_normal'  => $counterSetNorm,
            'by_user'          => auth()->id(),
        ]);

        return back()->with('success', 'Anwesenheit gespeichert.');
    }

    /** POST /gruppen – speichern */
    public function store(Request $r)
    {
        $data = $r->validate([
            'name'                    => ['required','string','max:150'],            // wichtig: name
            'code'                    => ['nullable','string','max:50','unique:gruppen,code'],
            'projekt_id'              => ['nullable','integer','exists:projekte,projekt_id'],
            'standard_mitarbeiter_id' => ['nullable','integer','exists:mitarbeiter,Mitarbeiter_id'],
            'aktiv'                   => ['sometimes','boolean'],
        ]);

        // Falls Spalte "aktiv" existiert: Standard = true, wenn Checkbox fehlt
        if (Schema::hasColumn('gruppen','aktiv')) {
            $data['aktiv'] = $r->has('aktiv') ? $r->boolean('aktiv') : true;
        } else {
            unset($data['aktiv']);
        }

        $gruppe = Gruppe::create($data);

        // ✨ Aktivitäts-Log
        Log::channel('activity')->info('gruppe.created', [
            'gruppe_id'               => $gruppe->getKey(),
            'name'                    => $gruppe->name,
            'code'                    => $gruppe->code,
            'projekt_id'              => $gruppe->projekt_id,
            'standard_mitarbeiter_id' => $gruppe->standard_mitarbeiter_id,
            'by_user'                 => auth()->id(),
        ]);

        return redirect()->route('gruppen.index')->with('success', 'Gruppe angelegt.');
    }

    /** Formular: bearbeiten */
    public function edit(Gruppe $gruppe)
    {
        $projekte    = Projekt::orderBy('bezeichnung')->get();
        $mitarbeiter = Mitarbeiter::orderBy('Nachname')->orderBy('Vorname')->get();

        return view('gruppen.edit', compact('gruppe','projekte','mitarbeiter'));
    }

    /** PUT/PATCH – aktualisieren */
    public function update(Request $r, Gruppe $gruppe)
    {
        $data = $r->validate([
            'name' => ['required','string','max:150'],
            'code' => [
                'nullable','string','max:50',
                Rule::unique('gruppen','code')->ignore($gruppe->getKey(), $gruppe->getKeyName()),
            ],
            'projekt_id'              => ['nullable','integer','exists:projekte,projekt_id'],
            'standard_mitarbeiter_id' => ['nullable','integer','exists:mitarbeiter,Mitarbeiter_id'],
            'aktiv'                   => ['sometimes','boolean'],
        ]);

        if (Schema::hasColumn('gruppen','aktiv')) {
            $data['aktiv'] = $r->boolean('aktiv', (bool) ($gruppe->aktiv ?? true));
        } else {
            unset($data['aktiv']);
        }

        // Für Logging: vorher/nachher vergleichen
        $before = [
            'name'                    => $gruppe->name,
            'code'                    => $gruppe->code,
            'projekt_id'              => $gruppe->projekt_id,
            'standard_mitarbeiter_id' => $gruppe->standard_mitarbeiter_id,
            'aktiv'                   => Schema::hasColumn('gruppen','aktiv') ? (bool)$gruppe->aktiv : null,
        ];

        $gruppe->update($data);

        $after = [
            'name'                    => $gruppe->name,
            'code'                    => $gruppe->code,
            'projekt_id'              => $gruppe->projekt_id,
            'standard_mitarbeiter_id' => $gruppe->standard_mitarbeiter_id,
            'aktiv'                   => Schema::hasColumn('gruppen','aktiv') ? (bool)$gruppe->aktiv : null,
        ];

        // changed keys ermitteln
        $changed = [];
        foreach ($after as $k => $v) {
            if (($before[$k] ?? null) !== $v) {
                $changed[] = $k;
            }
        }

        // ✨ Aktivitäts-Log
        Log::channel('activity')->info('gruppe.updated', [
            'gruppe_id' => $gruppe->getKey(),
            'changed'   => $changed,
            'by_user'   => auth()->id(),
        ]);

        return redirect()->route('gruppen.index')->with('success','Gruppe aktualisiert.');
    }

    /**
     * „Löschen“: NICHT löschen – archivieren/deaktivieren.
     * Fallback: SoftDelete, wenn `deleted_at` existiert. Hart löschen wird vermieden.
     */
    public function destroy(Gruppe $gruppe)
    {
        if (Schema::hasColumn('gruppen','aktiv')) {
            $gruppe->update(['aktiv' => 0]);

            // ✨ Aktivitäts-Log
            Log::channel('activity')->info('gruppe.deactivated', [
                'gruppe_id' => $gruppe->getKey(),
                'by_user'   => auth()->id(),
            ]);

            return redirect()->route('gruppen.index')->with('success','Gruppe deaktiviert.');
        }

        if (Schema::hasColumn('gruppen','deleted_at')) {
            // SoftDelete möglich
            $payload = [
                'gruppe_id' => $gruppe->getKey(),
                'by_user'   => auth()->id(),
            ];
            $gruppe->delete();

            // ✨ Aktivitäts-Log
            Log::channel('activity')->info('gruppe.archived', $payload);

            return redirect()->route('gruppen.index')->with('success','Gruppe archiviert.');
        }

        // Weder aktiv-Spalte noch SoftDelete vorhanden → lieber abbrechen statt hart löschen
        return redirect()->route('gruppen.index')
            ->with('error', 'Kein Archiv-Feld vorhanden – Gruppe wurde NICHT gelöscht.');
    }

    /** Optional: Reaktivieren (falls Button/Route vorhanden) */
    public function activate(Gruppe $gruppe)
    {
        if (Schema::hasColumn('gruppen','aktiv')) {
            $gruppe->update(['aktiv' => 1]);

            // ✨ Aktivitäts-Log
            Log::channel('activity')->info('gruppe.activated', [
                'gruppe_id' => $gruppe->getKey(),
                'by_user'   => auth()->id(),
            ]);

            return back()->with('success','Gruppe aktiviert.');
        }
        return back()->with('error','Kein aktiv-Feld vorhanden.');
    }

    /** Teilnehmer hinzufügen (Historie wahren: beitritt_bis zurücksetzen) */
    public function attachTeilnehmer(Request $r, Gruppe $gruppe)
    {
        $this->authorize('updateMembers', $gruppe);

        $data = $r->validate([
            'teilnehmer_id' => ['required','integer','exists:teilnehmer,Teilnehmer_id'],
            'beitritt_von'  => ['nullable','date'],
        ]);

        $gruppe->teilnehmer()->syncWithoutDetaching([
            $data['teilnehmer_id'] => [
                'beitritt_von' => $data['beitritt_von'] ?? now()->toDateString(),
                'beitritt_bis' => null, // Wiederbeitritt → Ende zurücksetzen
            ],
        ]);

        // ✨ Aktivitäts-Log
        Log::channel('activity')->info('gruppe.member.attached', [
            'gruppe_id'     => $gruppe->getKey(),
            'teilnehmer_id' => $data['teilnehmer_id'],
            'beitritt_von'  => $data['beitritt_von'] ?? now()->toDateString(),
            'user_id'       => auth()->id(),
        ]);

        return back()->with('success', 'Teilnehmer zur Gruppe hinzugefügt.');
    }

    /**
     * Teilnehmer „entfernen“: NICHT detach (kein Datenverlust),
     * sondern beitritt_bis setzen (Standard: heute). Optional force=1 zum echten Detach.
     */
    public function detachTeilnehmer(Gruppe $gruppe, Teilnehmer $teilnehmer, Request $r)
    {
        $this->authorize('updateMembers', $gruppe);

        if ($r->boolean('force')) {
            // echter Austritt ohne Historie (nur wenn du das wirklich brauchst)
            $gruppe->teilnehmer()->detach($teilnehmer->getKey());

            // ✨ Aktivitäts-Log
            Log::channel('activity')->info('gruppe.member.detached', [
                'gruppe_id'     => $gruppe->getKey(),
                'teilnehmer_id' => $teilnehmer->getKey(),
                'user_id'       => auth()->id(),
            ]);

            return back()->with('success', 'Teilnehmer aus der Gruppe entfernt (ohne Historie).');
        }

        $bis = $r->input('beitritt_bis') ?: now()->toDateString();

        // setzt nur das Ende-Datum in der Pivot – Historie bleibt erhalten
        $gruppe->teilnehmer()->updateExistingPivot($teilnehmer->getKey(), [
            'beitritt_bis' => $bis,
        ]);

        // ✨ Aktivitäts-Log
        Log::channel('activity')->info('gruppe.member.deactivated', [
            'gruppe_id'     => $gruppe->getKey(),
            'teilnehmer_id' => $teilnehmer->getKey(),
            'beitritt_bis'  => $bis,
            'user_id'       => auth()->id(),
        ]);

        return back()->with('success', 'Teilnehmer abgemeldet (Historie erhalten).');
    }
}
