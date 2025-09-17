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
        // Tabellen-Namen sicher aus den Models holen
        $tblTn = (new Teilnehmer)->getTable();
        $tblPr = (new Projekt)->getTable();
        $tblMa = (new Mitarbeiter)->getTable();

        $data = $request->validate([
            'dokument_slug' => ['required','string'],
            'teilnehmer_id' => ['required','integer', Rule::exists($tblTn, 'Teilnehmer_id')],
            'projekt_id'    => ['nullable','integer', Rule::exists($tblPr, 'projekt_id')],
            'mitarbeiter_id'=> ['nullable','integer', Rule::exists($tblMa, 'Mitarbeiter_id')],
            'pdf'           => ['nullable'], // Checkbox
        ]);

        $dokument    = Dokument::where('slug', $data['dokument_slug'])->firstOrFail();
        $teilnehmer  = Teilnehmer::findOrFail($data['teilnehmer_id']);
        $projekt     = !empty($data['projekt_id'])     ? Projekt::find($data['projekt_id'])         : null;
        $mitarbeiter = !empty($data['mitarbeiter_id']) ? Mitarbeiter::find($data['mitarbeiter_id']) : null;

        // Tokens
        $tokens = $this->buildTokens($teilnehmer, $projekt, $mitarbeiter);

        $replacements = [];
        foreach ($tokens as $k => $v) {
            $replacements['{' . $k . '}'] = (string) $v;
        }

        $htmlBody = strtr($dokument->body ?? '', $replacements);

        // PDF?
        $wantsPdf = $request->boolean('pdf');

        if ($wantsPdf && class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $wrapped = $this->wrapForPrint($htmlBody);
            $file    = 'Dokument_'.$dokument->slug.'_'.now()->format('Ymd_His').'.pdf';
            return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($wrapped)->download($file);
        }

        // Vorschau HTML
        return view('dokumente.render', [
            'html'        => $htmlBody,
            'title'       => $dokument->name,
            'pdfAvailable'=> class_exists(\Barryvdh\DomPDF\Facade\Pdf::class),
            'formPayload' => $data,
        ]);
    }

    /**
     * Direkt-Render von /teilnehmer/{teilnehmer}/dokumente/{slug}
     */
    public function render(Teilnehmer $teilnehmer, string $slug, Request $request)
    {
        $dokument = Dokument::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $projekt     = $request->filled('projekt_id')
            ? Projekt::find($request->integer('projekt_id'))
            : null;

        $mitarbeiter = $request->filled('mitarbeiter_id')
            ? Mitarbeiter::find($request->integer('mitarbeiter_id'))
            : null;

        $tokens = $this->buildTokens($teilnehmer, $projekt, $mitarbeiter);

        $replacements = [];
        foreach ($tokens as $k => $v) {
            $replacements['{' . $k . '}'] = (string) $v;
        }

        $htmlBody = strtr($dokument->body ?? '', $replacements);

        return view('dokumente.render', [
            'html'        => $htmlBody,
            'title'       => $dokument->name,
            'pdfAvailable'=> class_exists(\Barryvdh\DomPDF\Facade\Pdf::class),
            'formPayload' => [
                'dokument_slug' => $slug,
                'teilnehmer_id' => $teilnehmer->Teilnehmer_id,
                'projekt_id'    => $projekt?->projekt_id,
                'mitarbeiter_id'=> $mitarbeiter?->Mitarbeiter_id,
            ],
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

    /**
     * (Optional) PDF-Generierung mit gewähltem Projekt/Mitarbeiter.
     */
    public function generatePdf(Request $r, Teilnehmer $teilnehmer, Dokument $dokument)
    {
        if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            return back()->with('error', 'PDF-Paket ist nicht installiert.');
        }

        $tblPr = (new Projekt)->getTable();
        $tblMa = (new Mitarbeiter)->getTable();

        $data = $r->validate([
            'projekt_id'     => ['required','integer', Rule::exists($tblPr, 'projekt_id')],
            'mitarbeiter_id' => ['nullable','integer', Rule::exists($tblMa, 'Mitarbeiter_id')],
        ]);

        $projekt = Projekt::findOrFail($data['projekt_id']);
        $berater = $data['mitarbeiter_id'] ? Mitarbeiter::findOrFail($data['mitarbeiter_id']) : null;

        // Zeitraum aus Pivot, Fallback auf Projekt
        $tp = TeilnehmerProjekt::where('teilnehmer_id', $teilnehmer->Teilnehmer_id)
                               ->where('projekt_id', $projekt->projekt_id)
                               ->first();

        $start = $tp?->beginn ?? $projekt->beginn ?? null;
        $ende  = $tp?->ende   ?? $projekt->ende   ?? null;

        $html = $this->renderForTeilnehmerUndProjekt($dokument, $teilnehmer, $projekt, $berater, $start, $ende);

        $wrapped  = $this->wrapForPrint($html);
        $filename = 'Dokument_'.$dokument->slug.'_'.$teilnehmer->Nachname.'_'.$teilnehmer->Vorname.'.pdf';

        return \Barryvdh\DomPDF\Facade\Pdf::loadHTML($wrapped)->download($filename);
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

            // Ort/Datum
            '{Ort}'           => e($t->Wohnort ?: 'Graz'),
            '{Heute}'         => now()->format('d.m.Y'),
        ];

        return strtr($doc->body, $map);
    }

    /**
     * Universelle Tokens für go()/render().
     * Deckt Person, Projekt, Mitarbeiter, Kenntnisse, Praktika & Logos ab.
     */
    protected function buildTokens(Teilnehmer $teilnehmer, ?Projekt $projekt = null, ?Mitarbeiter $mitarbeiter = null): array
    {
        $fmtDate = function ($date, string $format = 'd.m.Y') {
            if (!$date) return '';
            try { return Carbon::parse($date)->format($format); }
            catch (\Throwable $e) { return (string) $date; }
        };
        $n = fn($v) => $v ?? '';

        // Kenntnisse (Relation optional)
        $kenntnisse = method_exists($teilnehmer, 'kenntnisse')
            ? optional($teilnehmer->kenntnisse)->first()
            : null;

        // Praktika (Relation optional, dynamische Spalten!)
        $prakTable = 'teilnehmer_praktika';
        $colVon = 'beginn';
        $colBis = 'ende';
        $colStd = 'stunden_ausmass';

        $letztesPraktikum = method_exists($teilnehmer, 'praktika')
            ? $teilnehmer->praktika()->orderByDesc($colVon)->first()
            : null;

        $praktikumStundenSumme = method_exists($teilnehmer, 'praktika')
            ? (float) $teilnehmer->praktika()->sum($colStd)
            : 0.0;

        $prakVon = $letztesPraktikum?->$colVon;
        $prakBis = $letztesPraktikum?->$colBis;
        $prakStd = $letztesPraktikum?->$colStd;


        // Anrede (einfach)
        $anrede = 'Sehr geehrte/r';
        if (($teilnehmer->Geschlecht ?? null) === 'Frau') $anrede = 'Sehr geehrte Frau';
        if (($teilnehmer->Geschlecht ?? null) === 'Mann') $anrede = 'Sehr geehrter Herr';

        // Ort & Heute
        $heute = now();
        $ort   = $teilnehmer->Wohnort ?: ($projekt->ort ?? '');

        // Logos (per Token direkt als <img>-Tag nutzbar)
        $logoTag = fn($file, $alt) => '<img src="/images/'.$file.'" alt="'.$alt.'" style="max-height:60px;">';

        return [
            // Person
            'Anrede'        => $anrede,
            'Vorname'       => $n($teilnehmer->Vorname),
            'Nachname'      => $n($teilnehmer->Nachname),
            'Geburtsdatum'  => $fmtDate($teilnehmer->Geburtsdatum),

            // Projekt
            'Projekt'       => $n(optional($projekt)->bezeichnung ?? ''),
            'ProjektBeginn' => $fmtDate(optional($projekt)->beginn ?? null),
            'ProjektEnde'   => $fmtDate(optional($projekt)->ende   ?? null),

            // Mitarbeiter
            'MitarbeiterVorname' => $n(optional($mitarbeiter)->Vorname ?? ''),
            'MitarbeiterNachname'=> $n(optional($mitarbeiter)->Nachname ?? ''),

            // Ort/Datum
            'Ort'   => $n($ort),
            'Heute' => $heute->format('d.m.Y'),

            // Logos / Bilder
            'LogoTop'    => $logoTag('logo.jpg',       'Logo'),
            'LogoBit'    => $logoTag('bit.jpg',        'bit'),
            'LogoOecert' => $logoTag('oecert.jpg',     'oeCERT'),
            'LogoOrg'    => $logoTag('org-photo.jpg',  'Organisation'),
            'LogoEU'     => $logoTag('eu.jpg',         'EU'),
            'LogoAMS'    => $logoTag('ams.jpg',        'AMS'),

            // Kenntnisse (mehrere mögliche Spaltennamen tolerieren)
            'DeutschLesen'     => $n($kenntnisse->DeutschLesen     ?? $kenntnisse->deutsch_lesen     ?? ''),
            'DeutschHoeren'    => $n($kenntnisse->DeutschHoeren    ?? $kenntnisse->deutsch_hoeren    ?? ''),
            'DeutschSchreiben' => $n($kenntnisse->DeutschSchreiben ?? $kenntnisse->deutsch_schreiben ?? ''),
            'DeutschSprechen'  => $n($kenntnisse->DeutschSprechen  ?? $kenntnisse->deutsch_sprechen  ?? ''),
            'EnglischNiveau'   => $n($kenntnisse->EnglischNiveau   ?? $kenntnisse->englisch          ?? ''),
            'MathematikNiveau' => $n($kenntnisse->MathematikNiveau ?? $kenntnisse->mathematik        ?? ''),

            // Praktikum – letztes + Summe (robust)
            'PraktikumFirmaLetztes'   => $n($letztesPraktikum->firma   ?? $letztesPraktikum->Firma   ?? ''),
            'PraktikumBereichLetztes' => $n($letztesPraktikum->bereich ?? $letztesPraktikum->Bereich ?? ''),
            'PraktikumVonLetztes'     => $fmtDate($prakVon),
            'PraktikumBisLetztes'     => $fmtDate($prakBis),
            'PraktikumStundenLetztes' => $prakStd !== null ? number_format((float)$prakStd, 2, ',', '.') : '',
            'PraktikumStundenSumme'   => number_format($praktikumStundenSumme, 2, ',', '.'),

            // Tabellen als HTML für Vorlagen
            'PraktikaTabelleHtml'     => $this->renderPraktikaTable($teilnehmer),
            'KenntnisseTabelleHtml'   => $this->renderKenntnisseTable($teilnehmer),
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

        // Primär: deine realen Spalten; Fallback: alte Varianten
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
            // Zeitraum formatieren (robust)
            $vonVal = $colVon ? ($r->$colVon ?? null) : null;
            $bisVal = $colBis ? ($r->$colBis ?? null) : null;

            $fmt = function ($v) {
                if (!$v) return '';
                try { return Carbon::parse($v)->format('d.m.Y'); }
                catch (\Throwable $e) { return (string)$v; }
            };

            $zeit = trim(($fmt($vonVal) ?: '').' – '.($fmt($bisVal) ?: ''));

            // Stundenzahl (numeric & hübsch formatiert)
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
     * (Optional) Kenntnisse als HTML-Tabelle (falls Spalten vorhanden).
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

    /**
     * Anrede aus Geschlecht (kurz).
     */
    private function anredeAusTeilnehmer(?Teilnehmer $tn): string
    {
        $g = mb_strtolower(trim((string)($tn?->Geschlecht ?? '')));
        if ($g === 'frau') return 'Frau';
        if ($g === 'mann' || $g === 'herr') return 'Herr';
        return '';
    }

    /**
     * Wrapper für Druck/PDF.
     */
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

    /**
     * Bild als data: URI einbetten (falls gewünscht).
     */
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
}
