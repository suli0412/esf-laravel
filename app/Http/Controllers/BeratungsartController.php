<?php

namespace App\Http\Controllers;

use App\Models\Beratungsart;
use Illuminate\Http\Request;

class BeratungsartController extends Controller
{
    public function index()
    {
        $items = Beratungsart::orderBy('Code')->paginate(20);
        return view('beratungsarten.index', compact('items'));
    }

    public function create()
    {
        return view('beratungsarten.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => 'required|string|size:3|unique:beratungsarten,Code',
            'Bezeichnung' => 'required|string|max:50',
        ]);

        Beratungsart::create($data);
        return redirect()->route('beratungsarten.index')->with('success','Art angelegt.');
    }

    public function edit(Beratungsart $beratungsarten)
    {
        // $beratungsarten — имя параметра формируется по ресурсу
        return view('beratungsarten.edit', ['item' => $beratungsarten]);
    }

    public function update(Request $request, Beratungsart $beratungsarten)
    {
        $data = $request->validate([
            'Code' => 'required|string|size:3|unique:beratungsarten,Code,'.$beratungsarten->getKey().',Art_id',
            'Bezeichnung' => 'required|string|max:50',
        ]);

        $beratungsarten->update($data);
        return redirect()->route('beratungsarten.index')->with('success','Art aktualisiert.');
    }

    public function destroy(Beratungsart $beratungsarten)
    {
        $beratungsarten->delete();
        return redirect()->route('beratungsarten.index')->with('success','Art gelöscht.');
    }
}
