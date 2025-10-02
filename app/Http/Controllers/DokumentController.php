<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;
use App\Models\Dokument;
use App\Models\Teilnehmer;
use App\Models\Projekt;
use App\Models\Mitarbeiter;
use App\Models\TeilnehmerProjekt;
use App\Models\Gruppe;


use Illuminate\Support\Facades\Log;

class DokumentController extends Controller
{
    /**
     * Liste der Vorlagen + Generator.
     */
    public function index(Request $request)
    {
        // Vorlagen
        $docs = Dokument::orderBy('name')->get();

        // Projekte (für Auswahl im Generator)
        $projekte = Projekt::orderBy('bezeichnung')->get();

        // Teilnehmer mit Suche + Pagination
        $q_tn  = trim($request->get('q_tn', ''));
        $tnQry = Teilnehmer::query();

        if ($q_tn !== '') {
            $tnQry->where(function ($q) use ($q_tn) {
                $q->where('Nachname', 'like', "%{$q_tn}%")
                  ->orWhere('Vorname', 'like', "%{$q_tn}%")
                  ->orWhere('Email', 'like', "%{$q_tn}%");
            });
        }

        $tnRows = $tnQry->orderBy('Nachname')->orderBy('Vorname')
                        ->paginate(10)->withQueryString();

        // Mitarbeiter mit Suche + Pagination
        $q_ma  = trim($request->get('q_ma', ''));
        $maQry = Mitarbeiter::query();

        if ($q_ma !== '') {
            $maQry->where(function ($q) use ($q_ma) {
                $q->where('Nachname', 'like', "%{$q_ma}%")
                  ->orWhere('Vorname', 'like', "%{$q_ma}%")
                  ->orWhere('Email', 'like', "%{$q_ma}%");
            });
        }

        $maRows = $maQry->orderBy('Nachname')->orderBy('Vorname')
                        ->paginate(10)->withQueryString();

        // Vorauswahl aus Query
        $teilnehmerSelected  = $request->filled('teilnehmer_id')
            ? Teilnehmer::find($request->integer('teilnehmer_id'))
            : null;

        $mitarbeiterSelected = $request->filled('mitarbeiter_id')
            ? Mitarbeiter::find($request->integer('mitarbeiter_id'))
            : null;

        return view('dokumente.index', [
            'docs'                => $docs,
            'projekte'            => $projekte,
            'tnRows'              => $tnRows,
            'maRows'              => $maRows,
            'teilnehmerSelected'  => $teilnehmerSelected,
            'mitarbeiterSelected' => $mitarbeiterSelected,
            'q_tn'                => $q_tn,
            'q_ma'                => $q_ma,
        ]);
    }

    /** Kleine Hilfe: komplette Skala aus config/levels.php als String
     *
    private function scaleString(string $key): string
    {
        $levels = config("levels.$key", []);
        $levels = array_values(array_unique(array_map('trim', $levels)));
        return implode(', ', $levels);
    }

    */


    private function pickLevel(Teilnehmer $t, string $outCol, string $inCol): string
    {
    $out = trim((string)($t->{$outCol} ?? ''));
    if ($out !== '') return $out;

    $in  = trim((string)($t->{$inCol} ?? ''));
    return $in !== '' ? $in : '—';
    }


    /** Hilfsfunktion: baut alle Ersetzungs-Variablen für die Bestätigung */
    private function certVars(Teilnehmer $t, ?Gruppe $g = null): array
{
    // Ort (Fallback)
    $ort = trim((string)$t->Wohnort);
    if ($ort === '') {
        $ort = config('app.city') ?? config('org.city') ?? 'Graz';
    }

    $datum = now()->format('d.m.Y');

    // Zeitraum aus Pivot
    $von = $bis = '';
    $pivot = $g
        ? $t->gruppen()->where('gruppen.gruppe_id', $g->gruppe_id)->first()?->pivot
        : $t->gruppen()->orderBy('gruppe_teilnehmer.beitritt_von')->first()?->pivot;

    if ($pivot) {
        $von = $pivot->beitritt_von ? \Illuminate\Support\Carbon::parse($pivot->beitritt_von)->format('d.m.Y') : '';
        $bis = $pivot->beitritt_bis ? \Illuminate\Support\Carbon::parse($pivot->beitritt_bis)->format('d.m.Y') : '';
    }

    $projektName = $g->name ?? '';

    // >>> HIER: tatsächliche Level (Austritt, sonst Eintritt)
    $lesen     = $this->pickLevel($t, 'de_lesen_out',     'de_lesen_in');
    $hoeren    = $this->pickLevel($t, 'de_hoeren_out',    'de_hoeren_in');
    $schreiben = $this->pickLevel($t, 'de_schreiben_out', 'de_schreiben_in');
    $sprechen  = $this->pickLevel($t, 'de_sprechen_out',  'de_sprechen_in');
    $englisch  = $this->pickLevel($t, 'en_out',           'en_in');
    $mathe     = $this->pickLevel($t, 'ma_out',           'ma_in');

    return [
        '{Ort}'               => $ort,
        '{Datum}'             => $datum,
        '{ZeitraumVon}'       => $von,
        '{ZeitraumBis}'       => $bis,
        '{ProjektName}'       => $projektName,

        '{DeutschLesen}'      => $lesen,
        '{DeutschHoeren}'     => $hoeren,
        '{DeutschSchreiben}'  => $schreiben,
        '{DeutschSprechen}'   => $sprechen,
        '{EnglischNiveau}'    => $englisch,
        '{MathematikNiveau}'  => $mathe,
    ];
}


    /**
     * Hilfsfunktion: nimm die erste existierende Spalte aus Kandidaten.
     */
    private function pickCol(string $table, array $candidates): ?string
    {
        if (!Schema::hasTable($table)) return null;
        $cols = Schema::getColumnListing($table);
        foreach ($candidates as $c) {
            if (in_array($c, $cols, true)) return $c;
        }
        return null;
    }

    /**
     * Neue Vorlage (nutzt edit-View).
     */
    public function create()
    {
        $dokument = new Dokument(['is_active' => 1]);
        return view('dokumente.edit', compact('dokument'));
    }

    /**
     * Vorlage speichern.
     */
    public function store(Request $r)
    {
        $data = $r->validate([
            'name'      => 'required|string|max:150',
            'slug'      => 'nullable|string|max:150|unique:dokumente,slug',
            'is_active' => 'sometimes|boolean',
            'body'      => 'required|string',
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = $r->boolean('is_active');

        $dokument = Dokument::create($data);

        return redirect()
            ->route('dokumente.edit', $dokument)
            ->with('success', 'Vorlage gespeichert.');
    }

    /**
     * Vorlage bearbeiten.
     */
    public function edit(Dokument $dokument)
    {
        return view('dokumente.edit', compact('dokument'));
    }

    /**
     * Vorlage aktualisieren.
     */
    public function update(Request $r, Dokument $dokument)
    {
        $data = $r->validate([
            'name'      => 'required|string|max:150',
            'slug'      => 'required|string|max:150|unique:dokumente,slug,'.$dokument->getKey().','.$dokument->getKeyName(),
            'is_active' => 'sometimes|boolean',
            'body'      => 'required|string',
        ]);

        $data['is_active'] = $r->boolean('is_active');

        $dokument->update($data);

        return back()->with('success', 'Vorlage aktualisiert.');
    }

    /**
     * Vorlage löschen.
     */
    public function destroy(Dokument $dokument)
    {
        $dokument->delete();

        return redirect()
            ->route('dokumente.index')
            ->with('success', 'Vorlage gelöscht.');
    }

    /**
     * Generator (POST /dokumente/go): Vorschau oder PDF erzeugen.
     */
    public function go(Request $request)
    {
        $tblTn = (new Teilnehmer)->getTable();
        $tblPr = (new Projekt)->getTable();
        $tblMa = (new Mitarbeiter)->getTable();

        $data = $request->validate([
            'dokument_slug' => ['required','string'],
            'teilnehmer_id' => ['required','integer', Rule::exists($tblTn, 'Teilnehmer_id')],
            'projekt_id'    => ['nullable','integer', Rule::exists($tblPr, 'projekt_id')],
            'mitarbeiter_id'=> ['nullable','integer', Rule::exists($tblMa, 'Mitarbeiter_id')],
            'pdf'           => ['nullable'],
        ]);

        $dokument    = Dokument::where('slug', $data['dokument_slug'])->firstOrFail();
        $teilnehmer  = Teilnehmer::findOrFail($data['teilnehmer_id']);
        $projekt     = !empty($data['projekt_id'])     ? Projekt::find($data['projekt_id'])         : null;
        $mitarbeiter = !empty($data['mitarbeiter_id']) ? Mitarbeiter::find($data['mitarbeiter_id']) : null;

        $wantsPdf = $request->boolean('pdf');

        // Tokens (inkl. Skalen & Ort/Datum-Fallbacks)
        $tokens = $this->buildTokens($teilnehmer, $projekt, $mitarbeiter, $wantsPdf);

        $replacements = [];
        foreach ($tokens as $k => $v) {
            $replacements['{' . $k . '}'] = (string) $v;
        }

        $htmlBody = strtr($dokument->body ?? '', $replacements);

        if ($wantsPdf && class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $wrapped = $this->wrapForPrint($htmlBody);
            $file    = 'Dokument_'.$dokument->slug.'_'.now()->format('Ymd_His').'.pdf';
            return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($wrapped)->download($file);
        }

        return view('dokumente.render', [
            'html'        => $htmlBody,
            'title'       => $dokument->name,
            'pdfAvailable'=> class_exists(\Barryvdh\DomPDF\Facade\Pdf::class),
            'formPayload' => $data,
        ]);
    }

    /** Beispiel: Render-Action, die die Tokens ersetzt */
    public function render(Teilnehmer $teilnehmer, string $slug)
    {
        $dokument = Dokument::where('slug', $slug)->firstOrFail();

        // Gruppe optional
        $gruppe = null;
        if ($gid = request('gruppe_id')) {
            $gruppe = Gruppe::find($gid);
        } else {
            $gruppe = $teilnehmer->gruppen()->first();
        }

        $raw  = $dokument->inhalt ?? $dokument->template ?? $dokument->body;
        $text = strtr($raw, $this->certVars($teilnehmer, $gruppe));

        return view('dokumente.rendered', [
            'text'      => $text,
            'teilnehmer'=> $teilnehmer,
            'dokument'  => $dokument,
        ]);
    }

    /**
     * (Optional) Auswahlseite Projekt/Mitarbeiter für TN.
     */
    public function prepare(Teilnehmer $teilnehmer, Dokument $dokument)
    {
        $tp = $teilnehmer->teilnehmerProjekte()->orderByDesc('beginn')->get();
        $mitarbeiter = Mitarbeiter::orderBy('Nachname')->orderBy('Vorname')->get();

        $vorauswahlProjektId     = optional($tp->first())->projekt_id;
        $vorauswahlMitarbeiterId = optional($tp->first()?->projekt)->standard_mitarbeiter_id;

        return view('dokumente.prepare', compact(
            'teilnehmer','dokument','tp','mitarbeiter','vorauswahlProjektId','vorauswahlMitarbeiterId'
        ));
    }

    /** Beispiel: PDF-Erzeugung – gleicher Trick vor dem PDF-Export */
    public function generatePdf(Teilnehmer $teilnehmer, Dokument $dokument)
    {
        $gruppe = request('gruppe_id') ? Gruppe::find(request('gruppe_id')) : $teilnehmer->gruppen()->first();
        $raw    = $dokument->inhalt ?? $dokument->template ?? $dokument->body;
        $text   = strtr($raw, $this->certVars($teilnehmer, $gruppe));

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dokumente.rendered', [
            'text'       => $text,
            'teilnehmer' => $teilnehmer,
            'dokument'   => $dokument,
        ]);

        return $pdf->download(Str::slug($dokument->titel ?? 'bestaetigung').'.pdf');
    }

    /**
     * Platzhalterersetzung – Variante mit Start/Ende und Dauer.
     */
    protected function renderForTeilnehmerUndProjekt(Dokument $doc, Teilnehmer $t, Projekt $p, ?Mitarbeiter $m, $start, $ende): string
    {
        $format = fn($d) => $d ? Carbon::parse($d)->format('d.m.Y') : '';

        $anrede = $this->anredeAusTeilnehmer($t);
        $geburtsdatum = $t->Geburtsdatum ? Carbon::parse($t->Geburtsdatum)->format('d.m.Y') : '';

        // Dauer grob
        $dauerTage   = ($start && $ende) ? Carbon::parse($start)->diffInDays(Carbon::parse($ende)) + 1 : null;
        $dauerWochen = $dauerTage ? round($dauerTage/7, 1) : null;
        $dauerMonate = ($start && $ende) ? Carbon::parse($start)->floatDiffInMonths(Carbon::parse($ende)) : null;

        $map = [
            // Teilnehmer
            '{Anrede}'        => e($anrede),
            '{Vorname}'       => e($t->Vorname ?? ''),
            '{Nachname}'      => e($t->Nachname ?? ''),
            '{Geburtsdatum}'  => $geburtsdatum,

            // Projekt
            '{Projekt}'       => e($p->bezeichnung ?? ''),
            '{ProjektCode}'   => e($p->code ?? ''),
            '{ProjektBeginn}' => $format($start),
            '{ProjektEnde}'   => $format($ende),
            '{KursdauerTage}'   => $dauerTage ? (string)$dauerTage : '',
            '{KursdauerWochen}' => $dauerWochen ? (string)$dauerWochen : '',
            '{KursdauerMonate}' => $dauerMonate ? number_format($dauerMonate, 1, ',', '') : '',

            // Mitarbeiter
            '{MitarbeiterVorname}'   => $m?->Vorname ? e($m->Vorname) : '',
            '{MitarbeiterNachname}'  => $m?->Nachname ? e($m->Nachname) : '',
            '{MitarbeiterTaetigkeit}'=> $m?->Taetigkeit ? e($m->Taetigkeit) : '',
            '{MitarbeiterEmail}'     => $m?->Email ? e($m->Email) : '',
            '{MitarbeiterTelefon}'   => $m?->Telefonnummer ? e($m->Telefonnummer) : '',

            // Ort/Datum (mit Fallback)
            '{Ort}'           => e($t->Wohnort ?: (config('app.city') ?? config('org.city') ?? 'Graz')),
            '{Heute}'         => now()->format('d.m.Y'),
            '{Datum}'         => now()->format('d.m.Y'),
        ];

        return strtr($doc->body, $map);
    }

    /**
     * Universelle Tokens für go()/render(). Deckt Person, Projekt, Mitarbeiter,
     * Kenntnisse, Praktika, Logos ab – PLUS Skalen + Datum/Ort-Fallback.
     */
    protected function buildTokens(
        Teilnehmer $teilnehmer,
        ?Projekt $projekt = null,
        ?Mitarbeiter $mitarbeiter = null,
        bool $forPdf = false
    ): array
    {
       $fmtDate = function ($date, string $format = 'd.m.Y') {
        if (!$date) return '';
        try { return \Illuminate\Support\Carbon::parse($date)->format($format); }
        catch (\Throwable $e) { return (string) $date; }
        };
        $n = fn($v) => $v ?? '';

        // Teilnehmer-Foto (relativ zu storage/app/public)
        $fotoRel = $this->guessTeilnehmerPhotoPath($teilnehmer);
        $teilnehmerFotoTag = '';
        if ($fotoRel) {
            if ($src = $this->imageSrcStorage($fotoRel, $forPdf)) {
                $teilnehmerFotoTag = '<img src="'.$src.'" alt="Foto" style="max-height:90px;border-radius:6px;">';
            }
        }

        // Logos aus /public/images/*
        $logo = fn(string $file, string $alt) =>
            ($src = $this->imageSrcPublic('images/'.$file, $forPdf))
                ? '<img src="'.$src.'" alt="'.$alt.'" style="max-height:60px;">'
                : '';

        // Fallbacks für Ort/Datum
        $ortFallback = $teilnehmer->Wohnort
            ?: ($projekt->ort ?? (config('app.city') ?? config('org.city') ?? 'Graz'));
        $heute = now()->format('d.m.Y');



        // >>> tatsächliche Level (Austritt, sonst Eintritt)
        $lesen     = $this->pickLevel($teilnehmer, 'de_lesen_out',     'de_lesen_in');
        $hoeren    = $this->pickLevel($teilnehmer, 'de_hoeren_out',    'de_hoeren_in');
        $schreiben = $this->pickLevel($teilnehmer, 'de_schreiben_out', 'de_schreiben_in');
        $sprechen  = $this->pickLevel($teilnehmer, 'de_sprechen_out',  'de_sprechen_in');
        $englisch  = $this->pickLevel($teilnehmer, 'en_out',           'en_in');
        $mathe     = $this->pickLevel($teilnehmer, 'ma_out',           'ma_in');


        return [
            // Person
            'Anrede'        => $this->anredeAusTeilnehmer($teilnehmer),
            'Vorname'       => $n($teilnehmer->Vorname),
            'Nachname'      => $n($teilnehmer->Nachname),
            'Geburtsdatum'  => $fmtDate($teilnehmer->Geburtsdatum),

            // Projekt
            'Projekt'       => $n(optional($projekt)->bezeichnung ?? ''),
            'ProjektName'   => $n(optional($projekt)->bezeichnung ?? ''), // für {ProjektName}
            'ProjektBeginn' => $fmtDate(optional($projekt)->beginn ?? null),
            'ProjektEnde'   => $fmtDate(optional($projekt)->ende   ?? null),

            // Mitarbeiter
            'MitarbeiterVorname'  => $n(optional($mitarbeiter)->Vorname ?? ''),
            'MitarbeiterNachname' => $n(optional($mitarbeiter)->Nachname ?? ''),

            // Ort/Datum (inkl. {Datum})
            'Ort'   => $n($ortFallback),
            'Heute' => $heute,
            'Datum' => $heute,

            // Logos / Bilder
            'LogoTop'        => $logo('logo.jpg',      'Logo'),
            'LogoBit'        => $logo('bit.jpg',       'bit'),
            'LogoOecert'     => $logo('oecert.jpg',    'oeCERT'),
            'LogoOrg'        => $logo('org-photo.jpg', 'Organisation'),
            'LogoEU'         => $logo('eu.jpg',        'EU'),
            'LogoAMS'        => $logo('ams.jpg',       'AMS'),
            'TeilnehmerFoto' => $teilnehmerFotoTag,

            // Tabellen
            'PraktikaTabelleHtml'   => $this->renderPraktikaTable($teilnehmer),
            'KenntnisseTabelleHtml' => $this->renderKenntnisseTable($teilnehmer),

            // >>> Skalen (wichtig für deine Vorlage!)
            'DeutschLesen'      => $lesen,
            'DeutschHoeren'     => $hoeren,
            'DeutschSchreiben'  => $schreiben,
            'DeutschSprechen'   => $sprechen,
            'EnglischNiveau'    => $englisch,
            'MathematikNiveau'  => $mathe,
        ];
    }

    /**
     * (Optional) Praktika als HTML-Tabelle für Vorlagen.
     */
    private function renderPraktikaTable(Teilnehmer $tn): string
    {
        $table = 'teilnehmer_praktika';
        if (!Schema::hasTable($table)) {
            return '<p class="small">Keine Praktika vorhanden.</p>';
        }

        $colVon = Schema::hasColumn($table, 'beginn')
            ? 'beginn' : $this->pickCol($table, ['beginn_datum','von']);
        $colBis = Schema::hasColumn($table, 'ende')
            ? 'ende'   : $this->pickCol($table, ['ende_datum','bis']);
        $colStd = Schema::hasColumn($table, 'stunden_ausmass')
            ? 'stunden_ausmass' : $this->pickCol($table, ['stundenumfang','stunden','Stunden']);

        $rows = method_exists($tn, 'praktika')
            ? $tn->praktika()
                ->when($colVon, fn($q) => $q->orderBy($colVon))
                ->get()
            : collect();

        if ($rows->isEmpty()) {
            return '<p class="small">Keine Praktika vorhanden.</p>';
        }

        $out = '<table style="width:100%; border-collapse:collapse; margin:6pt 0 0 0;">
    <thead>
        <tr>
        <th style="border-bottom:1px solid #ddd; text-align:left; padding:6pt;">Bereich</th>
        <th style="border-bottom:1px solid #ddd; text-align:left; padding:6pt;">Firma</th>
        <th style="border-bottom:1px solid #ddd; text-align:left; padding:6pt;">Zeitraum</th>
        <th style="border-bottom:1px solid #ddd; text-align:right; padding:6pt;">Stunden</th>
        <th style="border-bottom:1px solid #ddd; text-align:left; padding:6pt;">Anmerkung</th>
        </tr>
    </thead><tbody>';

        foreach ($rows as $r) {
            $vonVal = $colVon ? ($r->$colVon ?? null) : null;
            $bisVal = $colBis ? ($r->$colBis ?? null) : null;

            $fmt = function ($v) {
                if (!$v) return '';
                try { return Carbon::parse($v)->format('d.m.Y'); }
                catch (\Throwable $e) { return (string)$v; }
            };

            $zeit = trim(($fmt($vonVal) ?: '').' – '.($fmt($bisVal) ?: ''));

            $stdRaw = $colStd ? ($r->$colStd ?? null) : null;
            $stdOut = ($stdRaw !== null && $stdRaw !== '')
                ? number_format((float)$stdRaw, 2, ',', '.')
                : '—';

            $out .= '<tr>
        <td style="border-bottom:1px solid #eee; padding:6pt;">'.e($r->bereich ?? '—').'</td>
        <td style="border-bottom:1px solid #eee; padding:6pt;">'.e($r->firma ?? '—').'</td>
        <td style="border-bottom:1px solid #eee; padding:6pt;">'.($zeit !== '–' ? e($zeit) : '—').'</td>
        <td style="border-bottom:1px solid #eee; padding:6pt; text-align:right;">'.$stdOut.'</td>
        <td style="border-bottom:1px solid #eee; padding:6pt;">'.e($r->anmerkung ?? '—').'</td>
        </tr>';
        }

        $out .= '</tbody></table>';
        return $out;
    }

    /**
     * (Optional) Kenntnisse als HTML-Tabelle.
     */
    private function renderKenntnisseTable(Teilnehmer $tn): string
    {
        $k = method_exists($tn, 'kenntnisse') ? $tn->kenntnisse?->first() : null;
        if (!$k) {
            return '<p class="small">Keine Einträge zu Kenntnissen vorhanden.</p>';
        }

        $norm = function (?string $v): string {
            if (!$v) return '—';
            $v = trim($v);
            $v = str_replace(',', '.', $v);
            return strtoupper($v);
        };

        $cells = [
            'Deutsch – Leseverstehen'  => $norm($k->DeutschLesen     ?? $k->deutsch_lesen     ?? null),
            'Deutsch – Hörverstehen'   => $norm($k->DeutschHoeren    ?? $k->deutsch_hoeren    ?? null),
            'Deutsch – Schreiben'      => $norm($k->DeutschSchreiben ?? $k->deutsch_schreiben ?? null),
            'Deutsch – Sprechen'       => $norm($k->DeutschSprechen  ?? $k->deutsch_sprechen  ?? null),
            'Englisch'                 => $norm($k->EnglischNiveau   ?? $k->englisch          ?? null),
            'Mathematik'               => $norm($k->MathematikNiveau ?? $k->mathematik        ?? null),
        ];

        $out = '<table style="width:100%; border-collapse:collapse; margin:6pt 0 0 0;">
      <thead>
        <tr>
          <th style="border-bottom:1px solid #ddd; text-align:left; padding:6pt;">Kompetenz</th>
          <th style="border-bottom:1px solid #ddd; text-align:left; padding:6pt;">Niveau</th>
        </tr>
      </thead><tbody>';

        foreach ($cells as $label => $val) {
            $out .= '<tr>
          <td style="border-bottom:1px solid #eee; padding:6pt;">'.e($label).'</td>
          <td style="border-bottom:1px solid #eee; padding:6pt;">'.e($val).'</td>
        </tr>';
        }

        $out .= '</tbody></table>';
        return $out;
    }

    private function anredeAusTeilnehmer(?Teilnehmer $tn): string
    {
        $g = mb_strtolower(trim((string)($tn?->Geschlecht ?? '')));
        if ($g === 'frau') return 'Frau';
        if ($g === 'mann' || $g === 'herr') return 'Herr';
        return '';
    }

    private function wrapForPrint(string $innerHtml): string
    {
        return <<<HTML
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <title>Dokument</title>
  <style>
    @page { margin: 24mm 18mm; }
    body  { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12pt; color: #111; }
    .page { max-width: 720px; margin: 0 auto; }
    .header { display:flex; align-items:center; justify-content:space-between; margin-bottom: 24pt; }
    .logo   { height: 64px; }
    .photo  { height: 64px; }
    h1,h2 { margin: 0 0 10pt 0; }
    p { line-height: 1.45; margin: 0 0 10pt 0; }
    .small { font-size: 9pt; color:#555; }
    .hr { border-top:1px solid #ddd; margin: 12pt 0; }
    table { width:100%; border-collapse: collapse; }
    th, td { padding: 6pt; }
  </style>
</head>
<body>
  <div class="page">
    {$innerHtml}
  </div>
</body>
</html>
HTML;
    }

    private function imgSrc(string $relPath): string
    {
        $path = public_path($relPath);
        if (!is_file($path)) {
            return '';
        }
        $mime = mime_content_type($path) ?: 'image/jpeg';
        $data = base64_encode(file_get_contents($path));
        return "data:$mime;base64,$data";
    }

    private function guessTeilnehmerPhotoPath(Teilnehmer $t): ?string
    {
        $candidates = [
            'foto_pfad','photo_path','bild_pfad','avatar_path','foto','photo','bild','avatar'
        ];
        foreach ($candidates as $field) {
            if (isset($t->$field) && is_string($t->$field) && $t->$field !== '') {
                $v = ltrim($t->$field, '/');
                $v = str_starts_with($v, 'storage/') ? substr($v, strlen('storage/')) : $v;
                return $v; // Relativ zu storage/app/public
            }
        }
        return null;
    }

    private function imageSrcPublic(string $publicRelativePath, bool $forPdf): ?string
    {
        $publicRelativePath = ltrim($publicRelativePath, '/');
        $abs = public_path($publicRelativePath);

        if (!is_file($abs)) return null;

        if ($forPdf) {
            $mime = mime_content_type($abs) ?: 'image/jpeg';
            $data = base64_encode(file_get_contents($abs));
            return "data:$mime;base64,$data";
        }

        return asset($publicRelativePath);
    }

    private function imageSrcStorage(string $storageRelativePath, bool $forPdf): ?string
    {
        $storageRelativePath = ltrim($storageRelativePath, '/');
        $abs = storage_path('app/public/'.$storageRelativePath);

        if (!is_file($abs)) return null;

        if ($forPdf) {
            $mime = mime_content_type($abs) ?: 'image/jpeg';
            $data = base64_encode(file_get_contents($abs));
            return "data:$mime;base64,$data";
        }

        return asset('storage/'.$storageRelativePath);
    }
}
