<?php

namespace App\Http\Controllers;

use App\Models\Mitarbeiter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Beratungsart;      //
use App\Models\Beratungsthema;    //
use App\Models\Teilnehmer;        //


class MitarbeiterController extends Controller
{
    public function index(Request $request)
    {
        $q = (string) $request->get('q', '');

        $mitarbeiter = Mitarbeiter::when($q, function ($query) use ($q) {
                $query->where('Nachname', 'like', "%{$q}%")
                      ->orWhere('Vorname', 'like', "%{$q}%")
                      ->orWhere('Email', 'like', "%{$q}%");
            })
            ->orderBy('Nachname')->orderBy('Vorname')
            ->paginate(20)
            ->withQueryString();

        return view('mitarbeiter.index', compact('mitarbeiter', 'q'));
    }

    public function create()
    {
        $taetigkeiten = ['Leitung','Verwaltung','Beratung','Bildung','Teamleitung','Praktikant','Andere'];
        return view('mitarbeiter.create', compact('taetigkeiten'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Nachname'      => 'required|string|max:100',
            'Vorname'       => 'required|string|max:100',
            'Taetigkeit'    => ['required', Rule::in(['Leitung','Verwaltung','Beratung','Bildung','Teamleitung','Praktikant','Andere'])],
            'Email'         => 'required|email|max:255|unique:mitarbeiter,Email',
            'Telefonnummer' => 'nullable|string|max:50|unique:mitarbeiter,Telefonnummer',
        ]);

        $m = Mitarbeiter::create($data);

        return redirect()->route('mitarbeiter.show', $m)->with('success', 'Mitarbeiter angelegt.');
    }

public function show(Mitarbeiter $mitarbeiter)
    {
        $mitarbeiter->load(['beratungen.teilnehmer','gruppenBeratungen.teilnehmer']);

        $arten          = Beratungsart::orderBy('Code')->get();
        $themen         = Beratungsthema::orderBy('Bezeichnung')->get();
        $teilnehmerList = Teilnehmer::orderBy('Nachname')->orderBy('Vorname')->get();

        return view('mitarbeiter.show', compact('mitarbeiter','arten','themen','teilnehmerList'));
    }
    public function edit(Mitarbeiter $mitarbeiter)
    {
        $taetigkeiten = ['Leitung','Verwaltung','Beratung','Bildung','Teamleitung','Praktikant','Andere'];
        return view('mitarbeiter.edit', compact('mitarbeiter', 'taetigkeiten'));
    }

    public function update(Request $request, Mitarbeiter $mitarbeiter)
    {
        $data = $request->validate([
            'Nachname'      => 'required|string|max:100',
            'Vorname'       => 'required|string|max:100',
            'Taetigkeit'    => ['required', Rule::in(['Leitung','Verwaltung','Beratung','Bildung','Teamleitung','Praktikant','Andere'])],
            'Email'         => ['required','email','max:255', Rule::unique('mitarbeiter', 'Email')->ignore($mitarbeiter->Mitarbeiter_id, 'Mitarbeiter_id')],
            'Telefonnummer' => ['nullable','string','max:50', Rule::unique('mitarbeiter','Telefonnummer')->ignore($mitarbeiter->Mitarbeiter_id, 'Mitarbeiter_id')],
        ]);

        $mitarbeiter->update($data);

        return redirect()->route('mitarbeiter.show', $mitarbeiter)->with('success', 'Mitarbeiter aktualisiert.');
    }

    public function destroy(Mitarbeiter $mitarbeiter)
    {
        $mitarbeiter->delete();
        return redirect()->route('mitarbeiter.index')->with('success', 'Mitarbeiter gelöscht.');
    }


}
