<?php

namespace App\Http\Controllers;

use App\Models\Gruppe;
use App\Models\Projekt;
use App\Models\Mitarbeiter;
use App\Models\Teilnehmer;
use App\Models\Anwesenheit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class GruppenController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $rows = Gruppe::with(['projekt','standardMitarbeiter'])
            ->orderBy('name')
            ->paginate(20);

        return view('gruppen.index', compact('rows'));
    }

    /**
     * Zeigt eine Gruppe inkl. Mitgliederliste (Teilnehmer) mit Suche & Pagination
     * + NEU: Datumsauswahl und Anwesenheits-Vorbelegung ($heuteMap) je Teilnehmer.
     */
    public function show(Request $request, Gruppe $gruppe)
    {
        $q     = trim((string)$request->input('q', ''));
        $datum = $request->input('datum', Carbon::today()->toDateString());

        // Basis-Query bleibt unverändert (Suche + Sortierung + Pagination)
        $teilnehmer = $gruppe->teilnehmer() // Hinweis: Relation im Gruppe-Model (belongsToMany/hasMany)
            ->when($q !== '', function ($qb) use ($q) {
                $qb->where(function ($w) use ($q) {
                    $w->where('Nachname', 'like', "%{$q}%")
                      ->orWhere('Vorname', 'like', "%{$q}%")
                      ->orWhere('Email', 'like', "%{$q}%");
                });
            })
            ->orderBy('Nachname')
            ->orderBy('Vorname')
            ->paginate(25)
            ->withQueryString();

        // NEU: Anwesenheiten des gewählten Datums in einem Rutsch laden (ohne N+1)
        $pk   = (new Teilnehmer)->getKeyName(); // z.B. 'Teilnehmer_id'
        $ids  = $teilnehmer->getCollection()->pluck($pk)->all();

        $anwForDate = [];
        if (!empty($ids)) {
            $anwForDate = Anwesenheit::query()
                ->whereIn('teilnehmer_id', $ids)
                ->where('gruppe_id', $gruppe->getKey())
                ->whereDate('datum', $datum)
                ->get()
                ->keyBy('teilnehmer_id'); // ->get('teilnehmer_id')->status
        }

        // Map: teilnehmer_id => bool|null (true/false = vorhanden, null = noch nicht erfasst)
        $heuteMap = [];
        foreach ($teilnehmer->getCollection() as $tn) {
            $found = $anwForDate[$tn->{$pk}] ?? null;
            $heuteMap[$tn->{$pk}] = $found ? (bool)$found->status : null;
        }

                // IDs, die schon Mitglied sind (Pivot + ggf. direkte FK-Zuweisung als Fallback)
        $currentIdsPivot  = \DB::table('gruppe_teilnehmer')
            ->where('gruppe_id', $gruppe->getKey())
            ->pluck('teilnehmer_id')->all();

        $currentIdsDirect = \App\Models\Teilnehmer::query()
            ->where('gruppe_id', $gruppe->getKey())
            ->pluck('Teilnehmer_id')->all();

        $currentIds = array_values(array_unique(array_merge($currentIdsPivot, $currentIdsDirect)));

        $add_q = trim((string) $request->input('add_q', ''));

        $availableTeilnehmer = \App\Models\Teilnehmer::query()
            ->when(!empty($currentIds), fn($q) => $q->whereNotIn('Teilnehmer_id', $currentIds))
            ->when($add_q !== '', function ($qb) use ($add_q) {
                $qb->where(function ($w) use ($add_q) {
                    $w->where('Nachname', 'like', "%{$add_q}%")
                    ->orWhere('Vorname',  'like', "%{$add_q}%")
                    ->orWhere('Email',    'like', "%{$add_q}%");
                });
            })
            ->orderBy('Nachname')->orderBy('Vorname')
            ->limit(50)
            ->get();


        return view('gruppen.show',
            compact('gruppe','teilnehmer','q','datum','heuteMap','availableTeilnehmer','add_q'));
    }

    public function create()
    {
        $gruppe    = new Gruppe(['aktiv' => 1]);
        $projekte  = Projekt::orderBy('bezeichnung')->get();
        $mitarbeiter = Mitarbeiter::orderBy('Nachname')->orderBy('Vorname')->get();

        return view('gruppen.create', compact('gruppe','projekte','mitarbeiter'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => ['required','string','max:150'],
            'code' => ['nullable','string','max:50','unique:gruppen,code'],
            'projekt_id' => ['nullable','integer','exists:projekte,projekt_id'],
            'standard_mitarbeiter_id' => ['nullable','integer','exists:mitarbeiter,Mitarbeiter_id'],
            'aktiv' => ['sometimes','boolean'],
        ]);
        $data['aktiv'] = $r->boolean('aktiv');

        $gruppe = Gruppe::create($data);

        return redirect()->route('gruppen.index')->with('success','Gruppe angelegt.');
    }

    public function edit(Gruppe $gruppe)
    {
        $projekte    = Projekt::orderBy('bezeichnung')->get();
        $mitarbeiter = Mitarbeiter::orderBy('Nachname')->orderBy('Vorname')->get();

        return view('gruppen.edit', compact('gruppe','projekte','mitarbeiter'));
    }

    public function update(Request $r, Gruppe $gruppe)
    {
        $data = $r->validate([
            'name' => ['required','string','max:150'],
            'code' => [
                'nullable','string','max:50',
                Rule::unique('gruppen','code')->ignore($gruppe->getKey(), $gruppe->getKeyName()),
            ],
            'projekt_id' => ['nullable','integer','exists:projekte,projekt_id'],
            'standard_mitarbeiter_id' => ['nullable','integer','exists:mitarbeiter,Mitarbeiter_id'],
            'aktiv' => ['sometimes','boolean'],
        ]);
        $data['aktiv'] = $r->boolean('aktiv');

        $gruppe->update($data);

        return redirect()->route('gruppen.index')->with('success','Gruppe aktualisiert.');
    }

    public function destroy(Gruppe $gruppe)
    {
        $gruppe->delete();
        return redirect()->route('gruppen.index')->with('success','Gruppe gelöscht.');
    }

    public function attachTeilnehmer(Request $r, Gruppe $gruppe)
    {
        $this->authorize('updateMembers', $gruppe);
        $data = $r->validate([
            'teilnehmer_id' => ['required','integer','exists:teilnehmer,Teilnehmer_id'],
            'beitritt_von'  => ['nullable','date'],
        ]);

        // doppelte Einträge vermeiden
        $gruppe->teilnehmer()->syncWithoutDetaching([
            $data['teilnehmer_id'] => [
                'beitritt_von' => $data['beitritt_von'] ?? now()->toDateString(),
            ],
        ]);

        return back()->with('success', 'Teilnehmer zur Gruppe hinzugefügt.');
    }

    public function detachTeilnehmer(Gruppe $gruppe, Teilnehmer $teilnehmer)
    {
         $this->authorize('updateMembers', $gruppe);
        $gruppe->teilnehmer()->detach($teilnehmer->getKey());
        return back()->with('success', 'Teilnehmer aus der Gruppe entfernt.');
    }
}
