<?php

namespace App\Http\Controllers;

use App\Models\Kompetenz;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KompetenzController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // optional: $this->middleware('permission:settings.manage');
    }

    /** Liste */
    public function index()
    {
        $rows = Kompetenz::orderBy('code')->get();
        return view('stammdaten.kompetenzen.index', compact('rows'));
    }

    /** Formular: neu */
    public function create()
    {
        return view('stammdaten.kompetenzen.edit', ['item' => new Kompetenz()]);
    }

    /** Speichern: neu */
    public function store(Request $r)
    {
        $data = $r->validate([
            'code'        => ['required','string','max:20', Rule::unique('kompetenzen','code')],
            'bezeichnung' => ['required','string','max:100'],
        ]);

        Kompetenz::create($data);

        return redirect()
            ->route('kompetenzen.index')
            ->with('success', 'Kompetenz angelegt.');
    }

    /** Formular: bearbeiten */
    public function edit(Kompetenz $kompetenz)
    {
        return view('stammdaten.kompetenzen.edit', ['item' => $kompetenz]);
    }

    /** Aktualisieren */
    public function update(Request $r, Kompetenz $kompetenz)
    {
        $data = $r->validate([
            'code'        => [
                'required','string','max:20',
                Rule::unique('kompetenzen','code')->ignore($kompetenz->kompetenz_id, 'kompetenz_id'),
            ],
            'bezeichnung' => ['required','string','max:100'],
        ]);

        $kompetenz->update($data);

        return redirect()
            ->route('kompetenzen.index')
            ->with('success', 'Kompetenz aktualisiert.');
    }

    /** Löschen */
    public function destroy(Kompetenz $kompetenz)
    {
        $kompetenz->delete();

        return redirect()
            ->route('kompetenzen.index')
            ->with('success', 'Kompetenz gelöscht.');
    }
}
