<?php

namespace App\Http\Controllers;

use App\Models\Beratung;
use Illuminate\Http\Request;

class BeratungController extends Controller
{


   public function index(Request $request) {
    $q = $request->string('q')->toString();

    $rows = \App\Models\Beratung::with(['teilnehmer','mitarbeiter','art','thema'])
        ->when($q, function($qbuilder) use ($q) {
            $qbuilder->whereHas('teilnehmer', function($w) use ($q){
                $w->where('Nachname','like',"%$q%")
                  ->orWhere('Vorname','like',"%$q%");
            })->orWhereHas('mitarbeiter', function($w) use ($q){
                $w->where('Nachname','like',"%$q%")
                  ->orWhere('Vorname','like',"%$q%");
            })->orWhereHas('thema', function($w) use ($q){
                $w->where('Bezeichnung','like',"%$q%");
            })->orWhereHas('art', function($w) use ($q){
                $w->where('Bezeichnung','like',"%$q%")
                  ->orWhere('Code','like',"%$q%");
            });
        })
        ->orderByDesc('datum')
        ->paginate(20)->withQueryString();

    $arten           = \App\Models\Beratungsart::orderBy('Code')->get();
    $themen          = \App\Models\Beratungsthema::orderBy('Bezeichnung')->get();
    $teilnehmerList  = \App\Models\Teilnehmer::orderBy('Nachname')->orderBy('Vorname')->get();
    $mitarbeiterList = \App\Models\Mitarbeiter::orderBy('Nachname')->orderBy('Vorname')->get();

    return view('beratungen.index', compact(
        'rows','q','arten','themen','teilnehmerList','mitarbeiterList'
    ));
}
    public function store(Request $request)
    {
        $data = $request->validate([
            'art_id'         => 'required|exists:beratungsarten,Art_id',
            'thema_id'       => 'required|exists:beratungsthemen,Thema_id',
            'teilnehmer_id'  => 'required|exists:teilnehmer,Teilnehmer_id',
            'mitarbeiter_id' => 'nullable|exists:mitarbeiter,Mitarbeiter_id',
            'datum'          => 'required|date',
            'dauer_h'        => 'nullable|numeric|min:0|max:24',
            'notizen'        => 'nullable|string',
        ]);

        Beratung::create($data);

        return back()->with('success', 'Beratung gespeichert.');
    }
}
