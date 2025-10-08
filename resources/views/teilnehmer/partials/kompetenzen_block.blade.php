@php
  // Fallback, falls keine Liste gereicht wurde
  $kompetenzen = $kompetenzen ?? \App\Models\Kompetenz::orderBy('code')->get();

  // Teilnehmer-Stände in zwei Maps aufteilen (key = kompetenz_id)
  $st = collect($teilnehmer->kompetenzstaende ?? []);
  $mapEin = $st->filter(fn($s) => ($s->zeitpunkt_norm ?? strtolower(trim($s->zeitpunkt))) === 'eintritt')
               ->keyBy('kompetenz_id');
  $mapAus = $st->filter(fn($s) => ($s->zeitpunkt_norm ?? strtolower(trim($s->zeitpunkt))) === 'austritt')
               ->keyBy('kompetenz_id');
@endphp

{{-- =========================
     EINTRITT
========================= --}}
<h3 class="text-lg font-semibold">Kompetenzstand (Eintritt)</h3>
<table class="w-full text-sm">
  <thead>
    <tr>
      <th class="text-left px-2 py-1">Kompetenz</th>
      <th class="text-left px-2 py-1">Niveau</th>
      <th class="text-left px-2 py-1">Datum</th>
      <th class="text-left px-2 py-1">Bemerkung</th>
    </tr>
  </thead>
  <tbody>
  @php
    // Wenn eine vorgebaute Liste existiert, nutzen – sonst aus Map rendern
    $rowsEin = collect(isset($eintrittList) ? $eintrittList : $kompetenzen->map(function($k) use ($mapEin) {
      $s = $mapEin->get($k->kompetenz_id);
      return (object)[
        'kompetenz_id' => $k->kompetenz_id,
        'kcode'        => $k->code,
        'kbez'         => $k->bezeichnung,
        'ncode'        => $s?->niveau?->code,
        'nlabel'       => $s?->niveau?->label,
        'datum'        => $s?->datum,
        'bemerkung'    => $s?->bemerkung,
      ];
    }));
  @endphp

  @forelse($rowsEin as $row)
    @php
      // Versuche, schöne Bezeichnungen anzuzeigen (egal ob aus Join-Liste oder Map)
      $kcode = $row->kcode    ?? ($row->kompetenz?->code ?? null);
      $kbez  = $row->kbez     ?? ($row->kompetenz?->bezeichnung ?? null);
      $ncode = $row->ncode    ?? ($row->niveau?->code ?? null);
      $nlabel= $row->nlabel   ?? ($row->niveau?->label ?? null);
      $date  = $row->datum    ?? null;
      $note  = $row->bemerkung ?? null;
    @endphp
    <tr class="border-t">
      <td class="px-2 py-1">
        <span class="font-semibold">{{ $kcode ?? '—' }}</span>
        <span class="text-gray-700">{{ $kbez ? ' — '.$kbez : '' }}</span>
      </td>
      <td class="px-2 py-1">
        {{ $ncode ? ($nlabel ? "$ncode — $nlabel" : $ncode) : '—' }}
      </td>
      <td class="px-2 py-1">
        {{ $date ? \Carbon\Carbon::parse($date)->format('d.m.Y') : '—' }}
      </td>
      <td class="px-2 py-1">{{ $note ?: '—' }}</td>
    </tr>
  @empty
    <tr><td colspan="4" class="px-2 py-2 text-gray-500">Keine Einträge.</td></tr>
  @endforelse
  </tbody>
</table>

{{-- =========================
     AUSTRITT
========================= --}}
<h3 class="text-lg font-semibold mt-6">Kompetenzstand (Austritt)</h3>
<table class="w-full text-sm">
  <thead>
    <tr>
      <th class="text-left px-2 py-1">Kompetenz</th>
      <th class="text-left px-2 py-1">Niveau</th>
      <th class="text-left px-2 py-1">Datum</th>
      <th class="text-left px-2 py-1">Bemerkung</th>
    </tr>
  </thead>
  <tbody>
  @php
    $rowsAus = collect(isset($austrittList) ? $austrittList : $kompetenzen->map(function($k) use ($mapAus) {
      $s = $mapAus->get($k->kompetenz_id);
      return (object)[
        'kompetenz_id' => $k->kompetenz_id,
        'kcode'        => $k->code,
        'kbez'         => $k->bezeichnung,
        'ncode'        => $s?->niveau?->code,
        'nlabel'       => $s?->niveau?->label,
        'datum'        => $s?->datum,
        'bemerkung'    => $s?->bemerkung,
      ];
    }));
  @endphp

  @forelse($rowsAus as $row)
    @php
      $kcode = $row->kcode    ?? ($row->kompetenz?->code ?? null);
      $kbez  = $row->kbez     ?? ($row->kompetenz?->bezeichnung ?? null);
      $ncode = $row->ncode    ?? ($row->niveau?->code ?? null);
      $nlabel= $row->nlabel   ?? ($row->niveau?->label ?? null);
      $date  = $row->datum    ?? null;
      $note  = $row->bemerkung ?? null;
    @endphp
    <tr class="border-t">
      <td class="px-2 py-1">
        <span class="font-semibold">{{ $kcode ?? '—' }}</span>
        <span class="text-gray-700">{{ $kbez ? ' — '.$kbez : '' }}</span>
      </td>
      <td class="px-2 py-1">
        {{ $ncode ? ($nlabel ? "$ncode — $nlabel" : $ncode) : '—' }}
      </td>
      <td class="px-2 py-1">
        {{ $date ? \Carbon\Carbon::parse($date)->format('d.m.Y') : '—' }}
      </td>
      <td class="px-2 py-1">{{ $note ?: '—' }}</td>
    </tr>
  @empty
    <tr><td colspan="4" class="px-2 py-2 text-gray-500">Keine Einträge.</td></tr>
  @endforelse
  </tbody>
</table>
