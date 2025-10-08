@extends('layouts.app')

@section('title', 'Teilnehmer • '.$teilnehmer->Vorname.' '.$teilnehmer->Nachname)

@section('content')
@php
    $tnId = $teilnehmer->getKey();
    $fmt = function ($val) {
        if (!$val) return '—';
        try { return \Illuminate\Support\Carbon::parse($val)->format('d.m.Y'); }
        catch (\Throwable $e) { return '—'; }
    };
    // 0/1, "0"/"1", true/false -> "Ja"/"Nein"; null/"" -> "—"
    $yesNo = function ($v) {
        return is_null($v) || $v === '' ? '—' : ((int) $v === 1 ? 'Ja' : 'Nein');
    };
@endphp

{{-- Kopf --}}
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
  <div>
    <h1 class="text-2xl font-bold">
      {{ $teilnehmer->Vorname }} {{ $teilnehmer->Nachname }}
    </h1>
    <p class="text-sm text-gray-500">
      Teilnehmer • ID #{{ $tnId }}
      @if(!empty($teilnehmer->Geburtsdatum)) · geb. {{ $fmt($teilnehmer->Geburtsdatum) }} @endif
    </p>
  </div>

  <div class="flex flex-wrap gap-2">
    <a href="{{ route('teilnehmer.index') }}" class="px-3 py-2 border rounded-lg hover:bg-gray-50">Zurück</a>
    <a href="{{ route('teilnehmer.edit', $tnId) }}" class="px-3 py-2 border rounded-lg hover:bg-gray-50">Bearbeiten</a>

    @if (Route::has('checkliste.edit') && $teilnehmer->checkliste)
      <a href="{{ route('checkliste.edit', ['teilnehmer' => $tnId]) }}" class="px-3 py-2 border rounded-lg hover:bg-gray-50">
        Checkliste bearbeiten
      </a>
    @endif
  </div>
</div>

{{-- KPI-Karten --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  <div class="bg-white rounded-xl shadow-sm p-4">
    <div class="text-sm text-gray-500">Dokumente</div>
    <div class="text-2xl font-semibold">{{ $anzahlDokumente }}</div>
  </div>
  <div class="bg-white rounded-xl shadow-sm p-4">
    <div class="text-sm text-gray-500">Beratungen gesamt</div>
    <div class="text-2xl font-semibold">{{ $anzahlBeratungen }}</div>
  </div>
  <div class="bg-white rounded-xl shadow-sm p-4">
    <div class="text-sm text-gray-500">Fehlminuten ({{ \Illuminate\Support\Str::of($monat)->replace('-', '/') }})</div>
    <div class="text-2xl font-semibold">{{ $fehlminutenSumme }}</div>
  </div>
  <div class="bg-white rounded-xl shadow-sm p-4">
    <div class="text-sm text-gray-500">Praktikumsstunden (Summe)</div>
    <div class="text-2xl font-semibold">{{ number_format($praktikaStundenSumme, 1, ',', '.') }}</div>
  </div>
</div>

{{-- 2-Spalten-Layout --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

  {{-- Linke Spalte --}}
  <div class="lg:col-span-7 space-y-6">

    {{-- Stammdaten --}}
    <section class="bg-white rounded-xl shadow-sm p-5">
      <h2 class="text-lg font-semibold mb-3">Stammdaten</h2>

      <div class="grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
        <div><span class="text-gray-500">Nachname</span><div>{{ $teilnehmer->Nachname ?: '—' }}</div></div>
        <div><span class="text-gray-500">Vorname</span><div>{{ $teilnehmer->Vorname ?: '—' }}</div></div>
        <div><span class="text-gray-500">Geschlecht</span><div>{{ $teilnehmer->Geschlecht ?: '—' }}</div></div>
        <div><span class="text-gray-500">SVN</span><div>{{ $teilnehmer->SVN ?: '—' }}</div></div>

        <div class="sm:col-span-2">
          <span class="text-gray-500">Adresse</span>
          <div>
            {{ $teilnehmer->Strasse ?: '—' }} {{ $teilnehmer->Hausnummer ?: '' }}<br>
            {{ $teilnehmer->PLZ ?: '' }} {{ $teilnehmer->Wohnort ?: '—' }}<br>
            {{ $teilnehmer->Land ?: '—' }}
          </div>
        </div>

        <div>
          <span class="text-gray-500">E-Mail</span>
          <div>
            @if(!empty($teilnehmer->Email))
              <a href="mailto:{{ $teilnehmer->Email }}" class="text-blue-600 hover:underline">{{ $teilnehmer->Email }}</a>
            @else
              —
            @endif
          </div>
        </div>

        <div><span class="text-gray-500">Telefon</span><div>{{ $teilnehmer->Telefon ?? $teilnehmer->Telefonnummer ?? '—' }}</div></div>
        <div><span class="text-gray-500">Geburtsdatum</span><div>{{ $fmt($teilnehmer->Geburtsdatum) }}</div></div>
        <div><span class="text-gray-500">Geburtsland</span><div>{{ $teilnehmer->Geburtsland ?: '—' }}</div></div>
        <div><span class="text-gray-500">Staatszugehörigkeit</span><div>{{ $teilnehmer->Staatszugehoerigkeit ?? $teilnehmer->Staatszugehörigkeit ?? '—' }}</div></div>
        <div><span class="text-gray-500">Kategorie</span><div>{{ $teilnehmer->Staatszugehörigkeit_Kategorie ?? '—' }}</div></div>
        <div><span class="text-gray-500">Aufenthaltsstatus</span><div>{{ $teilnehmer->Aufenthaltsstatus ?: '—' }}</div></div>

        <div class="sm:col-span-2">
          <span class="text-gray-500">Gruppe</span>
          <div>
            @if($teilnehmer->gruppe_id && $teilnehmer->gruppe)
              {{ $teilnehmer->gruppe->name }} (ID {{ $teilnehmer->gruppe->gruppe_id }})
            @elseif(!empty($teilnehmer->gruppe_id))
              Gruppe #{{ $teilnehmer->gruppe_id }}
            @else
              —
            @endif
          </div>
        </div>
      </div>
    </section>

    {{-- Soziale Merkmale / Unterlagen & Ziele / Sonstiges --}}
    <section class="grid md:grid-cols-3 gap-6">
      <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-semibold mb-3">Soziale Merkmale</h3>
        <dl class="text-sm space-y-2">
          <div><dt class="text-gray-500">Minderheit</dt><dd>{{ $yesNo($teilnehmer->Minderheit) }}</dd></div>
          <div><dt class="text-gray-500">Behinderung</dt><dd>{{ $yesNo($teilnehmer->Behinderung) }}</dd></div>
          <div><dt class="text-gray-500">Obdachlos</dt><dd>{{ $yesNo($teilnehmer->Obdachlos) }}</dd></div>
          <div><dt class="text-gray-500">Ländliche Gebiete</dt><dd>{{ $yesNo($teilnehmer->Laendliche_Gebiete ?? $teilnehmer->LaendlicheGebiete ?? null) }}</dd></div>
          <div><dt class="text-gray-500">Eltern im Ausland geboren</dt><dd>{{ $yesNo($teilnehmer->Eltern_im_Ausland ?? $teilnehmer->ElternImAuslandGeboren ?? null) }}</dd></div>
          <div><dt class="text-gray-500">Armutsbetroffen</dt><dd>{{ $yesNo($teilnehmer->Armutsbetroffen) }}</dd></div>
          <div><dt class="text-gray-500">Armutsgefährdet</dt><dd>{{ $yesNo($teilnehmer->Armutsgefährdet) }}</dd></div>
          <div><dt class="text-gray-500">Bildungshintergrund</dt><dd>{{ $teilnehmer->ISCED ?? $teilnehmer->Bildungshintergrund ?? '—' }}</dd></div>
        </dl>
      </div>

      <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-semibold mb-3">Unterlagen & Ziele</h3>
        <dl class="text-sm space-y-2">
          <div><dt class="text-gray-500">IDEA Stammdatenblatt</dt><dd>{{ $yesNo($teilnehmer->IDEA_Stammdatenblatt) }}</dd></div>
          <div><dt class="text-gray-500">IDEA Dokumente</dt><dd>{{ $yesNo($teilnehmer->IDEA_Dokumente) }}</dd></div>
          <div><dt class="text-gray-500">PAZ</dt><dd>{{ $teilnehmer->PAZ ?? '—' }}</dd></div>
        </dl>
      </div>

      <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-semibold mb-3">Sonstiges</h3>
        <dl class="text-sm space-y-2">
          <div><dt class="text-gray-500">Berufserfahrung als</dt><dd>{{ $teilnehmer->Berufserfahrung_als ?? '—' }}</dd></div>
          <div><dt class="text-gray-500">Bereich</dt><dd>{{ $teilnehmer->Bereich_berufserfahrung ?? '—' }}</dd></div>
          <div><dt class="text-gray-500">Land</dt><dd>{{ $teilnehmer->Land_berufserfahrung ?? '—' }}</dd></div>
          <div><dt class="text-gray-500">Firma</dt><dd>{{ $teilnehmer->Firma_berufserfahrung ?? '—' }}</dd></div>
          <div><dt class="text-gray-500">Beginn</dt><dd>{{ $teilnehmer->Zeit_berufserfahrung ?? '—' }}</dd></div>
          <div><dt class="text-gray-500">Stundenumfang</dt><dd>{{ $teilnehmer->Stundenumfang_berufserfahrung ?? '—' }}</dd></div>
          <div><dt class="text-gray-500">Zertifikate</dt><dd>{{ $teilnehmer->Zertifikate ?? '—' }}</dd></div>
          <div><dt class="text-gray-500">Clearing Gruppe</dt><dd>{{ $yesNo($teilnehmer->Clearing_gruppe) }}</dd></div>
          <div><dt class="text-gray-500">Berufswunsch</dt><dd>{{ $teilnehmer->Berufswunsch ?? '—' }}</dd></div>
          <div><dt class="text-gray-500">Branche</dt><dd>{{ $teilnehmer->Berufswunsch_branche ?? '—' }}</dd></div>
          <div><dt class="text-gray-500">Branche 2</dt><dd>{{ $teilnehmer->Berufswunsch_branche2 ?? '—' }}</dd></div>
          <div><dt class="text-gray-500">Unterrichtseinheiten</dt><dd>{{ $teilnehmer->Unterrichtseinheiten ?? '—' }}</dd></div>
          <div><dt class="text-gray-500">Anmerkung</dt><dd>{{ $teilnehmer->Anmerkung ?? '—' }}</dd></div>
        </dl>
      </div>
    </section>

    {{-- Beratungen --}}
    <section class="rounded-2xl border bg-white shadow">
      <div class="px-5 py-4 border-b flex items-center justify-between">
        <h3 class="text-base font-semibold">Beratungen</h3>
        @can('beratung.manage')
          <a href="{{ route('beratungen.index', ['q' => $teilnehmer->Email ?? '']) }}"
             class="text-sm px-3 py-1 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
            Neue Beratung
          </a>
        @endcan
      </div>

      <div class="p-5">
        <div class="overflow-x-auto">
          <table class="w-full table-fixed border-collapse">
            <thead>
              <tr class="text-left text-sm text-gray-600">
                <th class="w-28">Datum</th>
                <th class="w-56">Art</th>
                <th class="w-56">Thema</th>
                <th class="w-40">Berater</th>
                <th class="w-24 text-right">Dauer (h)</th>
                <th class="w-32">Aktionen</th>
              </tr>
            </thead>
            <tbody class="align-top text-sm">
              @foreach ($teilnehmer->beratungen as $b)
                @php
                  $bid = $b->beratung_id ?? $b->id ?? $b->getKey();
                  $dateStr   = $b->datum ? \Illuminate\Support\Carbon::parse($b->datum)->format('d.m.Y') : '—';
                  $artStr    = optional($b->art)->Bezeichnung ?? optional($b->art)->bezeichnung ?? '—';
                  $themaStr  = optional($b->thema)->Bezeichnung ?? optional($b->thema)->bezeichnung ?? ($b->thema ?? '—');
                  $beraterStr = trim((optional($b->mitarbeiter)->Nachname ?? '').' '.(optional($b->mitarbeiter)->Vorname ?? ''));
                  if ($beraterStr === '') $beraterStr = '—';
                  $dauerRaw = $b->dauer_h ?? $b->dauer_stunden ?? null;
                  $dauerStr = is_null($dauerRaw) ? '—' : number_format((float)$dauerRaw, 2, ',', '.');
                @endphp

                <tr class="border-b last:border-b-0">
                  <td class="py-2 pr-3 align-top">{{ $dateStr }}</td>
                  <td class="py-2 pr-3 align-top">{{ $artStr }}</td>
                  <td class="py-2 pr-3 align-top">{{ $themaStr }}</td>
                  <td class="py-2 pr-3 align-top">{{ $beraterStr }}</td>
                  <td class="py-2 pr-3 align-top text-right">{{ $dauerStr }}</td>
                  <td class="py-2 align-top">
                    <div class="flex gap-2">
                      <button type="button"
                              class="px-3 py-1 rounded border shadow text-sm"
                              onclick="openNotizenModal(@js($b->notizen))">
                        Notizen
                      </button>

                      @can('beratung.manage')
                        <a href="{{ route('beratungen.edit', $bid) }}"
                           class="px-3 py-1 rounded border shadow text-sm">
                          Bearbeiten
                        </a>
                        <form method="POST" action="{{ route('beratungen.destroy', $bid) }}"
                              onsubmit="return confirm('Diese Beratung wirklich löschen?');">
                          @csrf @method('DELETE')
                          <button class="px-3 py-1 rounded border shadow text-sm text-red-600">
                            Löschen
                          </button>
                        </form>
                      @endcan
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </section>

    {{-- Kompetenzstand (Eintritt) --}}
    <h3 class="text-lg font-semibold mt-6 mb-2">Kompetenzstand (Eintritt)</h3>
    <table class="w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-2 text-left">Kompetenz</th>
          <th class="px-3 py-2 text-left">Niveau</th>
          <th class="px-3 py-2 text-left">Datum</th>
          <th class="px-3 py-2 text-left">Bemerkung</th>
        </tr>
      </thead>
      <tbody>
        @forelse($eintrittList as $r)
          <tr class="border-t">
            <td class="px-3 py-2"><span class="font-semibold">{{ $r->kcode }}</span> — {{ $r->kbez }}</td>
            <td class="px-3 py-2">{{ $r->ncode ? ($r->ncode.' — '.$r->nlabel) : '—' }}</td>
            <td class="px-3 py-2">{{ $r->datum ? \Illuminate\Support\Carbon::parse($r->datum)->format('d.m.Y') : '—' }}</td>
            <td class="px-3 py-2">{{ $r->bemerkung ?: '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="4" class="px-3 py-3 text-gray-500">Keine Einträge.</td></tr>
        @endforelse
      </tbody>
    </table>

    {{-- Kompetenzstand (Austritt) --}}
    <h3 class="text-lg font-semibold mt-6 mb-2">Kompetenzstand (Austritt)</h3>
    <table class="w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-2 text-left">Kompetenz</th>
          <th class="px-3 py-2 text-left">Niveau</th>
          <th class="px-3 py-2 text-left">Datum</th>
          <th class="px-3 py-2 text-left">Bemerkung</th>
        </tr>
      </thead>
      <tbody>
        @forelse($austrittList as $r)
          <tr class="border-t">
            <td class="px-3 py-2"><span class="font-semibold">{{ $r->kcode }}</span> — {{ $r->kbez }}</td>
            <td class="px-3 py-2">{{ $r->ncode ? ($r->ncode.' — '.$r->nlabel) : '—' }}</td>
            <td class="px-3 py-2">{{ $r->datum ? \Illuminate\Support\Carbon::parse($r->datum)->format('d.m.Y') : '—' }}</td>
            <td class="px-3 py-2">{{ $r->bemerkung ?: '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="4" class="px-3 py-3 text-gray-500">Keine Einträge.</td></tr>
        @endforelse
      </tbody>
    </table>

    {{-- Anwesenheit mit Monat-Navigation --}}
    <section class="bg-white rounded-xl shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold">Anwesenheit</h2>
        <div class="flex items-center gap-2 text-sm">
          <a class="px-2 py-1 rounded border hover:bg-gray-50"
             href="{{ route('teilnehmer.show', ['teilnehmer'=>$tnId, 'monat'=>$prevMonat]) }}">« {{ \Illuminate\Support\Str::of($prevMonat)->replace('-', '/') }}</a>
          <span class="px-2 py-1">{{ \Illuminate\Support\Str::of($monat)->replace('-', '/') }}</span>
          <a class="px-2 py-1 rounded border hover:bg-gray-50"
             href="{{ route('teilnehmer.show', ['teilnehmer'=>$tnId, 'monat'=>$nextMonat]) }}">{{ \Illuminate\Support\Str::of($nextMonat)->replace('-', '/') }} »</a>
        </div>
      </div>

      @if($anwesenheiten->count())
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="text-left px-2 py-1">Datum</th>
                <th class="text-left px-2 py-1">Von</th>
                <th class="text-left px-2 py-1">Bis</th>
                <th class="text-left px-2 py-1">Fehlminuten</th>
                <th class="text-left px-2 py-1">Status</th>
              </tr>
            </thead>
            <tbody>
              @foreach($anwesenheiten as $a)
                <tr class="border-t">
                  <td class="px-2 py-1">{{ $fmt($a->datum) }}</td>
                  <td class="px-2 py-1">{{ $a->von ?? '—' }}</td>
                  <td class="px-2 py-1">{{ $a->bis ?? '—' }}</td>
                  <td class="px-2 py-1">{{ $a->fehlminuten ?? 0 }}</td>
                  <td class="px-2 py-1">{{ $a->status ?? '—' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <p class="text-sm text-gray-500">Keine Anwesenheit im gewählten Monat.</p>
      @endif
    </section>

    {{-- Praktika --}}
    <section id="praktika" class="bg-white rounded-xl shadow-sm p-5">
      <h2 class="text-lg font-semibold mb-3">Praktika</h2>
      @if($teilnehmer->praktika->count())
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="text-left px-2 py-1">Firma</th>
                <th class="text-left px-2 py-1">Beginn</th>
                <th class="text-left px-2 py-1">Ende</th>
                <th class="text-left px-2 py-1">Std.</th>
                <th class="text-left px-2 py-1">Anmerkung</th>
              </tr>
            </thead>
            <tbody>
              @foreach($teilnehmer->praktika as $p)
                <tr class="border-t">
                  <td class="px-2 py-1">{{ $p->firma ?? '—' }}</td>
                  <td class="px-2 py-1">{{ $fmt($p->beginn ?? ($p->beginn_datum ?? $p->von ?? null)) }}</td>
                  <td class="px-2 py-1">{{ $fmt($p->ende ?? ($p->ende_datum ?? $p->bis ?? null)) }}</td>
                  <td class="px-2 py-1">{{ $p->stunden_ausmass ?? '—' }}</td>
                  <td class="px-2 py-1">{{ $p->anmerkung ?? '—' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <p class="text-sm text-gray-500">Keine Praktika erfasst.</p>
      @endif
    </section>

    {{-- Dokumente --}}
    <section class="bg-white rounded-xl shadow-sm p-5">
      <h2 class="text-lg font-semibold mb-3">Dokumente</h2>
      @if($teilnehmer->dokumente->count())
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50">
              <tr>
                <th class="text-left px-2 py-1">Hochgeladen am</th>
                <th class="text-left px-2 py-1">Typ</th>
                <th class="text-left px-2 py-1">Originalname</th>
                <th class="text-left px-2 py-1">Aktion</th>
              </tr>
            </thead>
            <tbody>
              @foreach($teilnehmer->dokumente as $d)
                <tr class="border-t">
                  <td class="px-2 py-1">{{ $fmt($d->hochgeladen_am ?? $d->created_at) }}</td>
                  <td class="px-2 py-1">{{ $d->typ ?? '—' }}</td>
                  <td class="px-2 py-1">{{ $d->original_name ?? '—' }}</td>
                  <td class="px-2 py-1">
                    @if(Route::has('teilnehmer_dokumente.download'))
                      <a class="text-blue-600 hover:underline"
                         href="{{ route('teilnehmer_dokumente.download', $d->getKey()) }}">Download</a>
                    @elseif(Route::has('teilnehmer_dokumente.show'))
                      <a class="text-blue-600 hover:underline"
                         href="{{ route('teilnehmer_dokumente.show', $d->getKey()) }}">Anzeigen</a>
                    @else
                      —
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <p class="text-sm text-gray-500">Keine Dokumente hochgeladen.</p>
      @endif
    </section>

  </div>

  {{-- Rechte Spalte --}}
  <aside class="lg:col-span-5 space-y-6">

    {{-- Checkliste kompakt --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
      <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold">Checkliste</h2>
        @if (Route::has('checkliste.edit'))
          <a href="{{ route('checkliste.edit', ['teilnehmer'=>$tnId]) }}" class="text-sm px-3 py-1 border rounded hover:bg-gray-50">Bearbeiten</a>
        @endif
      </div>

      @if($teilnehmer->checkliste)
        <dl class="text-sm grid grid-cols-2 gap-x-6 gap-y-2">
          <div><dt class="text-gray-500">AMS Bericht</dt><dd>{{ $teilnehmer->checkliste->ams_bericht ?? '—' }}</dd></div>
          <div><dt class="text-gray-500">AMS Lebenslauf</dt><dd>{{ $teilnehmer->checkliste->ams_lebenslauf ?? '—' }}</dd></div>
          {{-- weitere Felder hier --}}
        </dl>
      @else
        <p class="text-gray-500 text-sm">Keine Checkliste vorhanden.</p>
      @endif
    </div>

    {{-- Schnellaktionen --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
      <h2 class="text-lg font-semibold mb-3">Aktionen</h2>
      <div class="flex flex-wrap gap-2">
        <a href="{{ route('teilnehmer.edit', $tnId) }}" class="px-3 py-2 border rounded-lg hover:bg-gray-50">Bearbeiten</a>
        @php $firstDocTpl = $docs->first(); @endphp
        @if($firstDocTpl && Route::has('dokumente.prepare'))
          <a href="{{ route('dokumente.prepare', ['teilnehmer'=>$tnId, 'dokument'=>$firstDocTpl->getKey()]) }}"
             class="px-3 py-2 border rounded-lg hover:bg-gray-50">Drucken</a>
        @endif
      </div>
    </div>

    {{-- Audit --}}
    <div class="bg-white rounded-xl shadow-sm p-5">
      @include('partials.audit', ['model' => $teilnehmer])
    </div>
  </aside>
</div>

{{-- Notizen Modal (JS im Content halten) --}}
<div id="beratungNotizenModal" class="hidden fixed inset-0 z-50 items-center justify-center">
  <div class="absolute inset-0 bg-black/40" onclick="closeNotizenModal()"></div>
  <div class="relative bg-white rounded-2xl shadow p-6 w-full max-w-xl mx-auto">
    <h4 class="text-base font-semibold mb-3">Notizen</h4>
    <div class="text-sm text-gray-800 whitespace-pre-wrap" data-content></div>
    <div class="mt-5 flex justify-end">
      <button class="px-3 py-2 rounded border shadow text-sm" onclick="closeNotizenModal()">Schließen</button>
    </div>
  </div>
</div>

<script>
function openNotizenModal(text) {
  const m = document.getElementById('beratungNotizenModal');
  m.querySelector('[data-content]').textContent = (text && String(text).trim()) ? text : '—';
  m.classList.remove('hidden'); m.classList.add('flex');
}
function closeNotizenModal() {
  const m = document.getElementById('beratungNotizenModal');
  m.classList.add('hidden'); m.classList.remove('flex');
}
</script>
@endsection
