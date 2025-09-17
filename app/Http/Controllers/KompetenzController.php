<?php

namespace App\Http\Controllers;

use App\Models\Kompetenz;
use Illuminate\Http\Request;

class KompetenzController extends Controller
{
    public function index()
    {
        $rows = Kompetenz::orderBy('code')->get();
        return view('stammdaten.kompetenzen.index', compact('rows'));
    }

    public function create()
    {
        return view('stammdaten.kompetenzen.edit', ['item' => new Kompetenz()]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'code'        => 'required|string|max:20|unique:kompetenzen,code',
            'bezeichnung' => 'required|string|max:100',
        ]);

        Kompetenz::create($data);
        return redirect()->route('kompetenzen.index')->with('success', 'Kompetenz angelegt.');
    }

    public function edit(Kompetenz $kompetenz)
    {
        return view('stammdaten.kompetenzen.edit', ['item' => $kompetenz]);
    }

    public function update(Request $r, Kompetenz $kompetenz)
    {
        $data = $r->validate([
            'code'        => 'required|string|max:20|unique:kompetenzen,code,'.$kompetenz->kompetenz_id.',kompetenz_id',
            'bezeichnung' => 'required|string|max:100',
        ]);

        $kompetenz->update($data);
        return redirect()->route('kompetenzen.index')->with('success', 'Kompetenz aktualisiert.');
    }

    public function destroy(Kompetenz $kompetenz)
    {
        $kompetenz->delete();
        return redirect()->route('kompetenzen.index')->with('success', 'Kompetenz gelöscht.');
    }
}
