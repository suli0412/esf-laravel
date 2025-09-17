{{-- Stammdaten + Soziale Merkmale + Unterlagen & Ziele + Beruf + Anmerkung + Checkliste + Kompetenzstände --}}

{{-- Stammdaten --}}
<div class="mb-6">
  <h3 class="text-lg font-semibold mb-3">Stammdaten</h3>
  <dl class="grid grid-cols-4 gap-4 text-sm">
    <dt class="text-gray-500">Nachname</dt><dd>{{ $teilnehmer->Nachname }}</dd>
    <dt class="text-gray-500">Vorname</dt><dd>{{ $teilnehmer->Vorname }}</dd>
    <dt class="text-gray-500">Geschlecht</dt><dd>{{ $teilnehmer->Geschlecht }}</dd>
    <dt class="text-gray-500">SVN</dt><dd>{{ $teilnehmer->SVN }}</dd>

    <dt class="text-gray-500">Straße</dt><dd>{{ $teilnehmer->Strasse }}</dd>
    <dt class="text-gray-500">Hausnummer</dt><dd>{{ $teilnehmer->Hausnummer }}</dd>
    <dt class="text-gray-500">PLZ</dt><dd>{{ $teilnehmer->PLZ }}</dd>
    <dt class="text-gray-500">Wohnort</dt><dd>{{ $teilnehmer->Wohnort }}</dd>

    <dt class="text-gray-500">Land</dt><dd>{{ $teilnehmer->Land }}</dd>
    <dt class="text-gray-500">Email</dt><dd>{{ $teilnehmer->Email }}</dd>
    <dt class="text-gray-500">Telefon</dt><dd>{{ $teilnehmer->Telefonnummer }}</dd>
    <dt class="text-gray-500">Geburtsdatum</dt>
    <dd>{{ $teilnehmer->Geburtsdatum ? \Illuminate\Support\Carbon::parse($teilnehmer->Geburtsdatum)->format('d.m.Y') : '' }}</dd>

    <dt class="text-gray-500">Geburtsland</dt><dd>{{ $teilnehmer->Geburtsland }}</dd>
    <dt class="text-gray-500">Staatszugehörigkeit</dt><dd>{{ $teilnehmer->getAttribute('Staatszugehörigkeit') }}</dd>
    <dt class="text-gray-500">Staatszugehörigkeit Kategorie</dt><dd>{{ $teilnehmer->getAttribute('Staatszugehörigkeit_Kategorie') }}</dd>
    <dt class="text-gray-500">Aufenthaltsstatus</dt><dd>{{ $teilnehmer->Aufenthaltsstatus }}</dd>
  </dl>
</div>

{{-- Soziale Merkmale --}}
<div class="mb-6">
  <h3 class="text-lg font-semibold mb-3">Soziale Merkmale</h3>
  <dl class="grid grid-cols-4 gap-4 text-sm">
    <dt class="text-gray-500">Minderheit</dt><dd>{{ $teilnehmer->Minderheit }}</dd>
    <dt class="text-gray-500">Behinderung</dt><dd>{{ $teilnehmer->Behinderung }}</dd>
    <dt class="text-gray-500">Obdachlos</dt><dd>{{ $teilnehmer->Obdachlos }}</dd>
    <dt class="text-gray-500">Ländliche Gebiete</dt><dd>{{ $teilnehmer->getAttribute('LändlicheGebiete') }}</dd>

    <dt class="text-gray-500">Eltern im Ausland geboren</dt><dd>{{ $teilnehmer->getAttribute('ElternImAuslandGeboren') }}</dd>
    <dt class="text-gray-500">Armutsbetroffen</dt><dd>{{ $teilnehmer->Armutsbetroffen }}</dd>
    <dt class="text-gray-500">Armutsgefährdet</dt><dd>{{ $teilnehmer->Armutsgefährdet }}</dd>
    <dt class="text-gray-500">Bildungshintergrund</dt><dd>{{ $teilnehmer->Bildungshintergrund }}</dd>
  </dl>
</div>

{{-- Unterlagen & Ziele --}}
<div class="mb-6">
  <h3 class="text-lg font-semibold mb-3">Unterlagen & Ziele</h3>
  <dl class="grid grid-cols-4 gap-4 text-sm">
    <dt class="text-gray-500">IDEA Stammdatenblatt</dt><dd>{{ $teilnehmer->IDEA_Stammdatenblatt ? 'Ja' : 'Nein' }}</dd>
    <dt class="text-gray-500">IDEA Dokumente</dt><dd>{{ $teilnehmer->IDEA_Dokumente ? 'Ja' : 'Nein' }}</dd>
    <dt class="text-gray-500">PAZ</dt><dd>{{ $teilnehmer->PAZ }}</dd>
  </dl>
</div>

{{-- Beruf --}}
<div class="mb-6">
  <h3 class="text-lg font-semibold mb-3">Beruf</h3>
  <dl class="grid grid-cols-4 gap-4 text-sm">
    <dt class="text-gray-500">Berufserfahrung als</dt><dd>{{ $teilnehmer->Berufserfahrung_als }}</dd>
    <dt class="text-gray-500">Bereich Berufserfahrung</dt><dd>{{ $teilnehmer->Bereich_berufserfahrung }}</dd>
    <dt class="text-gray-500">Land Berufserfahrung</dt><dd>{{ $teilnehmer->Land_berufserfahrung }}</dd>
    <dt class="text-gray-500">Firma Berufserfahrung</dt><dd>{{ $teilnehmer->Firma_berufserfahrung }}</dd>

    <dt class="text-gray-500">Zeit Berufserfahrung</dt><dd>{{ $teilnehmer->Zeit_berufserfahrung }}</dd>
    <dt class="text-gray-500">Stundenumfang</dt><dd>{{ $teilnehmer->Stundenumfang_berufserfahrung }}</dd>
    <dt class="text-gray-500">Zertifikate</dt><dd>{{ $teilnehmer->Zertifikate }}</dd>
    <dt class="text-gray-500">Clearing Gruppe</dt><dd>{{ $teilnehmer->Clearing_gruppe ? 'Ja' : 'Nein' }}</dd>

    <dt class="text-gray-500">Berufswunsch</dt><dd>{{ $teilnehmer->Berufswunsch }}</dd>
    <dt class="text-gray-500">Branche</dt><dd>{{ $teilnehmer->Berufswunsch_branche }}</dd>
    <dt class="text-gray-500">Branche 2</dt><dd>{{ $teilnehmer->Berufswunsch_branche2 }}</dd>
    <dt class="text-gray-500">Unterrichtseinheiten</dt><dd>{{ $teilnehmer->Unterrichtseinheiten }}</dd>
  </dl>
</div>

{{-- Anmerkung --}}
<div class="mb-6">
  <h3 class="text-lg font-semibold mb-3">Anmerkung</h3>
  <p class="whitespace-pre-line text-sm">{{ $teilnehmer->Anmerkung }}</p>
</div>

{{-- Checkliste Nermin (Anzeige) --}}
@if($teilnehmer->checkliste)
  <div class="mb-6">
    <h3 class="text-lg font-semibold mb-3">Checkliste Nermin</h3>
    <dl class="grid grid-cols-4 gap-4 text-sm">
      <dt class="text-gray-500">AMS Bericht</dt><dd>{{ $teilnehmer->checkliste->AMS_Bericht }}</dd>
      <dt class="text-gray-500">AMS Lebenslauf</dt><dd>{{ $teilnehmer->checkliste->AMS_Lebenslauf }}</dd>
      <dt class="text-gray-500">Erwerbsstatus</dt><dd>{{ $teilnehmer->checkliste->Erwerbsstatus }}</dd>
      <dt class="text-gray-500">Vorzeitiger Austritt</dt><dd>{{ $teilnehmer->checkliste->VorzeitigerAustritt }}</dd>
      <dt class="text-gray-500">IDEA</dt><dd>{{ $teilnehmer->checkliste->IDEA }}</dd>
    </dl>
  </div>
@endif

{{-- Kompetenzstand bei Eintritt / Austritt --}}
@php
  // Minimaler, sicherer Join nur zum Anzeigen:
  $kstEintritt = \Illuminate\Support\Facades\DB::table('kompetenzstand as ks')
    ->join('kompetenzen as k','k.kompetenz_id','=','ks.kompetenz_id')
    ->join('niveau as n','n.niveau_id','=','ks.niveau_id')
    ->where('ks.teilnehmer_id',$teilnehmer->Teilnehmer_id)
    ->where('ks.zeitpunkt','Eintritt')
    ->orderBy('k.code')
    ->select('k.code as kcode','k.bezeichnung as kbez','n.code as ncode','n.label as nlabel','ks.datum','ks.bemerkung')
    ->get();

  $kstAustritt = \Illuminate\Support\Facades\DB::table('kompetenzstand as ks')
    ->join('kompetenzen as k','k.kompetenz_id','=','ks.kompetenz_id')
    ->join('niveau as n','n.niveau_id','=','ks.niveau_id')
    ->where('ks.teilnehmer_id',$teilnehmer->Teilnehmer_id)
    ->where('ks.zeitpunkt','Austritt')
    ->orderBy('k.code')
    ->select('k.code as kcode','k.bezeichnung as kbez','n.code as ncode','n.label as nlabel','ks.datum','ks.bemerkung')
    ->get();
@endphp

<div class="mb-6">
  <h3 class="text-lg font-semibold mb-3">Kompetenzstand (Eintritt)</h3>
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
      @forelse($kstEintritt as $row)
        <tr class="border-t">
          <td class="px-3 py-2">{{ $row->kcode }} — {{ $row->kbez }}</td>
          <td class="px-3 py-2">{{ $row->ncode }} ({{ $row->nlabel }})</td>
          <td class="px-3 py-2">{{ $row->datum ? \Illuminate\Support\Carbon::parse($row->datum)->format('d.m.Y') : '—' }}</td>
          <td class="px-3 py-2">{{ $row->bemerkung ?? '—' }}</td>
        </tr>
      @empty
        <tr><td colspan="4" class="px-3 py-3 text-gray-500">Keine Einträge.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div>
  <h3 class="text-lg font-semibold mb-3">Kompetenzstand (Austritt)</h3>
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
      @forelse($kstAustritt as $row)
        <tr class="border-t">
          <td class="px-3 py-2">{{ $row->kcode }} — {{ $row->kbez }}</td>
          <td class="px-3 py-2">{{ $row->ncode }} ({{ $row->nlabel }})</td>
          <td class="px-3 py-2">{{ $row->datum ? \Illuminate\Support\Carbon::parse($row->datum)->format('d.m.Y') : '—' }}</td>
          <td class="px-3 py-2">{{ $row->bemerkung ?? '—' }}</td>
        </tr>
      @empty
        <tr><td colspan="4" class="px-3 py-3 text-gray-500">Keine Einträge.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
