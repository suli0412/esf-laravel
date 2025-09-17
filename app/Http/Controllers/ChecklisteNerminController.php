<?php

namespace App\Http\Controllers;

use App\Models\Teilnehmer;
use App\Models\ChecklisteNermin;
use Illuminate\Http\Request;

class ChecklisteNerminController extends Controller
{
    public function edit(Teilnehmer $teilnehmer)
    {
        $checkliste = $teilnehmer->checkliste ?: new ChecklisteNermin(['Teilnehmer_id' => $teilnehmer->Teilnehmer_id]);
        return view('teilnehmer.checkliste_edit', compact('teilnehmer','checkliste'));
    }

    public function save(Request $request, Teilnehmer $teilnehmer)
    {
        $data = $request->validate([
            'AMS_Bericht'         => 'nullable|in:Gesendet,Nicht gesendet',
            'AMS_Lebenslauf'      => 'nullable|in:Gesendet,Nicht gesendet',
            'Erwerbsstatus'       => 'nullable|string|max:150',
            'VorzeitigerAustritt' => 'nullable|in:Ja,Nein',
            'IDEA'                => 'nullable|in:Gesendet,Nicht gesendet,k. VD/g. AW,offen',
        ]);

        $teilnehmer->checkliste()->updateOrCreate([], $data);

        return redirect()->route('teilnehmer.show', $teilnehmer)->with('success','Checkliste gespeichert.');
    }
}
