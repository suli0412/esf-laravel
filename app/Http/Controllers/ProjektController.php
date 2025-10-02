<?php

namespace App\Http\Controllers;

use App\Models\Projekt;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\Mitarbeiter;

class ProjektController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $rows = Projekt::when($q !== '', function ($qr) use ($q) {
                    $qr->where(function ($w) use ($q) {
                        $w->where('code','like',"%{$q}%")
                          ->orWhere('bezeichnung','like',"%{$q}%");
                    });
                })
                ->orderByDesc('aktiv')
                ->orderBy('bezeichnung')
                ->paginate(20)
                ->withQueryString();

        return view('projekte.index', compact('rows','q'));
    }

    public function create()
    {
    $mitarbeiter = Mitarbeiter::orderBy('Nachname')->orderBy('Vorname')->get();
    return view('projekte.create', compact('mitarbeiter'));
    }
    public function store(Request $request)
    {
        $table = (new Projekt)->getTable(); // -> 'projekte'

        $data = $request->validate([
            'code'        => "required|string|max:30|unique:{$table},code",
        'bezeichnung' => 'required|string|max:150',
        'beschreibung'=> 'nullable|string',
        'inhalte'     => 'nullable|string',
        'verantwortlicher_id' => 'nullable|integer|exists:mitarbeiter,Mitarbeiter_id',
        'start'       => 'nullable|date',
        'ende'        => 'nullable|date|after_or_equal:start',
        'aktiv'       => 'nullable|boolean'
        ]);

        // Checkbox robust
        $data['aktiv'] = $request->boolean('aktiv');

        // Default: Ende = Start + 1 Jahr (nur wenn Start vorhanden & Ende leer)
        if (!empty($data['start']) && empty($data['ende'])) {
            $data['ende'] = Carbon::parse($data['start'])->addYear()->toDateString();
        }

        $projekt = Projekt::create($data);

        return redirect()
            ->route('projekte.show', $projekt)
            ->with('success','Projekt angelegt.');
    }

    public function show(Projekt $projekt)
    {
        return view('projekte.show', compact('projekt'));
    }

    public function edit(Projekt $projekt)
    {
    $mitarbeiter = Mitarbeiter::orderBy('Nachname')->orderBy('Vorname')->get();
    return view('projekte.edit', compact('projekt','mitarbeiter'));
    }

    public function update(Request $request, Projekt $projekt)
    {
        $table = (new Projekt)->getTable(); // -> 'projekte'

        $data = $request->validate([
        'code'        => ['required','string','max:30', Rule::unique($table,'code')->ignore($projekt->projekt_id,'projekt_id')],
        'bezeichnung' => 'required|string|max:150',
        'beschreibung'=> 'nullable|string',
        'inhalte'     => 'nullable|string',
        'verantwortlicher_id' => 'nullable|integer|exists:mitarbeiter,Mitarbeiter_id',
        'start'       => 'nullable|date',
        'ende'        => 'nullable|date|after_or_equal:start',
        'aktiv'       => 'nullable|boolean'
        ]);

        $data['aktiv'] = $request->boolean('aktiv');

        if (!empty($data['start']) && empty($data['ende'])) {
            $data['ende'] = Carbon::parse($data['start'])->addYear()->toDateString();
        }

        $projekt->update($data);

        return redirect()
            ->route('projekte.show', $projekt)
            ->with('success','Projekt aktualisiert.');
    }

    public function destroy(Projekt $projekt)
    {
        $projekt->delete();

        return redirect()
            ->route('projekte.index')
            ->with('success','Projekt gelöscht.');
    }

    /**
     * Optional: Aktiv/Inaktiv umschalten (Route: PATCH /projekte/{projekt}/toggle)
     */
    public function toggle(\App\Models\Projekt $projekt)
    {
        $projekt->aktiv = ! (bool)$projekt->aktiv;
        $projekt->save();

        return back()->with('status', 'Projektstatus aktualisiert.');
    }

}
