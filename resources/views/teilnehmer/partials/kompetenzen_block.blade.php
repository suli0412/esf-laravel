@php
  $kompetenzen = $kompetenzen ?? \App\Models\Kompetenz::orderBy('code')->get();
@endphp

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
  @forelse($eintrittList as $row)
    @php
      $komp = $teilnehmer->kompetenzstaende
              ->firstWhere(fn($s) => $s->zeitpunkt==='Eintritt' && $s->kompetenz_id===$row->kompetenz_id);
    @endphp
    <tr class="border-t">
      <td class="px-2 py-1">{{ $komp?->kompetenz?->bezeichnung ?? $komp?->kompetenz?->code ?? $row->kompetenz_id }}</td>
      <td class="px-2 py-1">{{ $komp?->niveau?->code ?? $row->niveau_id }}</td>
      <td class="px-2 py-1">
        @if(!empty($row->datum))
          {{ \Carbon\Carbon::parse($row->datum)->format('d.m.Y') }}
        @else
          —
        @endif
      </td>
      <td class="px-2 py-1">{{ $row->bemerkung ?? '—' }}</td>
    </tr>
  @empty
    <tr><td colspan="4" class="px-2 py-2 text-gray-500">Keine Einträge.</td></tr>
  @endforelse
  </tbody>
</table>
