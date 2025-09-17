<?php

namespace App\Http\Controllers;

use App\Models\GruppenBeratung;
use Illuminate\Http\Request;

class GruppenBeratungController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'art_id'        => 'required|exists:beratungsarten,Art_id',
            'thema_id'      => 'nullable|exists:beratungsthemen,Thema_id',
            'mitarbeiter_id'=> 'nullable|exists:mitarbeiter,Mitarbeiter_id',
            'datum'         => 'required|date',
            'dauer_h'       => 'nullable|numeric|min:0|max:24',
            'thema'         => 'nullable|string|max:255',
            'inhalt'        => 'nullable|string',
            'TNUnterlagen'  => 'nullable|boolean',
            'teilnehmer_ids'=> 'nullable|array',
            'teilnehmer_ids.*' => 'exists:teilnehmer,Teilnehmer_id',
        ]);

        $gb = GruppenBeratung::create([
            'art_id' => $data['art_id'],
            'thema_id' => $data['thema_id'] ?? null,
            'mitarbeiter_id' => $data['mitarbeiter_id'] ?? null,
            'datum' => $data['datum'],
            'dauer_h' => $data['dauer_h'] ?? null,
            'thema' => $data['thema'] ?? null,
            'inhalt' => $data['inhalt'] ?? null,
            'TNUnterlagen' => (int) ($request->boolean('TNUnterlagen')),
        ]);

        if (!empty($data['teilnehmer_ids'])) {
            $gb->teilnehmer()->attach($data['teilnehmer_ids']);
        }

        return back()->with('success','Gruppenberatung gespeichert.');
    }
}
