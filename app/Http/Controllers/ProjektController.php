<?php

namespace App\Http\Controllers;

use App\Models\Projekt;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjektController extends Controller
{
    public function index(Request $request)
    {
        $q = (string) $request->get('q', '');
        $rows = Projekt::when($q, function ($qr) use ($q) {
                    $qr->where('code','like',"%{$q}%")
                       ->orWhere('bezeichnung','like',"%{$q}%");
                })
                ->orderByDesc('aktiv')
                ->orderBy('bezeichnung')
                ->paginate(20)
                ->withQueryString();

        return view('projekte.index', compact('rows','q'));
    }

    public function create()
    {
        return view('projekte.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'        => 'required|string|max:30|unique:Projekte,code',
            'bezeichnung' => 'required|string|max:150',
            'start'       => 'nullable|date',
            'ende'        => 'nullable|date|after_or_equal:start',
            'aktiv'       => 'nullable|boolean',
        ]);
        $data['aktiv'] = (bool) ($data['aktiv'] ?? 0);

        $p = Projekt::create($data);

        return redirect()->route('projekte.show', $p)->with('success','Projekt angelegt.');
    }

    public function show(Projekt $projekte) // имя параметра = сегмент ресурса
    {
        $projekt = $projekte;               // для удобства в шаблоне
        return view('projekte.show', compact('projekt'));
    }

    public function edit(Projekt $projekte)
    {
        $projekt = $projekte;
        return view('projekte.edit', compact('projekt'));
    }

    public function update(Request $request, Projekt $projekte)
    {
        $projekt = $projekte;

        $data = $request->validate([
            'code'        => [
                'required','string','max:30',
                Rule::unique('Projekte','code')->ignore($projekt->projekt_id,'projekt_id'),
            ],
            'bezeichnung' => 'required|string|max:150',
            'start'       => 'nullable|date',
            'ende'        => 'nullable|date|after_or_equal:start',
            'aktiv'       => 'nullable|boolean',
        ]);
        $data['aktiv'] = (bool) ($data['aktiv'] ?? 0);

        $projekt->update($data);

        return redirect()->route('projekte.show', $projekt)->with('success','Projekt aktualisiert.');
    }

    public function destroy(Projekt $projekte)
    {
        $projekte->delete();
        return redirect()->route('projekte.index')->with('success','Projekt gelöscht.');
    }
}
