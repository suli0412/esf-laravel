<?php

namespace App\Http\Controllers;

use App\Models\Teilnehmer;
use Illuminate\Http\Request;

class TeilnehmerController extends Controller
{
    /**
     * Список участников + поиск и пагинация.
     */
    public function index(Request $request)
    {
        $q = $request->string('q')->toString();

        $teilnehmer = Teilnehmer::when($q, function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('Nachname', 'like', "%{$q}%")
                       ->orWhere('Vorname', 'like', "%{$q}%")
                       ->orWhere('Email', 'like', "%{$q}%");
                });
            })
            ->orderBy('Nachname')
            ->orderBy('Vorname')
            ->paginate(15)
            ->withQueryString();

        return view('teilnehmer.index', compact('teilnehmer', 'q'));
    }

    /**
     * Форма создания.
     */
    public function create()
    {
        $teilnehmer = new Teilnehmer();
        return view('teilnehmer.create', compact('teilnehmer'));
    }

    /**
     * Сохранение нового участника.
     */
    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $data = $this->normalizeCheckboxes($request, $data);

        $teilnehmer = Teilnehmer::create($data);

        return redirect()
            ->route('teilnehmer.show', $teilnehmer)
            ->with('success', 'Teilnehmer erfolgreich angelegt.');
    }

    /**
     * Просмотр.
     */
    public function show(Teilnehmer $teilnehmer)
    {
        return view('teilnehmer.show', compact('teilnehmer'));
    }

    /**
     * Форма редактирования.
     */
    public function edit(Teilnehmer $teilnehmer)
    {
        return view('teilnehmer.edit', compact('teilnehmer'));
    }

    /**
     * Обновление.
     */
    public function update(Request $request, Teilnehmer $teilnehmer)
    {
        $data = $this->validateData($request);
        $data = $this->normalizeCheckboxes($request, $data);

        $teilnehmer->update($data);

        return redirect()
            ->route('teilnehmer.show', $teilnehmer)
            ->with('success', 'Teilnehmer erfolgreich aktualisiert.');
    }

    /**
     * Удаление.
     */
    public function destroy(Teilnehmer $teilnehmer)
    {
        $teilnehmer->delete();

        return redirect()
            ->route('teilnehmer.index')
            ->with('success', 'Teilnehmer gelöscht.');
    }

    /**
     * Общая валидация для store/update.
     */
    private function validateData(Request $request): array
    {
        $jaNeinKA = 'in:Ja,Nein,Keine Angabe';

        return $request->validate([
            'Nachname'  => 'required|string|max:100',
            'Vorname'   => 'required|string|max:100',
            'Geschlecht'=> 'nullable|in:Mann,Frau,Nicht binär',
            'SVN'       => 'nullable|string|max:12',
            'Strasse'   => 'nullable|string|max:150',
            'Hausnummer'=> 'nullable|string|max:10',
            'PLZ'       => 'nullable|string|max:10',
            'Wohnort'   => 'nullable|string|max:150',
            'Land'      => 'nullable|string|max:50',
            'Email'     => 'nullable|email|max:255',
            'Telefonnummer' => 'nullable|string|max:25',
            'Geburtsdatum'  => 'nullable|date',
            'Geburtsland'   => 'nullable|string|max:100',
            'Staatszugehörigkeit' => 'nullable|string|max:100',
            'Staatszugehörigkeit_Kategorie' => 'nullable|string|max:100',
            'Aufenthaltsstatus' => 'nullable|string|max:100',

            'Minderheit'        => "nullable|$jaNeinKA",
            'Behinderung'       => "nullable|$jaNeinKA",
            'Obdachlos'         => "nullable|$jaNeinKA",
            'LändlicheGebiete'  => "nullable|$jaNeinKA",
            'ElternImAuslandGeboren' => "nullable|$jaNeinKA",
            'Armutsbetroffen'   => "nullable|$jaNeinKA",
            'Armutsgefährdet'   => "nullable|$jaNeinKA",

            'Bildungshintergrund' => 'nullable|in:ISCED0,ISCED1,ISCED2,ISCED3,ISCED4,ISCED5-8',

            // чекбоксы как boolean
            'IDEA_Stammdatenblatt' => 'nullable|boolean',
            'IDEA_Dokumente'       => 'nullable|boolean',

            'PAZ' => 'nullable|in:Arbeitsaufnahme,Lehrstelle,ePSA,Sprachprüfung A2/B1,weitere Deutschkurse,Basisbildung,Sonstige berufsspezifische Weiterbildung,Sonstiges',

            'Berufserfahrung_als'     => 'nullable|string|max:100',
            'Bereich_berufserfahrung' => 'nullable|string|max:100',
            'Land_berufserfahrung'    => 'nullable|string|max:30',
            'Firma_berufserfahrung'   => 'nullable|string|max:150',
            'Zeit_berufserfahrung'    => 'nullable|string|max:100',
            'Stundenumfang_berufserfahrung' => 'nullable|numeric|min:0|max:999.99',
            'Zertifikate'             => 'nullable|string|max:300',
            'Berufswunsch'            => 'nullable|string|max:100',
            'Berufswunsch_branche'    => 'nullable|string|max:100',
            'Berufswunsch_branche2'   => 'nullable|string|max:100',

            'Clearing_gruppe'     => 'nullable|boolean',
            'Unterrichtseinheiten'=> 'nullable|integer|min:0',
            'Anmerkung'           => 'nullable|string',
        ]);
    }

    /**
     * Приведение чекбоксов к 0/1 (если не пришли — сделать 0).
     */
    private function normalizeCheckboxes(Request $request, array $data): array
    {
        foreach (['IDEA_Stammdatenblatt','IDEA_Dokumente','Clearing_gruppe'] as $cb) {
            $data[$cb] = (int) $request->boolean($cb); // 0 или 1
        }
        return $data;
    }
}
