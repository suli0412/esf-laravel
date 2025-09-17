{{-- Beratungen + Schnellerfassung --}}

{{-- Liste --}}
<div class="mb-6">
  <h3 class="font-semibold mb-3">Beratungen</h3>
  <ul class="space-y-1 text-sm">
    @foreach(($teilnehmer->beratungen ?? []) as $b)
      <li>
        {{ optional($b->datum)->format('d.m.Y') }} · {{ $b->thema?->Bezeichnung ?? $b->thema }}
        @if($b->mitarbeiter)
          — <a href="{{ route('mitarbeiter.show', $b->mitarbeiter) }}" class="text-blue-600 hover:underline">Öffnen</a>
        @endif
      </li>
    @endforeach
    @if(($teilnehmer->beratungen ?? collect())->isEmpty())
      <li class="text-gray-500">Keine Einträge.</li>
    @endif
  </ul>
</div>

{{-- Schnellerfassung --}}
<div>
  <h3 class="text-lg font-semibold mb-2">Neue Beratung (für diesen Teilnehmer)</h3>
  @include('beratungen._quick_form_individual', [
    'teilnehmer'  => $teilnehmer,
    'mitarbeiter' => null,
    'arten'       => $arten,
    'themen'      => $themen,
  ])
</div>
