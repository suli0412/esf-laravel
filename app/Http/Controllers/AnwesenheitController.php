<?php

namespace App\Http\Controllers;

use App\Models\Teilnehmer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Gruppe;
use App\Models\TeilnehmerAnwesenheit as TA;
use Illuminate\Support\Carbon;


use Illuminate\Support\Facades\Log;


class AnwesenheitController extends Controller
{

public function index()
{
    $q         = trim(request('q', ''));
    $von       = request('von');
    $bis       = request('bis');
    $gruppeId  = request('gruppe_id');

    // Default-Zeitraum: aktuelle Woche (Mo–So)
    if (!$von || !$bis) {
        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $von = $von ?: $weekStart->toDateString();
        $bis = $bis ?: $weekStart->copy()->addDays(6)->toDateString();
    }

    // Grund-Query
    $qry = TA::query()->with('teilnehmer')
        ->whereBetween('datum', [$von, $bis]);

    // Filter Gruppe => nur TN aus dieser Gruppe
    $gruppe = null;
    if ($gruppeId) {
        $gruppe = Gruppe::find($gruppeId);
        if ($gruppe) {
            $ids = $gruppe->teilnehmer()->pluck('teilnehmer.Teilnehmer_id')->all();
            $qry->whereIn('teilnehmer_id', $ids ?: [-1]);
        }
    }

    // Suche (Name/Email des Teilnehmers)
    if ($q !== '') {
        $qry->whereHas('teilnehmer', function ($qq) use ($q) {
            $qq->where('Nachname', 'like', "%{$q}%")
               ->orWhere('Vorname', 'like', "%{$q}%")
               ->orWhere('Email', 'like', "%{$q}%");
        });
    }

    // Liste
    $rows = $qry->orderBy('datum', 'desc')->paginate(25)->withQueryString();

    // Summenpanel: Verspätungsminuten pro TN im Zeitraum (nur anwesend_verspaetet)
    $sumVerspaetet = TA::query()
        ->selectRaw('teilnehmer_id, SUM(IF(status = "anwesend_verspaetet", fehlminuten, 0)) AS sum_min')
        ->whereBetween('datum', [$von, $bis])
        ->when($gruppe, function ($qq) use ($gruppe) {
            $ids = $gruppe->teilnehmer()->pluck('teilnehmer.Teilnehmer_id')->all();
            $qq->whereIn('teilnehmer_id', $ids ?: [-1]);
        })
        ->when($q !== '', function ($qq) use ($q) {
            $qq->whereHas('teilnehmer', function ($q2) use ($q) {
                $q2->where('Nachname','like',"%{$q}%")
                   ->orWhere('Vorname','like',"%{$q}%")
                   ->orWhere('Email','like',"%{$q}%");
            });
        })
        ->groupBy('teilnehmer_id')
        ->with('teilnehmer')
        ->get()
        ->sortByDesc('sum_min'); // für Anzeige nach größter Summe

    // Gruppen für Dropdown
    $gruppen = Gruppe::orderBy('name')->get();

    return view('anwesenheit.index', compact(
        'rows','q','von','bis','gruppen','gruppe','gruppeId','sumVerspaetet'
    ));
}


            public function create()
    {
        $anwesenheit = new TA([
            'datum'   => now()->toDateString(),
            'status'  => 'anwesend',
            'fehlminuten' => 0,
        ]);

        $teilnehmer = Teilnehmer::orderBy('Nachname')->orderBy('Vorname')->get();
        $stati      = TA::STATI;

        return view('anwesenheit.create', compact('anwesenheit','teilnehmer','stati'));
    }

    public function edit(TA $anwesenheit)
    {
    $teilnehmer = Teilnehmer::orderBy('Nachname')->orderBy('Vorname')->get();
    $stati      = TA::STATI;

    // HIER: Creator/Updater für das Audit-Partial vorladen
    // Variante A (neue Relationsnamen aus dem Blameable-Trait):
    $anwesenheit->loadMissing([
        'createdBy:id,name,Vorname,Nachname',
        'updatedBy:id,name,Vorname,Nachname'
    ]);


    // $anwesenheit->loadMissing(['creator:id,name,Vorname,Nachname','updater:id,name,Vorname,Nachname']);

    return view('anwesenheit.edit', compact('anwesenheit','teilnehmer','stati'));
    }


    public function update(Request $request, TA $anwesenheit)
    {
        $data = $request->validate([
            'datum'         => ['required','date'],
            'teilnehmer_id' => ['required','integer','exists:teilnehmer,Teilnehmer_id'],
            'status'        => ['required','in:anwesend,anwesend_verspaetet,abwesend,entschuldigt,religiöser_feiertag'],
            'fehlminuten'   => ['nullable','integer','min:0','max:300'],
        ]);

        if ($data['status'] !== 'anwesend_verspaetet') {
            $data['fehlminuten'] = 0;
        }

        $anwesenheit->update($data);

        return redirect()->route('anwesenheit.index')->with('success','Gespeichert.');
    }

    public function destroy(TA $anwesenheit)
    {
        $anwesenheit->delete();
        return back()->with('success','Gelöscht.');
    }



    public function store(Request $request)
{
    $data = $request->validate([
        'teilnehmer_id' => ['required','integer','exists:teilnehmer,Teilnehmer_id'],
        'datum'         => [
            'required','date',
            Rule::unique('teilnehmer_anwesenheit', 'datum')
                ->where(fn($q) => $q->where('teilnehmer_id', $request->teilnehmer_id))
        ],
        'status'        => ['required', Rule::in(TA::STATI)], // <- TA statt TeilnehmerAnwesenheit
        'fehlminuten'   => ['nullable','integer','min:0','max:300'],
    ]);

    // Nur bei Verspätung Minuten berücksichtigen, sonst 0
    if ($data['status'] !== 'anwesend_verspaetet') {
        $data['fehlminuten'] = 0;
    } else {
        $mins = (int)($data['fehlminuten'] ?? 0);
        // optional: auf 15er-Schritte runden -> $mins = (int) (round($mins / 15) * 15);
        $data['fehlminuten'] = max(0, min(300, $mins));
    }

    TA::create($data); // <- TA statt TeilnehmerAnwesenheit

    return redirect()
        ->route('anwesenheit.index')
        ->with('success', 'Eintrag gespeichert.');
}




}
