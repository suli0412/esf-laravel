<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teilnehmer;
use App\Models\Beratungsart;
use App\Models\Beratungsthema;
use App\Models\Dokument;
use App\Models\Gruppe;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use App\Services\KompetenzstandService;
use Illuminate\Support\Facades\DB;
use App\Models\Kompetenz;   //
use App\Models\Niveau;      //


class TeilnehmerController extends Controller
{
    /**
     * Liste der Teilnehmer + Suche + Pagination.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $teilnehmer = Teilnehmer::with('checkliste')
            ->withCount('dokumente')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(fn ($w) => $w->where('Nachname', 'like', "%{$q}%")
                    ->orWhere('Vorname', 'like', "%{$q}%")
                    ->orWhere('Email', 'like', "%{$q}%"));
            })
            ->orderBy('Nachname')
            ->orderBy('Vorname')
            ->paginate(15)
            ->withQueryString();

        return view('teilnehmer.index', compact('teilnehmer', 'q'));
    }

    /**
     * Formular für neuen Teilnehmer.
     */
    public function create()
    {
        $kompetenzen = \App\Models\Kompetenz::orderBy('code')->get();
        $niveaus     = \App\Models\Niveau::orderBy('sort_order')->get();
        $teilnehmer = new Teilnehmer();
        $gruppen = Gruppe::orderBy('name')->get();
        return view('teilnehmer.create', compact('teilnehmer', 'gruppen','kompetenzen','niveaus'));
    }

    /**
     * Speichern eines neuen Teilnehmers.
     */
    public function store(Request $request, KompetenzstandService $svc)
{
    // 1) Aliase für Groß/Kleinschreibung akzeptieren
    $request->merge([
        'Nachname' => $request->input('Nachname', $request->input('nachname')),
        'Vorname'  => $request->input('Vorname',  $request->input('vorname')),
    ]);

    // 2) Validierung exakt auf deine Feldnamen (Großschreibung)
    $data = $request->validate([
        'Nachname' => 'required|string|max:100',
        'Vorname'  => 'required|string|max:100',

        // Optionalfelder (nur wenn gesendet)
        'Geschlecht' => 'sometimes|nullable|string|max:30',
        'SVN'        => 'sometimes|nullable|string|max:12',
        'Strasse'    => 'sometimes|nullable|string|max:150',
        'Hausnummer' => 'sometimes|nullable|string|max:10',
        'PLZ'        => 'sometimes|nullable|string|max:10',
        'Wohnort'    => 'sometimes|nullable|string|max:150',
        'Land'       => 'sometimes|nullable|string|max:100',
        'Email'      => 'sometimes|nullable|email|max:150',
        'Telefonnummer' => 'sometimes|nullable|string|max:30',
        'Geburtsdatum'  => 'sometimes|nullable|string', // wir parsen gleich selbst

        'Geburtsland' => 'sometimes|nullable|string|max:100',
        'Staatszugehörigkeit' => 'sometimes|nullable|string|max:100',
        'Staatszugehörigkeit_Kategorie' => 'sometimes|nullable|string|max:100',
        'Aufenthaltsstatus' => 'sometimes|nullable|string|max:100',

        'Minderheit' => 'sometimes|nullable|string|max:100',
        'Behinderung' => 'sometimes|nullable|string|max:100',
        'Obdachlos' => 'sometimes|nullable|string|max:10',
        'LändlicheGebiete' => 'sometimes|nullable|string|max:10',
        'ElternImAuslandGeboren' => 'sometimes|nullable|string|max:10',
        'Armutsbetroffen' => 'sometimes|nullable|string|max:10',
        'Armutsgefährdet' => 'sometimes|nullable|string|max:10',
        'Bildungshintergrund' => 'sometimes|nullable|string|max:100',

        'IDEA_Stammdatenblatt' => 'sometimes|boolean',
        'IDEA_Dokumente'       => 'sometimes|boolean',
        'PAZ'                  => 'sometimes|nullable|string|max:100',

        'Berufserfahrung_als'      => 'sometimes|nullable|string|max:150',
        'Bereich_berufserfahrung'  => 'sometimes|nullable|string|max:150',
        'Land_berufserfahrung'     => 'sometimes|nullable|string|max:100',
        'Firma_berufserfahrung'    => 'sometimes|nullable|string|max:150',
        'Zeit_berufserfahrung'     => 'sometimes|nullable|string|max:100',
        'Stundenumfang_berufserfahrung' => 'sometimes|nullable|string|max:50',
        'Zertifikate' => 'sometimes|nullable|string',

        'Berufswunsch'           => 'sometimes|nullable|string|max:150',
        'Berufswunsch_branche'   => 'sometimes|nullable|string|max:150',
        'Berufswunsch_branche2'  => 'sometimes|nullable|string|max:150',

        'Clearing_gruppe'     => 'sometimes|boolean',
        'Unterrichtseinheiten'=> 'sometimes|nullable|string|max:50',
        'Anmerkung'           => 'sometimes|nullable|string',

        'gruppe_id' => 'sometimes|nullable|integer|exists:gruppen,gruppe_id',
    ]);

    // 3) Dropdowns normalisieren
    $nullify = [
        'Aufenthaltsstatus',
        'Bildungshintergrund',
        'PAZ',
        'Bereich_berufserfahrung',
        'Land_berufserfahrung',
        'Zeit_berufserfahrung',
        'Staatszugehörigkeit_Kategorie',
    ];
    foreach ($nullify as $f) {
        if (array_key_exists($f, $data)) {
            $v = trim((string)($data[$f] ?? ''));
            $data[$f] = ($v === '' || $v === '?' || $v === '— bitte wählen —') ? null : $v;
        }
    }

    // 4) Checkboxen konsistent setzen (auch wenn nicht gesendet -> false)
    foreach (['IDEA_Stammdatenblatt','IDEA_Dokumente','Clearing_gruppe'] as $f) {
        $data[$f] = $request->boolean($f);
    }

    // 5) Gruppe optional
    $data['gruppe_id'] = $request->filled('gruppe_id') ? (int)$request->input('gruppe_id') : null;

    // 6) Geburtsdatum robust parsen
    if (array_key_exists('Geburtsdatum', $data)) {
        $raw = trim((string)$data['Geburtsdatum']);
        if ($raw === '') {
            $data['Geburtsdatum'] = null;
        } else {
            try {
                if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $raw)) {
                    $dt = \Carbon\Carbon::createFromFormat('d.m.Y', $raw);
                } else {
                    $dt = \Carbon\Carbon::parse($raw);
                }
                $data['Geburtsdatum'] = $dt->format('Y-m-d');
            } catch (\Throwable $e) {
                $data['Geburtsdatum'] = null;
            }
        }
    }

    // 7) Anlegen
    $teilnehmer = Teilnehmer::create($data);

    // 8) Kompetenzstände aus Formular (optional) – FIX: $request statt $r
    //    Erwartete Struktur:
    //    kompetenz[Eintritt][<kompetenz_id>] = <niveau_id or ''>
    //    kompetenz[Austritt][<kompetenz_id>] = <niveau_id or ''>
    //    kompetenz[datum][Eintritt] / [Austritt]
    //    kompetenz[bemerkung][Eintritt] / [Austritt]
    $payload = $request->input('kompetenz', []);

    // Defaults ergänzen, damit der Service stabil Werte bekommt
    $payload += [
        'Eintritt'  => $payload['Eintritt']  ?? [],
        'Austritt'  => $payload['Austritt']  ?? [],
        'datum'     => $payload['datum']     ?? [],
        'bemerkung' => $payload['bemerkung'] ?? [],
    ];

    // kleine Bereinigung: leere Strings bei Niveau-Einträgen raus
    $cleanup = function($arr) {
        if (!is_array($arr)) return [];
        $out = [];
        foreach ($arr as $k => $v) {
            $v = ($v === '' || $v === null) ? null : (int)$v;
            if (!is_null($v)) $out[(int)$k] = $v; // kompetenz_id => niveau_id
        }
        return $out;
    };
    $payload['Eintritt'] = $cleanup($payload['Eintritt']);
    $payload['Austritt'] = $cleanup($payload['Austritt']);

    // nur speichern, wenn irgendwas übergeben wurde
    $hasAny =
        !empty($payload['Eintritt']) ||
        !empty($payload['Austritt']) ||
        !empty(array_filter($payload['datum'] ?? [])) ||
        !empty(array_filter($payload['bemerkung'] ?? []));

    if ($hasAny) {
        $svc->saveForTeilnehmer($teilnehmer->Teilnehmer_id, $payload);
    }

    return redirect()
        ->route('teilnehmer.show', $teilnehmer)
        ->with('success', 'Teilnehmer erfolgreich angelegt.');
}


    /**
     * Detailansicht eines Teilnehmers.
     */
    public function show(Teilnehmer $teilnehmer, Request $request)
    {
        $teilnehmer->load([
            'checkliste',
            'beratungen.mitarbeiter',
            'beratungen.art',
            'beratungen.thema',
            'dokumente' => fn($q) => $q->orderByDesc('hochgeladen_am'),
            'praktika'  => function ($q) {
                // versuche "von", sonst "beginn_datum", sonst "beginn", sonst fallback auf PK
                $table = 'teilnehmer_praktika';
                $vonCol = Schema::hasColumn($table, 'von') ? 'von'
                    : (Schema::hasColumn($table, 'beginn_datum') ? 'beginn_datum'
                    : (Schema::hasColumn($table, 'beginn') ? 'beginn' : null));
                if ($vonCol) {
                    $q->orderByDesc($vonCol);
                } else {
                    $q->orderByDesc($q->getModel()->getKeyName());
                }
            },
        ]);

        $arten  = Beratungsart::orderBy('Code')->get();
        $themen = Beratungsthema::orderBy('Bezeichnung')->get();

        $docs = Dokument::query()
            ->when(method_exists(Dokument::class, 'scopeAktiv'),
                fn($q) => $q->aktiv(),
                fn($q) => $q->where('is_active', 1))
            ->orderBy('name')
            ->get();

        // Anwesenheiten für gewählten Monat
        $monat = $request->query('monat', now()->format('Y-m'));
        [$jahr, $monatZahl] = explode('-', $monat);

        $anwesenheiten = $teilnehmer->anwesenheiten()
            ->whereYear('datum', $jahr)
            ->whereMonth('datum', $monatZahl)
            ->orderBy('datum')
            ->get();

        $fehlminutenSumme = $anwesenheiten->sum('fehlminuten');

        // --- Praktikumsstunden korrekt summieren ---
        $praktikaStundenSumme = 0.0;

        if (method_exists($teilnehmer, 'praktika')) {
            $praktikaStundenSumme = (float) $teilnehmer->praktika()->sum('stunden_ausmass');
        }


        return view('teilnehmer.show', compact(
            'teilnehmer',
            'arten',
            'themen',
            'docs',
            'anwesenheiten',
            'monat',
            'fehlminutenSumme',
            'praktikaStundenSumme'
        ));
    }

    /**
     * Formular zum Bearbeiten.
     */

   public function edit(Teilnehmer $teilnehmer)
    {
        // Dropdowns etc.
        $gruppen      = Gruppe::orderBy('name')->get();
        $kompetenzen  = Kompetenz::orderBy('code')->get();
        $niveaus      = Niveau::orderBy('sort_order')->get();

        // Kompetenzstand des TN laden und in zwei Maps aufteilen
        $st = DB::table('kompetenzstand')
            ->where('teilnehmer_id', $teilnehmer->Teilnehmer_id)
            ->get(['kompetenz_id','niveau_id','zeitpunkt']);

        $eintrittMap = $st->where('zeitpunkt','Eintritt')
                          ->pluck('niveau_id','kompetenz_id')
                          ->all();

        $austrittMap = $st->where('zeitpunkt','Austritt')
                          ->pluck('niveau_id','kompetenz_id')
                          ->all();

        return view('teilnehmer.edit', compact(
            'teilnehmer',
            'gruppen',
            'kompetenzen',
            'niveaus',
            'eintrittMap',
            'austrittMap'
        ));
    }

/**
 * Update eines Teilnehmers.
 */
public function update(Request $request, Teilnehmer $teilnehmer, KompetenzstandService $svc)
{
    // 1) Aliase für Groß/Kleinschreibung akzeptieren
    $request->merge([
        'Nachname' => $request->input('Nachname', $request->input('nachname')),
        'Vorname'  => $request->input('Vorname',  $request->input('vorname')),
    ]);

    // 2) Validierung exakt auf deine Feldnamen (Großschreibung)
    $data = $request->validate([
        'Nachname' => 'required|string|max:100',
        'Vorname'  => 'required|string|max:100',

        // Optionalfelder (nur wenn gesendet)
        'Geschlecht' => 'sometimes|nullable|string|max:30',
        'SVN'        => 'sometimes|nullable|string|max:12',
        'Strasse'    => 'sometimes|nullable|string|max:150',
        'Hausnummer' => 'sometimes|nullable|string|max:10',
        'PLZ'        => 'sometimes|nullable|string|max:10',
        'Wohnort'    => 'sometimes|nullable|string|max:150',
        'Land'       => 'sometimes|nullable|string|max:100',
        'Email'      => 'sometimes|nullable|email|max:150',
        'Telefonnummer' => 'sometimes|nullable|string|max:30',
        'Geburtsdatum'  => 'sometimes|nullable|string',

        'Geburtsland' => 'sometimes|nullable|string|max:100',
        'Staatszugehörigkeit' => 'sometimes|nullable|string|max:100',
        'Staatszugehörigkeit_Kategorie' => 'sometimes|nullable|string|max:100',
        'Aufenthaltsstatus' => 'sometimes|nullable|string|max:100',

        'Minderheit' => 'sometimes|nullable|string|max:100',
        'Behinderung' => 'sometimes|nullable|string|max:100',
        'Obdachlos' => 'sometimes|nullable|string|max:10',
        'LändlicheGebiete' => 'sometimes|nullable|string|max:10',
        'ElternImAuslandGeboren' => 'sometimes|nullable|string|max:10',
        'Armutsbetroffen' => 'sometimes|nullable|string|max:10',
        'Armutsgefährdet' => 'sometimes|nullable|string|max:10',
        'Bildungshintergrund' => 'sometimes|nullable|string|max:100',

        'IDEA_Stammdatenblatt' => 'sometimes|boolean',
        'IDEA_Dokumente'       => 'sometimes|boolean',
        'PAZ'                  => 'sometimes|nullable|string|max:100',

        'Berufserfahrung_als'      => 'sometimes|nullable|string|max:150',
        'Bereich_berufserfahrung'  => 'sometimes|nullable|string|max:150',
        'Land_berufserfahrung'     => 'sometimes|nullable|string|max:100',
        'Firma_berufserfahrung'    => 'sometimes|nullable|string|max:150',
        'Zeit_berufserfahrung'     => 'sometimes|nullable|string|max:100',
        'Stundenumfang_berufserfahrung' => 'sometimes|nullable|string|max:50',
        'Zertifikate' => 'sometimes|nullable|string',

        'Berufswunsch'           => 'sometimes|nullable|string|max:150',
        'Berufswunsch_branche'   => 'sometimes|nullable|string|max:150',
        'Berufswunsch_branche2'  => 'sometimes|nullable|string|max:150',

        'Clearing_gruppe'     => 'sometimes|boolean',
        'Unterrichtseinheiten'=> 'sometimes|nullable|string|max:50',
        'Anmerkung'           => 'sometimes|nullable|string',

        'gruppe_id' => 'sometimes|nullable|integer|exists:gruppen,gruppe_id',
    ]);

    // 3) Dropdowns normalisieren
    $nullify = [
        'Aufenthaltsstatus',
        'Bildungshintergrund',
        'PAZ',
        'Bereich_berufserfahrung',
        'Land_berufserfahrung',
        'Zeit_berufserfahrung',
        'Staatszugehörigkeit_Kategorie',
    ];
    foreach ($nullify as $f) {
        if (array_key_exists($f, $data)) {
            $v = trim((string)($data[$f] ?? ''));
            $data[$f] = ($v === '' || $v === '?' || $v === '— bitte wählen —') ? null : $v;
        }
    }

    // 4) Checkboxen konsistent setzen
    // Tipp: In der Edit-Form jeweils ein <input type="hidden" name="XYZ" value="0"> vor dem Checkbox-Input einfügen,
    // dann kommt beim Uncheck zuverlässig "0" an.
    foreach (['IDEA_Stammdatenblatt','IDEA_Dokumente','Clearing_gruppe'] as $f) {
        $data[$f] = $request->boolean($f);
    }

    // 5) Gruppe optional
    $data['gruppe_id'] = $request->filled('gruppe_id') ? (int)$request->input('gruppe_id') : null;

    // 6) Geburtsdatum robust parsen
    if (array_key_exists('Geburtsdatum', $data)) {
        $raw = trim((string)$data['Geburtsdatum']);
        if ($raw === '') {
            $data['Geburtsdatum'] = null;
        } else {
            try {
                if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $raw)) {
                    $dt = \Carbon\Carbon::createFromFormat('d.m.Y', $raw);
                } else {
                    $dt = \Carbon\Carbon::parse($raw);
                }
                $data['Geburtsdatum'] = $dt->format('Y-m-d');
            } catch (\Throwable $e) {
                $data['Geburtsdatum'] = null;
            }
        }
    }

    // 7) Update Teilnehmer
    $teilnehmer->update($data);

    // 8) Kompetenzstände aus Formular (optional) – wie bei store()
    //    Erwartete Struktur:
    //    kompetenz[Eintritt][<kompetenz_id>] = <niveau_id or ''>
    //    kompetenz[Austritt][<kompetenz_id>] = <niveau_id or ''>
    //    kompetenz[datum][Eintritt] / [Austritt]
    //    kompetenz[bemerkung][Eintritt] / [Austritt]
    $payload = $request->input('kompetenz', []);
    $payload += [
        'Eintritt'  => $payload['Eintritt']  ?? [],
        'Austritt'  => $payload['Austritt']  ?? [],
        'datum'     => $payload['datum']     ?? [],
        'bemerkung' => $payload['bemerkung'] ?? [],
    ];

    $cleanup = function ($arr) {
        if (!is_array($arr)) return [];
        $out = [];
        foreach ($arr as $k => $v) {
            $v = ($v === '' || $v === null) ? null : (int)$v;
            if (!is_null($v)) $out[(int)$k] = $v; // kompetenz_id => niveau_id
        }
        return $out;
    };
    $payload['Eintritt'] = $cleanup($payload['Eintritt']);
    $payload['Austritt'] = $cleanup($payload['Austritt']);

    $hasAny =
        !empty($payload['Eintritt']) ||
        !empty($payload['Austritt']) ||
        !empty(array_filter($payload['datum'] ?? [])) ||
        !empty(array_filter($payload['bemerkung'] ?? []));

    if ($hasAny) {
        $svc->saveForTeilnehmer($teilnehmer->Teilnehmer_id, $payload);
    }

    return redirect()
        ->route('teilnehmer.show', $teilnehmer)
        ->with('success', 'Teilnehmer erfolgreich aktualisiert.');
}

    /**
     * Validierung für Store/Update.
     */
    private function validateData(Request $request): array
    {
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

            'Minderheit'              => ['nullable','in:0,1'],
            'Behinderung'             => ['nullable','in:0,1'],
            'Obdachlos'               => ['nullable','in:0,1'],
            'LändlicheGebiete'        => ['nullable','in:0,1'],
            'ElternImAuslandGeboren'  => ['nullable','in:0,1'],
            'Armutsbetroffen'         => ['nullable','in:0,1'],
            'Armutsgefährdet'         => ['nullable','in:0,1'],

            'Bildungshintergrund' => 'nullable|in:ISCED0,ISCED1,ISCED2,ISCED3,ISCED4,ISCED5-8',

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

            'gruppe_id'           => 'nullable|integer|exists:gruppen,gruppe_id',
        ]);
    }

    /**
     * Checkboxen / Tri-State auf 1/0/null normalisieren.
     */
    private function normalizeCheckboxes(Request $request, array $data): array
    {
        $boolFields = [
            'Minderheit','Behinderung','Obdachlos','LändlicheGebiete',
            'ElternImAuslandGeboren','Armutsbetroffen','Armutsgefährdet',
            'IDEA_Stammdatenblatt','IDEA_Dokumente','Clearing_gruppe',
        ];

        $map = [
            '1' => 1, 1 => 1, true => 1, 'true' => 1, 'Ja' => 1, 'ja' => 1,
            '0' => 0, 0 => 0, false => 0, 'false' => 0, 'Nein' => 0, 'nein' => 0,
            '' => null, null => null, 'Keine Angabe' => null, 'keine angabe' => null,
        ];

        foreach ($boolFields as $f) {
            $raw = $request->input($f, null);
            $data[$f] = $map[$raw] ?? (is_numeric($raw) ? (int)$raw : null);
        }

        return $data;
    }

    /**
     * Dropdowns mit "?" / "" auf null setzen.
     */
    private function normalizeDropdowns(array $data, array $keys): array
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }
            $v = $data[$key];
            if ($v === '?' || $v === '' || $v === null) {
                $data[$key] = null;
            }
        }
        return $data;
    }

    /**
     * Datum robust auf Y-m-d 00:00 normalisieren.
     */
    private function normalizeDates(?string $value): ?Carbon
    {
        if (!$value) return null;

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Throwable $e) {}

        try {
            return Carbon::createFromFormat('d.m.Y', $value)->startOfDay();
        } catch (\Throwable $e) {}

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function saveKompetenzstand(\Illuminate\Http\Request $request, \App\Models\Teilnehmer $teilnehmer, KompetenzstandService $service)
{
    $data = $request->validate([
        'eintritt' => 'array',
        'eintritt.*' => 'nullable|integer|exists:niveau,niveau_id',
        'austritt' => 'array',
        'austritt.*' => 'nullable|integer|exists:niveau,niveau_id',
    ]);

    $service->saveForTeilnehmer(
        (int)$teilnehmer->Teilnehmer_id,
        $data['eintritt'] ?? [],
        $data['austritt'] ?? []
    );

    return redirect()
        ->route('teilnehmer.edit', $teilnehmer)
        ->with('success', 'Kompetenzstand gespeichert.');
}
}
