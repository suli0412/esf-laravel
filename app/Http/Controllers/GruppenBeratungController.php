<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GruppenBeratung;
use App\Models\Beratungsart;
use App\Models\Beratungsthema;
use App\Models\Mitarbeiter;
use App\Models\Gruppe;
use App\Models\Teilnehmer;
use Illuminate\Validation\Rule;


use Illuminate\Support\Facades\Log;

class GruppenBeratungController extends Controller
{

    public function index(Request $request)
    {
        $q            = trim($request->get('q', ''));
        $von          = $request->date('von');
        $bis          = $request->date('bis');
        $gruppeId     = $request->integer('gruppe_id');
        $mitarbeiterId= $request->integer('mitarbeiter_id');
        $artId        = $request->integer('art_id');
        $themaId      = $request->integer('thema_id');

        $rows = GruppenBeratung::query()
            ->with(['mitarbeiter','gruppe','art','thema'])
            ->withCount('teilnehmer') // -> $row->teilnehmer_count
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('thema',   'like', "%{$q}%")
                      ->orWhere('inhalt','like', "%{$q}%");
                });
            })
            ->when($von, fn($qq) => $qq->whereDate('datum', '>=', $von))
            ->when($bis, fn($qq) => $qq->whereDate('datum', '<=', $bis))
            ->when($gruppeId, fn($qq) => $qq->where('gruppe_id', $gruppeId))
            ->when($mitarbeiterId, fn($qq) => $qq->where('mitarbeiter_id', $mitarbeiterId))
            ->when($artId, fn($qq) => $qq->where('art_id', $artId))
            ->when($themaId, fn($qq) => $qq->where('thema_id', $themaId))
            ->orderByDesc('datum')
            ->paginate(20)
            ->withQueryString();

        // Auswahllisten (für Filter/Formulare)
        $gruppen     = Gruppe::orderBy('name')->get();
        $mitarbeiter = Mitarbeiter::orderBy('Nachname')->orderBy('Vorname')->get();
        $arten       = Beratungsart::orderBy('Bezeichnung')->get();
        $themen      = Beratungsthema::orderBy('Bezeichnung')->get();

        return view('gruppen_beratungen.index', compact(
            'rows', 'q', 'von', 'bis', 'gruppeId', 'mitarbeiterId', 'artId', 'themaId',
            'gruppen','mitarbeiter','arten','themen'
        ));
    }

    /**
     * (Optional) Create-Form – nur wenn du eine Seite zum Anlegen willst.
     */
    public function create()
    {
        $gruppen     = Gruppe::orderBy('name')->get();
        $mitarbeiter = Mitarbeiter::orderBy('Nachname')->orderBy('Vorname')->get();
        $arten       = Beratungsart::orderBy('Bezeichnung')->get();
        $themen      = Beratungsthema::orderBy('Bezeichnung')->get();
        $teilnehmer  = Teilnehmer::orderBy('Nachname')->orderBy('Vorname')->get();

        return view('gruppen_beratungen.create', compact('gruppen','mitarbeiter','arten','themen','teilnehmer'));
    }

    /**
     * Speichern (deine bestehende Route: POST /gruppen-beratungen).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'gruppe_id'      => ['nullable', Rule::exists('gruppen','gruppe_id')],
            'art_id'         => ['required', Rule::exists('beratungsarten','Art_id')],
            'thema_id'       => ['nullable', Rule::exists('beratungsthemen','Thema_id')],
            'mitarbeiter_id' => ['nullable', Rule::exists('mitarbeiter','Mitarbeiter_id')],
            'datum'          => ['required', 'date'],
            'dauer_h'        => ['nullable', 'numeric', 'min:0', 'max:24'],
            'thema'          => ['nullable', 'string', 'max:255'],
            'inhalt'         => ['nullable', 'string'],
            'TNUnterlagen'   => ['nullable', 'boolean'],
            'teilnehmer_ids'   => ['nullable', 'array'],
            'teilnehmer_ids.*' => [Rule::exists('teilnehmer','Teilnehmer_id')],
        ]);

        $gb = GruppenBeratung::create([
            'gruppe_id'     => $data['gruppe_id']      ?? null,
            'art_id'        => $data['art_id'],
            'thema_id'      => $data['thema_id']       ?? null,
            'mitarbeiter_id'=> $data['mitarbeiter_id'] ?? null,
            'datum'         => $data['datum'],
            'dauer_h'       => $data['dauer_h']        ?? null,
            'thema'         => $data['thema']          ?? null,
            'inhalt'        => $data['inhalt']         ?? null,
            'TNUnterlagen'  => $request->boolean('TNUnterlagen') ? 1 : 0,
        ]);

        if (!empty($data['teilnehmer_ids'])) {
            $gb->teilnehmer()->attach($data['teilnehmer_ids']);
        }

        return back()->with('success','Gruppenberatung gespeichert.');
    }

    /**
     * Detailansicht (optional).
     */
    public function show(GruppenBeratung $session)
    {
        $session->load(['mitarbeiter','gruppe','art','thema','teilnehmer']);
        return view('gruppen_beratungen.show', compact('session'));
    }

    /**
     * Bearbeitungsformular (optional).
     */
    public function edit(GruppenBeratung $session)
    {
        $session->load(['mitarbeiter','gruppe','art','thema','teilnehmer']);

        $gruppen     = Gruppe::orderBy('name')->get();
        $mitarbeiter = Mitarbeiter::orderBy('Nachname')->orderBy('Vorname')->get();
        $arten       = Beratungsart::orderBy('Bezeichnung')->get();
        $themen      = Beratungsthema::orderBy('Bezeichnung')->get();
        $teilnehmer  = Teilnehmer::orderBy('Nachname')->orderBy('Vorname')->get();

        return view('gruppen_beratungen.edit', compact(
            'session','gruppen','mitarbeiter','arten','themen','teilnehmer'
        ));
    }

    /**
     * Aktualisieren (optional, wenn Route vorhanden ist).
     */
    public function update(Request $request, GruppenBeratung $session)
    {
        $data = $request->validate([
            'gruppe_id'      => ['nullable', Rule::exists('gruppen','gruppe_id')],
            'art_id'         => ['required', Rule::exists('beratungsarten','Art_id')],
            'thema_id'       => ['nullable', Rule::exists('beratungsthemen','Thema_id')],
            'mitarbeiter_id' => ['nullable', Rule::exists('mitarbeiter','Mitarbeiter_id')],
            'datum'          => ['required', 'date'],
            'dauer_h'        => ['nullable', 'numeric', 'min:0', 'max:24'],
            'thema'          => ['nullable', 'string', 'max:255'],
            'inhalt'         => ['nullable', 'string'],
            'TNUnterlagen'   => ['nullable', 'boolean'],
            'teilnehmer_ids'   => ['nullable', 'array'],
            'teilnehmer_ids.*' => [Rule::exists('teilnehmer','Teilnehmer_id')],
        ]);

        $session->update([
            'gruppe_id'     => $data['gruppe_id']      ?? null,
            'art_id'        => $data['art_id'],
            'thema_id'      => $data['thema_id']       ?? null,
            'mitarbeiter_id'=> $data['mitarbeiter_id'] ?? null,
            'datum'         => $data['datum'],
            'dauer_h'       => $data['dauer_h']        ?? null,
            'thema'         => $data['thema']          ?? null,
            'inhalt'        => $data['inhalt']         ?? null,
            'TNUnterlagen'  => $request->boolean('TNUnterlagen') ? 1 : 0,
        ]);

        // Teilnehmer optional syncen (nur wenn Feld im Formular vorhanden war)
        if ($request->has('teilnehmer_ids')) {
            $ids = array_filter((array)($data['teilnehmer_ids'] ?? []), 'is_numeric');
            $session->teilnehmer()->sync($ids);
        }

        return back()->with('success','Gruppenberatung aktualisiert.');
    }

    /**
     * Löschen (optional).
     */
    public function destroy(GruppenBeratung $session)
    {
        $session->teilnehmer()->detach();
        $session->delete();
        return redirect()->route('gruppen_beratungen.index')->with('success', 'Eintrag gelöscht.');
    }

    /**
     * Teilnehmer anhängen (deine bestehende Helfer-Route).
     */
    public function attachTeilnehmer(GruppenBeratung $session, Request $request)
    {
        $ids = array_filter((array)$request->get('teilnehmer_ids', []), 'is_numeric');
        if (!empty($ids)) {
            $session->teilnehmer()->syncWithoutDetaching($ids);
        }
        return back()->with('success','Teilnehmer hinzugefügt.');
    }

    /**
     * Teilnehmer lösen (deine bestehende Helfer-Route).
     */
    public function detachTeilnehmer(GruppenBeratung $session, Teilnehmer $teilnehmer)
    {
        $session->teilnehmer()->detach($teilnehmer->Teilnehmer_id);
        return back()->with('success','Teilnehmer entfernt.');
    }
}
