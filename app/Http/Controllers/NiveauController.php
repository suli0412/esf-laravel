<?php

namespace App\Http\Controllers;

use App\Models\Niveau;
use Illuminate\Http\Request;

class NiveauController extends Controller
{
    public function index()
    {
        $rows = Niveau::orderBy('sort_order')->orderBy('code')->get();
        return view('stammdaten.niveaus.index', compact('rows'));
    }

    public function create()
    {
        return view('stammdaten.niveaus.edit', ['item' => new Niveau()]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'code'       => 'required|string|max:20|unique:niveau,code',
            'label'      => 'required|string|max:50',
            'sort_order' => 'required|integer|min:0',
        ]);

        Niveau::create($data);
        return redirect()->route('niveaus.index')->with('success', 'Niveau angelegt.');
    }

    public function edit(Niveau $niveau)
    {
        return view('stammdaten.niveaus.edit', ['item' => $niveau]);
    }

    public function update(Request $r, Niveau $niveau)
    {
        $data = $r->validate([
            'code'       => 'required|string|max:20|unique:niveau,code,'.$niveau->niveau_id.',niveau_id',
            'label'      => 'required|string|max:50',
            'sort_order' => 'required|integer|min:0',
        ]);

        $niveau->update($data);
        return redirect()->route('niveaus.index')->with('success', 'Niveau aktualisiert.');
    }

    public function destroy(Niveau $niveau)
    {
        $niveau->delete();
        return redirect()->route('niveaus.index')->with('success', 'Niveau gelöscht.');
    }
}
