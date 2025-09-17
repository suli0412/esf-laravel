<?php

namespace App\Http\Controllers;

use App\Models\Teilnehmer;
use App\Models\TeilnehmerAnwesenheit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnwesenheitController extends Controller
{
    public function index(Request $request)
    {
        $q    = (string) $request->get('q', '');
        $von  = $request->date('von');
        $bis  = $request->date('bis');

        $rows = TeilnehmerAnwesenheit::with('teilnehmer')
            ->when($q, function ($query) use ($q) {
                $query->whereHas('teilnehmer', function ($w) use ($q) {
                    $w->where('Nachname','like',"%{$q}%")
                      ->orWhere('Vorname','like',"%{$q}%")
                      ->orWhere('Email','like',"%{$q}%");
                });
            })
            ->when($von, fn($qr)=>$qr->where('datum','>=',$von))
            ->when($bis, fn($qr)=>$qr->where('datum','<=',$bis))
            ->orderByDesc('datum')
            ->orderBy('teilnehmer_id')
            ->paginate(25)
            ->withQueryString();

        return view('anwesenheit.index', [
            'rows' => $rows,
            'q'    => $q,
            'von'  => $von?->toDateString(),
            'bis'  => $bis?->toDateString(),
        ]);
    }

    public function create()
    {
        $teilnehmer = Teilnehmer::orderBy('Nachname')->orderBy('Vorname')->get();
        $stati = TeilnehmerAnwesenheit::STATI;

        return view('anwesenheit.create', compact('teilnehmer','stati'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'teilnehmer_id' => ['required','integer','exists:teilnehmer,Teilnehmer_id'],
            'datum'         => ['required','date',
                Rule::unique('teilnehmer_anwesenheit', 'datum')
                    ->where(fn($q)=>$q->where('teilnehmer_id', $request->teilnehmer_id))
            ],
            'status'        => ['required', Rule::in(TeilnehmerAnwesenheit::STATI)],
            'fehlminuten'   => ['nullable','integer','min:0'],
        ]);

        $data['fehlminuten'] = $data['fehlminuten'] ?? 0;

        TeilnehmerAnwesenheit::create($data);

        return redirect()->route('anwesenheit.index')
            ->with('success','Eintrag gespeichert.');
    }

    public function edit(TeilnehmerAnwesenheit $anwesenheit)
    {
        $teilnehmer = Teilnehmer::orderBy('Nachname')->orderBy('Vorname')->get();
        $stati = TeilnehmerAnwesenheit::STATI;

        return view('anwesenheit.edit', compact('anwesenheit','teilnehmer','stati'));
    }

    public function update(Request $request, TeilnehmerAnwesenheit $anwesenheit)
    {
        $data = $request->validate([
            'teilnehmer_id' => ['required','integer','exists:teilnehmer,Teilnehmer_id'],
            'datum'         => ['required','date',
                Rule::unique('teilnehmer_anwesenheit','datum')
                    ->ignore($anwesenheit->anwesenheit_id,'anwesenheit_id')
                    ->where(fn($q)=>$q->where('teilnehmer_id', $request->teilnehmer_id))
            ],
            'status'        => ['required', Rule::in(TeilnehmerAnwesenheit::STATI)],
            'fehlminuten'   => ['nullable','integer','min:0'],
        ]);

        $data['fehlminuten'] = $data['fehlminuten'] ?? 0;

        $anwesenheit->update($data);

        return redirect()->route('anwesenheit.index')
            ->with('success','Eintrag aktualisiert.');
    }

    public function destroy(TeilnehmerAnwesenheit $anwesenheit)
    {
        $anwesenheit->delete();

        return redirect()->route('anwesenheit.index')
            ->with('success','Eintrag gelöscht.');
    }
}
