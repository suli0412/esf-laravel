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
     * Liste der Teilnehmer + Suche + Pagination.
     */
    public function index()
    {
        $q         = trim(request('q', ''));
        $gruppeId  = request('gruppe_id');
        $hasDocs   = request('has_docs'); // "1" = nur TN mit Docs, "0" = nur ohne
        $sort      = request('sort', 'name_asc');
        $view      = request('view', 'table'); // "table" oder "cards"

        $qry = \App\Models\Teilnehmer::query()
            ->with(['gruppe'])
            ->withCount(['dokumente', 'praktika']);

        if ($q !== '') {
            $qry->where(function($x) use ($q) {
                $x->where('Nachname', 'like', "%{$q}%")
                  ->orWhere('Vorname', 'like', "%{$q}%")
                  ->orWhere('Email', 'like', "%{$q}%")
                  ->orWhere('Telefonnummer', 'like', "%{$q}%");
            });
        }

        if ($gruppeId) {
            $qry->where('gruppe_id', $gruppeId);
        }

        if ($hasDocs === '1') {
            $qry->has('dokumente');
        } elseif ($hasDocs === '0') {
            $qry->doesntHave('dokumente');
        }

        // Sortierung
        switch ($sort) {
            case 'created_desc':  $qry->orderByDesc('created_at'); break;
            case 'created_asc':   $qry->orderBy('created_at'); break;
            case 'updated_desc':  $qry->orderByDesc('updated_at'); break;
            case 'updated_asc':   $qry->orderBy('updated_at'); break;
            case 'gruppe':        $qry->orderBy('gruppe_id')->orderBy('Nachname')->orderBy('Vorname'); break;
            default: // name_asc
                $qry->orderBy('Nachname')->orderBy('Vorname');
        }

        $rows    = $qry->paginate(20)->withQueryString();
        $gruppen = \App\Models\Gruppe::orderBy('name')->get();

        return view('teilnehmer.index', [
            'rows'     => $rows,
            'gruppen'  => $gruppen,
            'q'        => $q,
            'gruppeId' => $gruppeId,
            'hasDocs'  => $hasDocs,
            'sort'     => $sort,
            'view'     => $view,
        ]);
    }

    /**
     * Formular für neuen Teilnehmer.
     */
    public function create()
    {
        $kompetenzen = Kompetenze::orderBy('code')->get(); // Tippfehler? Falls dein Model App\Models\Kompetenz heißt:
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
        // Monat robust bestimmen (YYYY-MM)
        $monatParam = (string) $request->query('monat', now()->format('Y-m'));
        try {
            $cur = Carbon::createFromFormat('Y-m', $monatParam)->startOfMonth();
        } catch (\Throwable $e) {
            $cur = now()->startOfMonth();
        }
        $monat     = $cur->format('Y-m');
        $prevMonat = $cur->copy()->subMonth()->format('Y-m');
        $nextMonat = $cur->copy()->addMonth()->format('Y-m');
        $tnId      = $teilnehmer->Teilnehmer_id;

        // Relationen laden
        $teilnehmer->load([
            'createdBy:id,name',
            'updatedBy:id,name',
            'checkliste',
            'beratungen.mitarbeiter',
            'beratungen.art',
            'beratungen.thema',
            'dokumente' => fn($q) => $q->orderByDesc('hochgeladen_am'),
            'praktika'  => function ($q) {
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

        $anzahlDokumente  = $teilnehmer->dokumente->count();
        $anzahlBeratungen = $teilnehmer->beratungen->count();

        // Dropdown-/Hilfsdaten
        $arten  = Beratungsart::orderBy('Code')->get();
        $themen = Beratungsthema::orderBy('Bezeichnung')->get();

        $docs = Dokument::query()
            ->when(method_exists(Dokument::class, 'scopeAktiv'),
                fn($q) => $q->aktiv(),
                fn($q) => $q->where('is_active', 1))
            ->orderBy('name')
            ->get();

        // Anwesenheit (monatlich)
        $anwesenheiten = $teilnehmer->anwesenheiten()
            ->whereYear('datum',  $cur->year)
            ->whereMonth('datum', $cur->month)
            ->orderBy('datum')
            ->get();

        $fehlminutenSumme = (int) $anwesenheiten->sum('fehlminuten');

        // Praktika-Stunden gesamt
        $praktikaStundenSumme = 0.0;
        if (method_exists($teilnehmer, 'praktika')) {
            $praktikaStundenSumme = (float) $teilnehmer->praktika()->sum('stunden_ausmass');
        }

        // Kompetenzstand (Eintritt)
        $eintrittList = Kompetenzstand::query()
            ->eintritt()
            ->leftJoin('kompetenzen as k', 'k.kompetenz_id', '=', 'kompetenzstand.kompetenz_id')
            ->leftJoin('niveau as n',      'n.niveau_id',     '=', 'kompetenzstand.niveau_id')
            ->where('kompetenzstand.teilnehmer_id', $tnId)
            ->orderByRaw('CASE WHEN k.code IS NULL THEN 1 ELSE 0 END, k.code ASC')
            ->orderBy('kompetenzstand.kompetenz_id')
            ->get([
                'k.code as kcode',
                'k.bezeichnung as kbez',
                'n.code as ncode',
                'n.label as nlabel',
                'kompetenzstand.zeitpunkt',
                'kompetenzstand.datum',
                'kompetenzstand.bemerkung',
            ]);

        // Kompetenzstand (Austritt)
        $austrittList = Kompetenzstand::query()
            ->austritt()
            ->leftJoin('kompetenzen as k', 'k.kompetenz_id', '=', 'kompetenzstand.kompetenz_id')
            ->leftJoin('niveau as n',      'n.niveau_id',     '=', 'kompetenzstand.niveau_id')
            ->where('kompetenzstand.teilnehmer_id', $tnId)
            ->orderByRaw('CASE WHEN k.code IS NULL THEN 1 ELSE 0 END, k.code ASC')
            ->orderBy('kompetenzstand.kompetenz_id')
            ->get([
                'k.code as kcode',
                'k.bezeichnung as kbez',
                'n.code as ncode',
                'n.label as nlabel',
                'kompetenzstand.zeitpunkt',
                'kompetenzstand.datum',
                'kompetenzstand.bemerkung',
            ]);

        $teilnehmer->loadMissing([
            'createdBy:id,name,Vorname,Nachname',
            'updatedBy:id,name,Vorname,Nachname',
        ]);

        return view('teilnehmer.show', compact(
            'teilnehmer',
            'arten',
            'themen',
            'docs',
            'anwesenheiten',
            'monat',
            'prevMonat',
            'nextMonat',
            'tnId',
            'fehlminutenSumme',
            'praktikaStundenSumme',
            'eintrittList',
            'austrittList',
            'anzahlDokumente',
            'anzahlBeratungen',
        ));
    }

    /**
     * Formular zum Bearbeiten.
     */
    public function edit(Teilnehmer $teilnehmer)
    {
        $gruppen     = Gruppe::orderBy('name')->get();
        $kompetenzen = Kompetenz::orderBy('code')->get();
        $niveaus     = Niveau::orderBy('sort_order')->get();

        // Dokumente mit jüngstem Upload zuerst
        $teilnehmer->load(['dokumente' => fn($q) => $q->orderByDesc('hochgeladen_am')]);

        // Kompetenzstände holen
        $st = DB::table('kompetenzstand')
            ->where('teilnehmer_id', $teilnehmer->Teilnehmer_id)
            ->get(['kompetenz_id','niveau_id','zeitpunkt']);

        $eintrittMap = $st->where('zeitpunkt', 'Eintritt')->pluck('niveau_id','kompetenz_id')->all();
        $austrittMap = $st->where('zeitpunkt', 'Austritt')->pluck('niveau_id','kompetenz_id')->all();

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
        // 1) Aliase
        $request->merge([
            'Nachname' => $request->input('Nachname', $request->input('nachname')),
            'Vorname'  => $request->input('Vorname',  $request->input('vorname')),
        ]);

        // Level-Whitelist
        $de = implode(',', config('levels.deutsch'));
        $en = implode(',', config('levels.englisch'));
        $ma = implode(',', config('levels.mathe'));

        // 2) Validierung
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

            // Eintritt
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

        // 8) Kompetenzstände – wie bei store()
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
