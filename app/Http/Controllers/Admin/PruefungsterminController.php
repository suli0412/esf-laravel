<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pruefungstermin;
use App\Models\Niveau;
use App\Models\Teilnehmer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class PruefungsterminController extends Controller
{
    public function index()
    {
        $termine = Pruefungstermin::with('niveau')
            ->orderBy('datum','desc')
            ->paginate(20);

        return view('admin.pruefungstermine.index', compact('termine'));
    }

    public function create()
    {
        $termin  = new Pruefungstermin(['datum' => now()->toDateString()]);
        $niveaus = Niveau::orderBy('sort_order')->orderBy('code')->get();
        return view('admin.pruefungstermine.form', compact('termin','niveaus'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'bezeichnung' => ['nullable','string','max:100'],
            'niveau_id'   => ['required','integer','exists:niveau,niveau_id'],
            'datum'       => ['required','date'],
            'institut'    => ['nullable','string','max:100'],
        ]);

        $termin = Pruefungstermin::create($data);

        // ✨ Aktivitäts-Log
        Log::channel('activity')->info('pruefungstermin.created', [
            'termin_id'   => $termin->getKey(),
            'niveau_id'   => $termin->niveau_id,
            'bezeichnung' => $termin->bezeichnung,
            'datum'       => $termin->datum?->toDateString(),
            'institut'    => $termin->institut,
            'by_user'     => auth()->id(),
        ]);

        return redirect()
            ->route('admin.pruefungstermine.index')
            ->with('success','Prüfungstermin angelegt.');
    }

    public function show(Pruefungstermin $pruefungstermin, Request $r)
    {
        $pruefungstermin->load('niveau');
        $q = trim($r->get('q',''));

        $treffer = collect();
        if ($q !== '') {
            $treffer = Teilnehmer::query()
                ->where('Nachname','like',"%{$q}%")
                ->orWhere('Vorname','like',"%{$q}%")
                ->orWhere('Email','like',"%{$q}%")
                ->limit(10)
                ->get();
        }

        $buchungen = $pruefungstermin->teilnehmer()
            ->withPivot('bestanden','selbstzahler')
            ->get();

        return view('admin.pruefungstermine.show', compact('pruefungstermin','treffer','buchungen','q'));
    }

    public function edit(Pruefungstermin $pruefungstermin)
    {
        $niveaus = Niveau::orderBy('sort_order')->orderBy('code')->get();
        return view('admin.pruefungstermine.form', [
            'termin'  => $pruefungstermin,
            'niveaus' => $niveaus
        ]);
    }

    public function update(Request $r, Pruefungstermin $pruefungstermin)
    {
        $data = $r->validate([
            'bezeichnung' => ['nullable','string','max:100'],
            'niveau_id'   => ['required','integer','exists:niveau,niveau_id'],
            'datum'       => ['required','date'],
            'institut'    => ['nullable','string','max:100'],
        ]);

        // Snapshot vor dem Update für ein sinnvolles Änderungs-Log
        $before = [
            'bezeichnung' => $pruefungstermin->bezeichnung,
            'niveau_id'   => $pruefungstermin->niveau_id,
            'datum'       => optional($pruefungstermin->datum)->toDateString(),
            'institut'    => $pruefungstermin->institut,
        ];

        $pruefungstermin->update($data);

        $after = [
            'bezeichnung' => $pruefungstermin->bezeichnung,
            'niveau_id'   => $pruefungstermin->niveau_id,
            'datum'       => optional($pruefungstermin->datum)->toDateString(),
            'institut'    => $pruefungstermin->institut,
        ];

        $changed = [];
        foreach ($after as $k => $v) {
            if (($before[$k] ?? null) !== $v) {
                $changed[] = $k;
            }
        }

        // ✨ Aktivitäts-Log
        Log::channel('activity')->info('pruefungstermin.updated', [
            'termin_id' => $pruefungstermin->getKey(),
            'changed'   => $changed,
            'by_user'   => auth()->id(),
        ]);

        return redirect()
            ->route('admin.pruefungstermine.index')
            ->with('success','Prüfungstermin aktualisiert.');
    }

    public function destroy(Pruefungstermin $pruefungstermin)
    {
        $payload = [
            'termin_id'   => $pruefungstermin->getKey(),
            'niveau_id'   => $pruefungstermin->niveau_id,
            'bezeichnung' => $pruefungstermin->bezeichnung,
            'datum'       => optional($pruefungstermin->datum)->toDateString(),
            'by_user'     => auth()->id(),
        ];

        $pruefungstermin->delete();

        // ✨ Aktivitäts-Log
        Log::channel('activity')->warning('pruefungstermin.deleted', $payload);

        return redirect()
            ->route('admin.pruefungstermine.index')
            ->with('success','Prüfungstermin gelöscht.');
    }

    public function attachTeilnehmer(Pruefungstermin $pruefungstermin, Request $r)
    {
        $data = $r->validate([
            'teilnehmer_id' => ['required','integer','exists:teilnehmer,Teilnehmer_id'],
            'selbstzahler'  => ['sometimes','boolean'],
        ]);

        $pruefungstermin->teilnehmer()->syncWithoutDetaching([
            $data['teilnehmer_id'] => ['selbstzahler' => (bool)$r->boolean('selbstzahler')],
        ]);

        // ✨ Aktivitäts-Log
        Log::channel('activity')->info('pruefungstermin.member.attached', [
            'termin_id'     => $pruefungstermin->getKey(),
            'teilnehmer_id' => (int)$data['teilnehmer_id'],
            'selbstzahler'  => (bool)$r->boolean('selbstzahler'),
            'by_user'       => auth()->id(),
        ]);

        return back()->with('success','Teilnehmer gebucht.');
    }

    public function detachTeilnehmer(Pruefungstermin $pruefungstermin, Teilnehmer $teilnehmer)
    {
        $pruefungstermin->teilnehmer()->detach($teilnehmer->Teilnehmer_id);

        // ✨ Aktivitäts-Log
        Log::channel('activity')->info('pruefungstermin.member.detached', [
            'termin_id'     => $pruefungstermin->getKey(),
            'teilnehmer_id' => $teilnehmer->Teilnehmer_id,
            'by_user'       => auth()->id(),
        ]);

        return back()->with('success','Buchung entfernt.');
    }
}
