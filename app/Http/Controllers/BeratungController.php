<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Beratung;
use Illuminate\Support\Facades\Schema;
use App\Models\Beratungsart;
use App\Models\Beratungsthema;
use App\Models\Mitarbeiter;
use App\Models\Teilnehmer;
use Illuminate\Support\Facades\Log;



class BeratungController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        // Nur Berechtigte dürfen anlegen/ändern/löschen
        $this->middleware('can:beratung.manage')->only(['create','store','edit','update','destroy']);
        // Anzeigen (index/show) kannst du optional öffnen mit 'beratung.view'
    }
    public function index(\Illuminate\Http\Request $r)
    {
    $q   = trim($r->get('q',''));
    $von = $r->date('von');
    $bis = $r->date('bis');

    // Liste + Filter
    $rows = \App\Models\Beratung::query()
        ->with(['teilnehmer','mitarbeiter','art','thema'])
        ->when($q !== '', function ($w) use ($q) {
            $w->whereHas('teilnehmer', function ($t) use ($q) {
                $t->where('Nachname','like',"%$q%")
                  ->orWhere('Vorname','like',"%$q%")
                  ->orWhere('Email','like',"%$q%");
            });
        })
        ->when($von, fn($w) => $w->whereDate('datum','>=',$von))
        ->when($bis, fn($w) => $w->whereDate('datum','<=',$bis))
        ->orderByDesc('datum')
        ->paginate(20)
        ->withQueryString();

    // --- Select-Optionen (robust) ------------------------------------------
    // Beratungsarten
    if (\Illuminate\Support\Facades\Schema::hasTable('beratungsarten')) {
        $artCandidates = ['name','bezeichnung','titel','beschreibung','art','text'];
        $artLabelCol = collect($artCandidates)
            ->first(fn($c) => \Illuminate\Support\Facades\Schema::hasColumn('beratungsarten', $c))
            ?? 'Art_id';

        $artenQuery = \Illuminate\Support\Facades\DB::table('beratungsarten')->select('Art_id');
        if ($artLabelCol !== 'Art_id') {
            $artenQuery->addSelect(\Illuminate\Support\Facades\DB::raw("$artLabelCol as label"))
                       ->orderBy($artLabelCol);
        } else {
            $artenQuery->addSelect(\Illuminate\Support\Facades\DB::raw('Art_id as label'))
                       ->orderBy('Art_id');
        }
        $arten = $artenQuery->get();
    } else {
        $arten = collect();
    }

    // Beratungsthemen
    if (\Illuminate\Support\Facades\Schema::hasTable('beratungsthemen')) {
        $themaCandidates = ['name','bezeichnung','titel','beschreibung','thema','text'];
        $themaLabelCol = collect($themaCandidates)
            ->first(fn($c) => \Illuminate\Support\Facades\Schema::hasColumn('beratungsthemen', $c))
            ?? 'Thema_id';

        $themenQuery = \Illuminate\Support\Facades\DB::table('beratungsthemen')->select('Thema_id');
        if ($themaLabelCol !== 'Thema_id') {
            $themenQuery->addSelect(\Illuminate\Support\Facades\DB::raw("$themaLabelCol as label"))
                        ->orderBy($themaLabelCol);
        } else {
            $themenQuery->addSelect(\Illuminate\Support\Facades\DB::raw('Thema_id as label'))
                        ->orderBy('Thema_id');
        }
        $themen = $themenQuery->get();
    } else {
        $themen = collect();
    }

    // Stammdaten
    $mitarbeiter = \App\Models\Mitarbeiter::orderBy('Nachname')->orderBy('Vorname')
        ->get(['Mitarbeiter_id','Nachname','Vorname']);

    $teilnehmer = \App\Models\Teilnehmer::orderBy('Nachname')->orderBy('Vorname')
        ->get(['Teilnehmer_id','Nachname','Vorname','Email']);

    return view('beratung.index', compact(
        'rows','q','von','bis','arten','themen','mitarbeiter','teilnehmer'
    ));
}

/** Formular: Neu anlegen */
    public function create(Request $request)
    {
        $teilnehmerId = (int) $request->query('teilnehmer', 0);
        $teilnehmer   = $teilnehmerId ? Teilnehmer::find($teilnehmerId) : null;

        $arten       = Beratungsart::orderBy('Code')->get();
        $themen      = Beratungsthema::orderBy('Bezeichnung')->get();
        $mitarbeiter = Mitarbeiter::orderBy('Nachname')->orderBy('Vorname')->get();

        return view('beratungen.create', compact('arten','themen','mitarbeiter','teilnehmer'));
    }


    /** Speichern: Neu */
    public function store(Request $request)
    {
        $data = $request->validate([
            'teilnehmer_id'  => ['required','integer','exists:teilnehmer,Teilnehmer_id'],
            'art_id'         => ['required','integer','exists:beratungsarten,Art_id'],
            'thema_id'       => ['required','integer','exists:beratungsthemen,Thema_id'],
            'mitarbeiter_id' => ['nullable','integer','exists:mitarbeiter,Mitarbeiter_id'],
            'datum'          => ['required','date'],
            'dauer_h'        => ['nullable','numeric','min:0','max:24'],
            'notizen'        => ['nullable','string'],
        ]);

        $beratung = new Beratung();
        $beratung->teilnehmer_id  = $data['teilnehmer_id'];
        $beratung->art_id         = $data['art_id'];
        $beratung->thema_id       = $data['thema_id'];
        $beratung->mitarbeiter_id = $data['mitarbeiter_id'] ?? null;
        $beratung->datum          = $data['datum'];
        $beratung->dauer_h        = $data['dauer_h'] ?? null;
        $beratung->notizen        = $data['notizen'] ?? null;
        $beratung->save();

        return redirect()
            ->route('teilnehmer.show', $beratung->teilnehmer_id)
            ->with('status', 'Beratung wurde angelegt.');
    }

     /** Formular: Bearbeiten */
    public function edit(Beratung $beratung)
    {
        $arten       = Beratungsart::orderBy('Code')->get();
        $themen      = Beratungsthema::orderBy('Bezeichnung')->get();
        $mitarbeiter = Mitarbeiter::orderBy('Nachname')->orderBy('Vorname')->get();

        // Teilnehmer-Objekt nur für Anzeige-Header (Name)
        $teilnehmer = Teilnehmer::find($beratung->teilnehmer_id);

        return view('beratungen.edit', compact('beratung','arten','themen','mitarbeiter','teilnehmer'));
    }

    /** Speichern: Update */
    public function update(Request $request, Beratung $beratung)
    {
        $data = $request->validate([
            'teilnehmer_id'  => ['required','integer','exists:teilnehmer,Teilnehmer_id'],
            'art_id'         => ['required','integer','exists:beratungsarten,Art_id'],
            'thema_id'       => ['required','integer','exists:beratungsthemen,Thema_id'],
            'mitarbeiter_id' => ['nullable','integer','exists:mitarbeiter,Mitarbeiter_id'],
            'datum'          => ['required','date'],
            'dauer_h'        => ['nullable','numeric','min:0','max:24'],
            'notizen'        => ['nullable','string'],
        ]);

        $beratung->update($data);

        return redirect()
            ->route('teilnehmer.show', $beratung->teilnehmer_id)
            ->with('status', 'Beratung wurde aktualisiert.');
    }

    /** Löschen */
    public function destroy(Beratung $beratung)
    {
        $tnId = $beratung->teilnehmer_id;
        $beratung->delete();

        return redirect()
            ->route('teilnehmer.show', $tnId)
            ->with('status', 'Beratung wurde gelöscht.');
    }

    /** Wählt die erste vorhandene Spalte aus Kandidaten, sonst Fallback */
    private function pickCol(string $table, array $candidates, string $fallback): string
    {
        if (! Schema::hasTable($table)) {
            return $fallback;
        }
        $cols = Schema::getColumnListing($table);
        foreach ($candidates as $c) {
            if (in_array($c, $cols, true)) {
                return $c;
            }
        }
        return $fallback;
    }





}
