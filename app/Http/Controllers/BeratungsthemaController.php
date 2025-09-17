<?php

namespace App\Http\Controllers;

use App\Models\Beratungsthema;
use Illuminate\Http\Request;

class BeratungsthemaController extends Controller
{
    public function index()
    {
        $items = Beratungsthema::orderBy('Bezeichnung')->paginate(20);
        return view('beratungsthemen.index', compact('items'));
    }

    public function create()
    {
        return view('beratungsthemen.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Bezeichnung' => 'required|string|max:120|unique:beratungsthemen,Bezeichnung',
            'Beschreibung' => 'nullable|string',
        ]);

        Beratungsthema::create($data);
        return redirect()->route('beratungsthemen.index')->with('success','Thema angelegt.');
    }

    public function edit(Beratungsthema $beratungsthemen)
    {
        return view('beratungsthemen.edit', ['item' => $beratungsthemen]);
    }

    public function update(Request $request, Beratungsthema $beratungsthemen)
    {
        $data = $request->validate([
            'Bezeichnung' => 'required|string|max:120|unique:beratungsthemen,Bezeichnung,'.$beratungsthemen->getKey().',Thema_id',
            'Beschreibung' => 'nullable|string',
        ]);

        $beratungsthemen->update($data);
        return redirect()->route('beratungsthemen.index')->with('success','Thema aktualisiert.');
    }

    public function destroy(Beratungsthema $beratungsthemen)
    {
        $beratungsthemen->delete();
        return redirect()->route('beratungsthemen.index')->with('success','Thema gelöscht.');
    }
}
