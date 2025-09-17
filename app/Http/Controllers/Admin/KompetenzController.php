<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kompetenz;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KompetenzController extends Controller
{
    public function index()
    {
        $records = Kompetenz::orderBy('code')->paginate(20);
        return view('admin.kompetenzen.index', compact('records'));
    }

    public function create()
    {
        $kompetenz = new Kompetenz();
        return view('admin.kompetenzen.form', compact('kompetenz'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'code'        => ['required','string','max:20','unique:kompetenzen,code'],
            'bezeichnung' => ['required','string','max:100'],
        ]);
        $kompetenz = Kompetenz::create($data);
        return redirect()->route('admin.kompetenzen.index')->with('success','Kompetenz angelegt.');
    }

    public function edit(Kompetenz $kompetenz)
    {
        return view('admin.kompetenzen.form', compact('kompetenz'));
    }

    public function update(Request $r, Kompetenz $kompetenz)
    {
        $data = $r->validate([
            'code'        => ['required','string','max:20', Rule::unique('kompetenzen','code')->ignore($kompetenz->kompetenz_id,'kompetenz_id')],
            'bezeichnung' => ['required','string','max:100'],
        ]);
        $kompetenz->update($data);
        return redirect()->route('admin.kompetenzen.index')->with('success','Kompetenz aktualisiert.');
    }

    public function destroy(Kompetenz $kompetenz)
    {
        $kompetenz->delete();
        return back()->with('success','Kompetenz gelöscht.');
    }
}
