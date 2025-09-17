<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Niveau;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NiveauController extends Controller
{
    public function index()
    {
        $records = Niveau::orderBy('sort_order')->orderBy('code')->paginate(20);
        return view('admin.niveaus.index', compact('records'));
    }

    public function create()
    {
        $niveau = new Niveau(['sort_order' => 100]);
        return view('admin.niveaus.form', compact('niveau'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'code'       => ['required','string','max:20','unique:niveau,code'],
            'label'      => ['nullable','string','max:50'],
            'sort_order' => ['required','integer','between:0,10000'],
        ]);
        Niveau::create($data);
        return redirect()->route('admin.niveaus.index')->with('success','Niveau angelegt.');
    }

    public function edit(Niveau $niveau)
    {
        return view('admin.niveaus.form', compact('niveau'));
    }

    public function update(Request $r, Niveau $niveau)
    {
        $data = $r->validate([
            'code'       => ['required','string','max:20', Rule::unique('niveau','code')->ignore($niveau->niveau_id,'niveau_id')],
            'label'      => ['nullable','string','max:50'],
            'sort_order' => ['required','integer','between:0,10000'],
        ]);
        $niveau->update($data);
        return redirect()->route('admin.niveaus.index')->with('success','Niveau aktualisiert.');
    }

    public function destroy(Niveau $niveau)
    {
        $niveau->delete();
        return back()->with('success','Niveau gelöscht.');
    }
}
