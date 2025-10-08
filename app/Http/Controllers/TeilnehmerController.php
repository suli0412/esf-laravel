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
use App\Models\Kompetenz;
use App\Models\Niveau;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Kompetenzstand;
use Illuminate\Support\Facades\Log;


class TeilnehmerController extends Controller
{


    /**
 * Befüllt für einen Teilnehmer Demo-Kompetenzstände (Eintritt & Austritt).
 * Überschreibt/ergänzt vorhandene Werte per Upsert.
 */
    public function setDemoKompetenzen(Teilnehmer $teilnehmer, KompetenzstandService $svc): RedirectResponse
    {
    // Optional: $this->authorize('update', $teilnehmer);

    // Demo-Mappings (Kompetenz-Code -> Niveau-Code)
    $eintritt = [
        'DE_LESEN'=>'A1', 'DE_HOEREN'=>'A1', 'DE_SCHREIBEN'=>'A1', 'DE_SPRECHEN'=>'A1',
        'EN_GESAMT'=>'A1', 'MATHE'=>'A2', 'IKT'=>'A2',
    ];
    $austritt = [
        'DE_LESEN'=>'B1', 'DE_HOEREN'=>'B1', 'DE_SCHREIBEN'=>'A2.2', 'DE_SPRECHEN'=>'B1.1',
        'EN_GESAMT'=>'A2', 'MATHE'=>'B1', 'IKT'=>'B1',
    ];

    // Codes -> IDs
    $kByCode = DB::table('kompetenzen')->pluck('kompetenz_id','code');
    $nByCode = DB::table('niveau')->pluck('niveau_id','code');

    // Fehlende Codes freundlich melden
    $allKomps = array_keys($eintritt + $austritt);
    $allNivs  = array_values($eintritt + $austritt);
    $missingKomps = array_values(array_filter($allKomps, fn($c)=>!isset($kByCode[$c])));
    $missingNivs  = array_values(array_filter(array_unique($allNivs), fn($c)=>!isset($nByCode[$c])));
    if ($missingKomps || $missingNivs) {
        return back()->with('error',
            'Demo nicht gesetzt. Fehlende Einträge: '.
            ($missingKomps ? 'Kompetenzen: '.implode(', ',$missingKomps).'. ' : '').
            ($missingNivs  ? 'Niveaus: '.implode(', ',$missingNivs).'.' : '')
        );
    }

    // Map in ID->ID
    $toIdMap = function(array $map) use ($kByCode,$nByCode) {
        $out = [];
        foreach ($map as $kCode=>$nCode) {
            $out[$kByCode[$kCode]] = $nByCode[$nCode];
        }
        return $out;
    };
    $eintrittIdMap = $toIdMap($eintritt);
    $austrittIdMap = $toIdMap($austritt);

    DB::transaction(function () use ($teilnehmer, $svc, $eintrittIdMap, $austrittIdMap) {
        // schreibt/updated und löscht alte, nicht mehr gesetzte Einträge
        $svc->saveForTeilnehmer((int)$teilnehmer->Teilnehmer_id, $eintrittIdMap, $austrittIdMap);

        // Datum heute setzen, damit die View ein Datum zeigt
        $today = now()->toDateString();
        foreach ($eintrittIdMap as $kompetenzId => $niveauId) {
            DB::table('kompetenzstand')->where([
                'teilnehmer_id' => (int)$teilnehmer->Teilnehmer_id,
                'zeitpunkt'     => 'Eintritt',
                'kompetenz_id'  => $kompetenzId,
            ])->update(['datum' => $today]);
        }
        foreach ($austrittIdMap as $kompetenzId => $niveauId) {
            DB::table('kompetenzstand')->where([
                'teilnehmer_id' => (int)$teilnehmer->Teilnehmer_id,
                'zeitpunkt'     => 'Austritt',
                'kompetenz_id'  => $kompetenzId,
            ])->update(['datum' => $today]);
        }
    });

    Log::channel('activity')->info('demo.kompetenzen.set', [
        'teilnehmer_id' => (int)$teilnehmer->Teilnehmer_id,
        'by_user'       => auth()->id(),
    ]);

    return redirect()
        ->route('teilnehmer.show', $teilnehmer)
        ->with('success', 'Demo-Kompetenzen für Eintritt & Austritt gesetzt.');
    }


    /**
     * Liste der Teilnehmer + Suche + Pagination.
     */
    public function index(Request $request)
{
    // Ansicht (default: compact)
    $view = $request->query('view', 'compact');

    // Filter-/Sortier-Inputs für die View
    $q         = trim((string)$request->query('q', ''));
    $gruppeId  = $request->query('gruppe_id');
    $hasDocs   = $request->query('has_docs', null);   // '1' | '0' | null
    $sort      = $request->query('sort', 'name_asc'); // name_asc|gruppe|created_desc|updated_desc

    // Für die Filterleiste (Dropdown "Gruppe")
    $gruppen = Gruppe::orderBy('name')->get();

    // Praktika-Sortierspalte robust wählen (beginn | von | created_at)
    $praktikaOrder = Schema::hasColumn('teilnehmer_praktika', 'beginn')
        ? 'beginn'
        : (Schema::hasColumn('teilnehmer_praktika', 'von') ? 'von' : 'created_at');

    // Query aufbauen
    $query = Teilnehmer::query()
        ->with([
            'checkliste',
            'beratungen'    => fn ($q2) => $q2->orderByDesc('datum')->limit(3),
            'anwesenheiten' => fn ($q2) => $q2->orderByDesc('datum')->limit(3),
            'praktika'      => fn ($q2) => $q2->orderByDesc($praktikaOrder)->limit(3),
            'dokumente'     => fn ($q2) => $q2->orderByDesc('created_at')->limit(3),
        ])
        ->withCount(['beratungen','anwesenheiten','praktika','dokumente']);

    // Suche (Name, Vorname oder #ID)
    if ($q !== '') {
        $query->where(function ($qq) use ($q) {
            // Zahl → als ID (#ESF) interpretieren
            if (preg_match('/^\#?(\d+)$/', $q, $m)) {
                $id = (int)$m[1];
                $qq->orWhere('Teilnehmer_id', $id);
            }
            // Name/Freitext
            $qq->orWhere('Nachname', 'like', '%'.$q.'%')
               ->orWhere('Vorname',  'like', '%'.$q.'%');
        });
    }

    // Filter: Gruppe
    if (!empty($gruppeId)) {
        $query->where('gruppe_id', (int)$gruppeId);
    }

    // Filter: Dokumente vorhanden/keine
    if ($hasDocs === '1') {
        $query->has('dokumente');
    } elseif ($hasDocs === '0') {
        $query->doesntHave('dokumente');
    }

    // Sortierung
    switch ($sort) {
        case 'gruppe':
            $query->orderByRaw('CASE WHEN gruppe_id IS NULL THEN 1 ELSE 0 END') // ohne Gruppe zuletzt
                  ->orderBy('gruppe_id')
                  ->orderBy('Nachname')
                  ->orderBy('Vorname');
            break;

        case 'created_desc':
            $query->orderByDesc('created_at');
            break;

        case 'updated_desc':
            $query->orderByDesc('updated_at');
            break;

        case 'name_asc':
        default:
            $query->orderBy('Nachname')->orderBy('Vorname');
            break;
    }

    // Paginieren – wichtig: als $rows an die View
    $rows = $query->paginate(12);

    return view('teilnehmer.index', compact(
        'rows', 'view', 'q', 'gruppen', 'gruppeId', 'hasDocs', 'sort'
    ));
}


    /**
     * Formular für neuen Teilnehmer.
     */
    public function create()
    {
        $kompetenzen = Kompetenz::orderBy('code')->get(); // Tippfehler? Falls dein Model App\Models\Kompetenz heißt:
        // $kompetenzen = Kompetenz::orderBy('code')->get();
        $kompetenzen = Kompetenz::orderBy('code')->get();
        $niveaus     = Niveau::orderBy('sort_order')->get();
        $teilnehmer  = new Teilnehmer();
        $gruppen     = Gruppe::orderBy('name')->get();

        return view('teilnehmer.create', [
            'teilnehmer' => $teilnehmer,
            'gruppen'    => $gruppen,
            'kompetenzen'=> $kompetenzen,
            'niveaus'    => $niveaus,
            'levelsDe'   => config('levels.deutsch'),
            'levelsEn'   => config('levels.englisch'),
            'levelsMa'   => config('levels.mathe'),
        ]);
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

        // --- Level-Whitelist für Validierung
        $de = implode(',', config('levels.deutsch'));
        $en = implode(',', config('levels.englisch'));
        $ma = implode(',', config('levels.mathe'));

        // 2) Validierung + Unique-Regeln
        $data = $request->validate([
            'Nachname' => 'required|string|max:100',
            'Vorname'  => 'required|string|max:100',

            'Geschlecht' => 'sometimes|nullable|string|max:30',
            'SVN'   => ['sometimes','nullable','string','max:12', Rule::unique('teilnehmer','SVN')],
            'Strasse'    => 'sometimes|nullable|string|max:150',
            'Hausnummer' => 'sometimes|nullable|string|max:10',
            'PLZ'        => 'sometimes|nullable|string|max:10',
            'Wohnort'    => 'sometimes|nullable|string|max:150',
            'Land'       => 'sometimes|nullable|string|max:100',
            'Email'      => ['sometimes','nullable','email','max:150', Rule::unique('teilnehmer','Email')],
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

            // Gruppe: später Pivot
            'gruppe_id'  => 'sometimes|nullable|integer|exists:gruppen,gruppe_id',
            'beitritt_von' => 'sometimes|nullable|date',

            // Eintritt
            'de_lesen_in'      => "nullable|in:$de",
            'de_hoeren_in'     => "nullable|in:$de",
            'de_schreiben_in'  => "nullable|in:$de",
            'de_sprechen_in'   => "nullable|in:$de",
            'en_in'            => "nullable|in:$en",
            'ma_in'            => "nullable|in:$ma",

            // Austritt (beim Neuanlegen optional)
            'de_lesen_out'     => 'nullable',
            'de_hoeren_out'    => 'nullable',
            'de_schreiben_out' => 'nullable',
            'de_sprechen_out'  => 'nullable',
            'en_out'           => 'nullable',
            'ma_out'           => 'nullable',
        ]);

        // 3) Dropdowns normalisieren
        $nullify = [
            'Aufenthaltsstatus','Bildungshintergrund','PAZ',
            'Bereich_berufserfahrung','Land_berufserfahrung','Zeit_berufserfahrung',
            'Staatszugehörigkeit_Kategorie',
        ];
        foreach ($nullify as $f) {
            if (array_key_exists($f, $data)) {
                $v = trim((string)($data[$f] ?? ''));
                $data[$f] = ($v === '' || $v === '?' || $v === '— bitte wählen —') ? null : $v;
            }
        }

        // 4) Checkboxen konsistent setzen
        foreach (['IDEA_Stammdatenblatt','IDEA_Dokumente','Clearing_gruppe'] as $f) {
            $data[$f] = $request->boolean($f);
        }

        // 5) Gruppe aus Request herausnehmen (Pivot später)
        $gruppeId    = $request->filled('gruppe_id') ? (int)$request->input('gruppe_id') : null;
        $beitrittVon = $request->input('beitritt_von') ?: now()->toDateString();
        unset($data['gruppe_id'], $data['beitritt_von']);

        // 6) Geburtsdatum robust parsen
        if (array_key_exists('Geburtsdatum', $data)) {
            $raw = trim((string)$data['Geburtsdatum']);
            if ($raw === '') {
                $data['Geburtsdatum'] = null;
            } else {
                try {
                    if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $raw)) {
                        $dt = Carbon::createFromFormat('d.m.Y', $raw);
                    } else {
                        $dt = Carbon::parse($raw);
                    }
                    $data['Geburtsdatum'] = $dt->format('Y-m-d');
                } catch (\Throwable $e) {
                    $data['Geburtsdatum'] = null;
                }
            }
        }

        // 6b) Duplikat-Check
        $dup = Teilnehmer::query();
        if (!empty($data['SVN'])) {
            $dup->where('SVN', $data['SVN']);
        } elseif (!empty($data['Nachname']) && !empty($data['Vorname']) && !empty($data['Geburtsdatum'])) {
            $dup->whereRaw('LOWER(TRIM(Vorname)) = ?', [mb_strtolower(trim($data['Vorname']))])
                ->whereRaw('LOWER(TRIM(Nachname)) = ?', [mb_strtolower(trim($data['Nachname']))])
                ->whereDate('Geburtsdatum', $data['Geburtsdatum']);
        }
        if ($dup->exists()) {
            return back()
                ->withErrors(['Nachname' => 'Dieser Teilnehmer existiert bereits (gleiche Personendaten oder gleiche SVN).'])
                ->withInput();
        }

        // 7) Anlegen (+ optionaler Gruppen-Pivot + Kompetenzstände) in Transaktion
        $hasAnyKomp = false; // für Logging
        $teilnehmer = DB::transaction(function () use ($data, $gruppeId, $beitrittVon, $request, $svc, &$hasAnyKomp) {
            $teilnehmer = Teilnehmer::create($data);

            // 8) Kompetenzstände aus Formular
            $payload = $request->input('kompetenz', []);
            $payload += [
                'Eintritt'  => $payload['Eintritt']  ?? [],
                'Austritt'  => $payload['Austritt']  ?? [],
                'datum'     => $payload['datum']     ?? [],
                'bemerkung' => $payload['bemerkung'] ?? [],
            ];

            $cleanup = function($arr) {
                if (!is_array($arr)) return [];
                $out = [];
                foreach ($arr as $k => $v) {
                    $v = ($v === '' || $v === null) ? null : (int)$v;
                    if (!is_null($v)) $out[(int)$k] = $v;
                }
                return $out;
            };
            $eintritt = $cleanup($payload['Eintritt']);
            $austritt = $cleanup($payload['Austritt']);

            $hasAnyKomp =
                !empty($eintritt) ||
                !empty($austritt) ||
                !empty(array_filter($payload['datum'] ?? [])) ||
                !empty(array_filter($payload['bemerkung'] ?? []));

            if ($hasAnyKomp) {
                $svc->saveForTeilnehmer($teilnehmer->Teilnehmer_id, $eintritt, $austritt);
            }

            // 9) Falls Gruppe gewählt: Pivot setzen
            if ($gruppeId) {
                $teilnehmer->gruppe()->syncWithoutDetaching([
                    $gruppeId => ['beitritt_von' => $beitrittVon],
                ]);
            }

            return $teilnehmer;
        });

        // ✨ Aktivitäts-Log (außerhalb der TX)
        Log::channel('activity')->info('teilnehmer.created', [
            'teilnehmer_id' => $teilnehmer->Teilnehmer_id,
            'name'          => trim(($teilnehmer->Vorname ?? '').' '.($teilnehmer->Nachname ?? '')),
            'gruppe_id'     => $gruppeId,
            'beitritt_von'  => $gruppeId ? $beitrittVon : null,
            'kompetenzen'   => $hasAnyKomp,
            'by_user'       => auth()->id(),
        ]);

        return redirect()
            ->route('teilnehmer.show', $teilnehmer)
            ->with('success', 'Teilnehmer erfolgreich angelegt.');
    }

public function show(Teilnehmer $teilnehmer, Request $request)
{
    // 1) Monat (YYYY-MM) robust parsen
    $monatParam = (string) $request->query('monat', now()->format('Y-m'));
    try { $cur = Carbon::createFromFormat('Y-m', $monatParam)->startOfMonth(); }
    catch (\Throwable $e) { $cur = now()->startOfMonth(); }

    $monat     = $cur->format('Y-m');
    $prevMonat = $cur->copy()->subMonth()->format('Y-m');
    $nextMonat = $cur->copy()->addMonth()->format('Y-m');
    $tnId      = $teilnehmer->Teilnehmer_id;

    // 2) Relationen laden (einmal, vollständig) – mit robusten Order-Spalten
    $dokOrder = Schema::hasColumn('teilnehmer_dokumente', 'hochgeladen_am') ? 'hochgeladen_am' : 'created_at';

    $tablePrak = 'teilnehmer_praktika';
    $prakVonCol = Schema::hasColumn($tablePrak, 'von') ? 'von'
                 : (Schema::hasColumn($tablePrak, 'beginn_datum') ? 'beginn_datum'
                 : (Schema::hasColumn($tablePrak, 'beginn') ? 'beginn' : null));

    $teilnehmer->load([
        'createdBy:id,name',
        'updatedBy:id,name',
        'checkliste',
        'gruppe',
        'beratungen.mitarbeiter',
        'beratungen.art',
        'beratungen.thema',
        'kompetenzstaende.kompetenz',
        'kompetenzstaende.niveau',
        'dokumente' => fn ($q) => $q->orderByDesc($dokOrder),
        'praktika'  => function ($q) use ($prakVonCol) {
            if ($prakVonCol) {
                $q->orderByDesc($prakVonCol);
            } else {
                $q->orderByDesc($q->getModel()->getKeyName());
            }
        },
        'anwesenheiten' => fn ($q) => $q->orderBy('datum'),
    ]);

    // 3) KPI / Zähler (aus geladenen Collections)
    $anzahlDokumente  = $teilnehmer->dokumente->count();
    $anzahlBeratungen = $teilnehmer->beratungen->count();

    // 4) Dropdown-/Hilfsdaten
    $arten  = \App\Models\Beratungsart::orderBy('Code')->get();
    $themen = \App\Models\Beratungsthema::orderBy('Bezeichnung')->get();

    $docs = \App\Models\Dokument::query()
        ->when(method_exists(\App\Models\Dokument::class, 'scopeAktiv'),
            fn ($q) => $q->aktiv(),
            fn ($q) => $q->where('is_active', 1))
        ->orderBy('name')
        ->get();

    // 5) Anwesenheit (monatlich)
    $anwesenheiten = $teilnehmer->anwesenheiten
        ->whereBetween('datum', [$cur->copy()->startOfMonth(), $cur->copy()->endOfMonth()])
        ->values();
    $fehlminutenSumme = (int) $anwesenheiten->sum('fehlminuten');

    // 6) Praktika-Stunden gesamt
    $praktikaStundenSumme = method_exists($teilnehmer, 'praktika')
        ? (float) $teilnehmer->praktika()->sum('stunden_ausmass')
        : 0.0;

    // 7) Kompetenzstände (Eintritt/Austritt) robust
    $alleKomps = \App\Models\Kompetenz::orderBy('code')
        ->get(['kompetenz_id','code','bezeichnung'])
        ->keyBy('kompetenz_id');

    $niv = \App\Models\Niveau::get(['niveau_id','code','label'])->keyBy('niveau_id');

    $st = collect($teilnehmer->kompetenzstaende ?? []);

    $normZeitpunkt = function ($row) {
        $val = $row->zeitpunkt_norm ?? $row->zeitpunkt ?? '';
        $val = preg_replace('/\s+/u', ' ', (string)$val);
        $val = trim(mb_strtolower($val));
        return match ($val) {
            'ein','e','in','entry'   => 'eintritt',
            'aus','a','out','exit'   => 'austritt',
            default                  => $val,
        };
    };

    $mapEin = $st->filter(fn ($s) => $normZeitpunkt($s) === 'eintritt')->keyBy('kompetenz_id');
    $mapAus = $st->filter(fn ($s) => $normZeitpunkt($s) === 'austritt')->keyBy('kompetenz_id');

    $eintrittList = $alleKomps->values()->map(function ($k) use ($mapEin, $niv) {
        $row = $mapEin->get($k->kompetenz_id);
        $nivRow = $row && $row->niveau_id ? ($niv[$row->niveau_id] ?? null) : null;
        return (object) [
            'kcode'     => $k->code,
            'kbez'      => $k->bezeichnung,
            'niveau_id' => $row->niveau_id ?? null,
            'ncode'     => $nivRow->code ?? null,
            'nlabel'    => $nivRow->label ?? null,
            'datum'     => $row->datum ?? null,
            'bemerkung' => $row->bemerkung ?? null,
        ];
    });

    $austrittList = $alleKomps->values()->map(function ($k) use ($mapAus, $niv) {
        $row = $mapAus->get($k->kompetenz_id);
        $nivRow = $row && $row->niveau_id ? ($niv[$row->niveau_id] ?? null) : null;
        return (object) [
            'kcode'     => $k->code,
            'kbez'      => $k->bezeichnung,
            'niveau_id' => $row->niveau_id ?? null,
            'ncode'     => $nivRow->code ?? null,
            'nlabel'    => $nivRow->label ?? null,
            'datum'     => $row->datum ?? null,
            'bemerkung' => $row->bemerkung ?? null,
        ];
    });

    // 8) View
    return view('teilnehmer.show', [
        'teilnehmer'            => $teilnehmer,
        'arten'                 => $arten,
        'themen'                => $themen,
        'docs'                  => $docs,
        'anwesenheiten'         => $anwesenheiten,
        'monat'                 => $monat,
        'prevMonat'             => $prevMonat,
        'nextMonat'             => $nextMonat,
        'tnId'                  => $tnId,
        'fehlminutenSumme'      => $fehlminutenSumme,
        'praktikaStundenSumme'  => $praktikaStundenSumme,
        'eintrittList'          => $eintrittList,
        'austrittList'          => $austrittList,
        'anzahlDokumente'       => $anzahlDokumente,
        'anzahlBeratungen'      => $anzahlBeratungen,
        // optional fürs Debugging:
        'alleKomps'             => $alleKomps,
        'mapEin'                => $mapEin,
        'mapAus'                => $mapAus,
        // counts für rechte Karten (falls du sie in der View brauchst)
        'counts'                => [
            'beratungen'    => $anzahlBeratungen,
            'anwesenheiten' => $teilnehmer->anwesenheiten()->count(),
            'praktika'      => $teilnehmer->praktika()->count(),
            'dokumente'     => $anzahlDokumente,
        ],
    ]);
}

    /**
     * Formular zum Bearbeiten.
     */
    public function edit(Teilnehmer $teilnehmer)
    {
        $gruppen     = Gruppe::orderBy('name')->get();
        $kompetenzen = \App\Models\Kompetenz::orderBy('code')->get(['kompetenz_id','code','bezeichnung']);
        $niveaus     = Niveau::orderBy('sort_order')->get();

        // Dokumente mit jüngstem Upload zuerst
        $teilnehmer->load(['dokumente' => fn($q) => $q->orderByDesc('hochgeladen_am')]);
        $teilnehmer->load([
        'kompetenzstaende.kompetenz',
        'kompetenzstaende.niveau',
        ]);
        // Kompetenzstände holen
        $st = DB::table('kompetenzstand')
            ->where('teilnehmer_id', $teilnehmer->Teilnehmer_id)
            ->get(['kompetenz_id','niveau_id','zeitpunkt','zeitpunkt_norm']);


        $eintrittMap = $st->where('zeitpunkt_norm', 'eintritt')->pluck('niveau_id','kompetenz_id')->all();
        $austrittMap = $st->where('zeitpunkt_norm', 'austritt')->pluck('niveau_id','kompetenz_id')->all();


        return view('teilnehmer.edit', [
            'teilnehmer'  => $teilnehmer,
            'gruppen'     => $gruppen,
            'kompetenzen' => $kompetenzen,
            'niveaus'     => $niveaus,
            'eintrittMap' => $eintrittMap,
            'austrittMap' => $austrittMap,
            'docTypes'    => config('dokumente.teilnehmer_types', ['PDF','Foto','Sonstiges']),
            'levelsDe'    => config('levels.deutsch', []),
            'levelsEn'    => config('levels.englisch', []),
            'levelsMa'    => config('levels.mathe', []),
        ]);
    }

    /**
     * Update eines Teilnehmers.
     */
   public function update(Request $request, Teilnehmer $teilnehmer, KompetenzstandService $svc)
{
    //  Aliase
    $request->merge([
        'Nachname' => $request->input('Nachname', $request->input('nachname')),
        'Vorname'  => $request->input('Vorname',  $request->input('vorname')),
    ]);

    // Level-Whitelist
    $de = implode(',', config('levels.deutsch'));
    $en = implode(',', config('levels.englisch'));
    $ma = implode(',', config('levels.mathe'));

    //  Validierung
    $data = $request->validate([
        'Nachname' => 'required|string|max:100',
        'Vorname'  => 'required|string|max:100',

        'Geschlecht' => 'sometimes|nullable|string|max:30',
        'SVN'        => ['sometimes','nullable','string','max:12', Rule::unique('teilnehmer','SVN')->ignore($teilnehmer->Teilnehmer_id, 'Teilnehmer_id')],
        'Strasse'    => 'sometimes|nullable|string|max:150',
        'Hausnummer' => 'sometimes|nullable|string|max:10',
        'PLZ'        => 'sometimes|nullable|string|max:10',
        'Wohnort'    => 'sometimes|nullable|string|max:150',
        'Land'       => 'sometimes|nullable|string|max:100',
        'Email'      => ['sometimes','nullable','email','max:150', Rule::unique('teilnehmer','Email')->ignore($teilnehmer->Teilnehmer_id, 'Teilnehmer_id')],
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

        // Eintritt (Codes wie A0, A1 …)
        'de_lesen_in'      => "nullable|in:$de",
        'de_hoeren_in'     => "nullable|in:$de",
        'de_schreiben_in'  => "nullable|in:$de",
        'de_sprechen_in'   => "nullable|in:$de",
        'en_in'            => "nullable|in:$en",
        'ma_in'            => "nullable|in:$ma",

        // Austritt
        'de_lesen_out'     => "nullable|in:$de",
        'de_hoeren_out'    => "nullable|in:$de",
        'de_schreiben_out' => "nullable|in:$de",
        'de_sprechen_out'  => "nullable|in:$de",
        'en_out'           => "nullable|in:$en",
        'ma_out'           => "nullable|in:$ma",

        // optionaler Schalter
        'prune_missing'    => 'sometimes|boolean',
    ]);

    // 3) Dropdowns normalisieren
    $nullify = [
        'Aufenthaltsstatus','Bildungshintergrund','PAZ',
        'Bereich_berufserfahrung','Land_berufserfahrung','Zeit_berufserfahrung',
        'Staatszugehörigkeit_Kategorie',
    ];
    foreach ($nullify as $f) {
        if (array_key_exists($f, $data)) {
            $v = trim((string)($data[$f] ?? ''));
            $data[$f] = ($v === '' || $v === '?' || $v === '— bitte wählen —') ? null : $v;
        }
    }

    // 4) Checkboxen konsistent
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
                    $dt = Carbon::createFromFormat('d.m.Y', $raw);
                } else {
                    $dt = Carbon::parse($raw);
                }
                $data['Geburtsdatum'] = $dt->format('Y-m-d');
            } catch (\Throwable $e) {
                $data['Geburtsdatum'] = null;
            }
        }
    }

    // 6b) Duplikat-Check (aktuellen ausschließen)
    $dup = Teilnehmer::query()->whereKeyNot($teilnehmer->Teilnehmer_id);
    if (!empty($data['SVN'])) {
        $dup->where('SVN', $data['SVN']);
    } elseif (!empty($data['Nachname']) && !empty($data['Vorname']) && !empty($data['Geburtsdatum'])) {
        $dup->whereRaw('LOWER(TRIM(Vorname)) = ?', [mb_strtolower(trim($data['Vorname']))])
            ->whereRaw('LOWER(TRIM(Nachname)) = ?', [mb_strtolower(trim($data['Nachname']))])
            ->whereDate('Geburtsdatum', $data['Geburtsdatum']);
    }
    if ($dup->exists()) {
        return back()
            ->withErrors(['Nachname' => 'Konflikt: Datensatz mit diesen Personendaten/SVN existiert bereits.'])
            ->withInput();
    }

    // Für Logging: vorher/nachher vergleichen
    $before = [
        'Nachname' => $teilnehmer->Nachname,
        'Vorname'  => $teilnehmer->Vorname,
        'Email'    => $teilnehmer->Email,
        'gruppe_id'=> $teilnehmer->gruppe_id ?? null,
    ];

    // 7) Update Teilnehmer
    $teilnehmer->update($data);

    // 8) Kompetenzstände – bestehende Struktur (Service) + UI-Felder zusammenführen
    //    a) Alte Struktur (Form: kompetenz[Eintritt][kompetenz_id] = niveau_id)
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
            $v = ($v === '' || $v === null) ? null : (int)$v;   // niveau_id erwartet
            if (!is_null($v)) $out[(int)$k] = $v;               // kompetenz_id => niveau_id
        }
        return $out;
    };
    $eintritt = $cleanup($payload['Eintritt']);
    $austritt = $cleanup($payload['Austritt']);

    //    b) NEU: UI-Felder (Codes A0/A1…/M0) -> auflösen zu niveau_id und kompetenz_id
    $mapFormToKompetenz = [
        'de_lesen'     => 'DE_LESEN',
        'de_hoeren'    => 'DE_HOEREN',
        'de_schreiben' => 'DE_SCHREIBEN',
        'de_sprechen'  => 'DE_SPRECHEN',
        'en'           => 'EN',
        'ma'           => 'MA',
    ];
    $kompByCode = \App\Models\Kompetenz::get(['kompetenz_id','code'])
        ->keyBy(fn($k) => strtoupper($k->code));
    $nivByCode  = \App\Models\Niveau::get(['niveau_id','code'])
        ->keyBy(fn($n) => strtoupper($n->code));

    $uiEin = [];
    $uiAus = [];
    foreach ($mapFormToKompetenz as $prefix => $kompCode) {
        $komp = $kompByCode->get(strtoupper($kompCode));
        if (!$komp) continue;

        $inCode  = trim((string)$request->input("{$prefix}_in", ''));
        $outCode = trim((string)$request->input("{$prefix}_out", ''));

        if ($inCode !== '') {
            $niv = $nivByCode->get(strtoupper($inCode));
            if ($niv) $uiEin[$komp->kompetenz_id] = (int)$niv->niveau_id;
        }
        if ($outCode !== '') {
            $niv = $nivByCode->get(strtoupper($outCode));
            if ($niv) $uiAus[$komp->kompetenz_id] = (int)$niv->niveau_id;
        }
    }

    // UI-Werte überschreiben ggf. ältere Struktur
    if (!empty($uiEin)) $eintritt = array_replace($eintritt, $uiEin);
    if (!empty($uiAus)) $austritt = array_replace($austritt, $uiAus);

    // Flag: wurde überhaupt etwas zu Kompetenzen übermittelt?
    $hasAnyKomp =
        !empty($eintritt) ||
        !empty($austritt) ||
        !empty(array_filter($payload['datum'] ?? [])) ||
        !empty(array_filter($payload['bemerkung'] ?? [])) ||
        !empty($uiEin) || !empty($uiAus);

    // 8c) Speichern via Service
    if ($hasAnyKomp) {
        $svc->saveForTeilnehmer($teilnehmer->Teilnehmer_id, $eintritt, $austritt);
    }

    // 8d) Optional: nicht gesetzte Einträge löschen (nur die, die dieses UI abdeckt)
    if ($request->boolean('prune_missing')) {
        $allKompetenzIds = collect($mapFormToKompetenz)
            ->map(fn($code) => optional($kompByCode->get(strtoupper($code)))->kompetenz_id)
            ->filter()->values()->all();

        $keepEin = array_keys($eintritt); // aus gemergten Arrays
        $keepAus = array_keys($austritt);

        // Eintritt
        \DB::table('kompetenzstand')
            ->where('teilnehmer_id', $teilnehmer->Teilnehmer_id)
            ->where('zeitpunkt_norm','eintritt')
            ->whereIn('kompetenz_id', $allKompetenzIds)
            ->when(!empty($keepEin), fn($q) => $q->whereNotIn('kompetenz_id', $keepEin))
            ->delete();

        // Austritt
        \DB::table('kompetenzstand')
            ->where('teilnehmer_id', $teilnehmer->Teilnehmer_id)
            ->where('zeitpunkt_norm','austritt')
            ->whereIn('kompetenz_id', $allKompetenzIds)
            ->when(!empty($keepAus), fn($q) => $q->whereNotIn('kompetenz_id', $keepAus))
            ->delete();
    }

    // ✨ Aktivitäts-Log
    $after = [
        'Nachname' => $teilnehmer->Nachname,
        'Vorname'  => $teilnehmer->Vorname,
        'Email'    => $teilnehmer->Email,
        'gruppe_id'=> $teilnehmer->gruppe_id ?? null,
    ];
    $changed = [];
    foreach ($after as $k => $v) {
        if (($before[$k] ?? null) !== $v) {
            $changed[] = $k;
        }
    }

    Log::channel('activity')->info('teilnehmer.updated', [
        'teilnehmer_id' => $teilnehmer->Teilnehmer_id,
        'changed'       => $changed,
        'kompetenzen'   => $hasAnyKomp ? 'updated' : 'unchanged',
        'by_user'       => auth()->id(),
        'submitted_ui'  => [
            // nur zur Nachvollziehbarkeit (Codes), keine IDs
            'de_lesen_in'      => $request->input('de_lesen_in'),
            'de_hoeren_in'     => $request->input('de_hoeren_in'),
            'de_schreiben_in'  => $request->input('de_schreiben_in'),
            'de_sprechen_in'   => $request->input('de_sprechen_in'),
            'en_in'            => $request->input('en_in'),
            'ma_in'            => $request->input('ma_in'),
            'de_lesen_out'     => $request->input('de_lesen_out'),
            'de_hoeren_out'    => $request->input('de_hoeren_out'),
            'de_schreiben_out' => $request->input('de_schreiben_out'),
            'de_sprechen_out'  => $request->input('de_sprechen_out'),
            'en_out'           => $request->input('en_out'),
            'ma_out'           => $request->input('ma_out'),
            'prune_missing'    => $request->boolean('prune_missing'),
        ],
    ]);

    return redirect()
        ->route('teilnehmer.show', $teilnehmer)
        ->with('success', 'Teilnehmer erfolgreich aktualisiert.');
}

    public function saveKompetenzstand(Request $request, Teilnehmer $teilnehmer, KompetenzstandService $service)
    {
        $data = $request->validate([
            'eintritt'   => 'array',
            'eintritt.*' => 'nullable|integer|exists:niveau,niveau_id',
            'austritt'   => 'array',
            'austritt.*' => 'nullable|integer|exists:niveau,niveau_id',
        ]);

        $service->saveForTeilnehmer(
            (int)$teilnehmer->Teilnehmer_id,
            $data['eintritt'] ?? [],
            $data['austritt'] ?? []
        );

        // ✨ Aktivitäts-Log
        Log::channel('activity')->info('teilnehmer.kompetenz.saved', [
            'teilnehmer_id' => $teilnehmer->Teilnehmer_id,
            'eintritt_cnt'  => isset($data['eintritt']) ? count(array_filter($data['eintritt'])) : 0,
            'austritt_cnt'  => isset($data['austritt']) ? count(array_filter($data['austritt'])) : 0,
            'by_user'       => auth()->id(),
        ]);

        return redirect()
            ->route('teilnehmer.edit', $teilnehmer)
            ->with('success', 'Kompetenzstand gespeichert.');
    }

    public function destroy(Teilnehmer $teilnehmer): RedirectResponse
    {
        if (! auth()->user()->can('teilnehmer.delete')) {
            abort(403, 'Keine Berechtigung zum Löschen');
        }

        // Für Log: Snapshot vor dem Löschen
        $snapshot = [
            'teilnehmer_id' => $teilnehmer->Teilnehmer_id,
            'name'          => trim(($teilnehmer->Vorname ?? '').' '.($teilnehmer->Nachname ?? '')),
            'docs_count'    => $teilnehmer->dokumente()->count(),
            'ber_count'     => $teilnehmer->beratungen()->count(),
            'anw_count'     => $teilnehmer->anwesenheiten()->count(),
        ];

        DB::transaction(function () use ($teilnehmer) {
            $teilnehmer->gruppe()->detach();
            $teilnehmer->projekte()->detach();
            $teilnehmer->pruefungstermine()->detach();
            $teilnehmer->gruppenBeratungen()->detach();

            $teilnehmer->anwesenheiten()->delete();
            $teilnehmer->beratungen()->delete();
            $teilnehmer->teilnehmerProjekte()->delete();
            $teilnehmer->kompetenzstaende()->delete();
            if ($teilnehmer->kenntnisse)  { $teilnehmer->kenntnisse()->delete(); }
            if ($teilnehmer->checkliste)  { $teilnehmer->checkliste()->delete(); }

            foreach ($teilnehmer->dokumente as $doc) {
                if ($doc->dokument_pfad) {
                    Storage::disk('public')->delete($doc->dokument_pfad);
                }
                $doc->delete();
            }

            $teilnehmer->delete();
        });

        // ✨ Aktivitäts-Log
        Log::channel('activity')->warning('teilnehmer.deleted', $snapshot + [
            'by_user' => auth()->id(),
        ]);

        return redirect()
            ->route('teilnehmer.index')
            ->with('success', 'Teilnehmer wurde gelöscht.');
    }
}
