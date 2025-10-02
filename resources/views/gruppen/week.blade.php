@extends('layouts.app')

@section('title', 'Gruppe • '.$gruppe->name)

@section('content')
@php
    use Illuminate\Support\Carbon;

    // Falls nicht vom Controller gesetzt:
    $statusOptions = $statusOptions ?? [
        'anwesend'              => 'Anwesend',
        'anwesend_verspaetet'   => 'Anwesend (Verspätung)',
        'abwesend'              => 'Abwesend',
        'entschuldigt'          => 'Entschuldigt',
        'religiöser_feiertag'   => 'Religiöser Feiertag',
    ];

    $weekEnd    = $weekStart->copy()->addDays(6);
    $isoWeek    = $weekStart->isoWeek;
    $isoYear    = $weekStart->isoWeekYear;

    // für Vor/Zurück
    $prevW = $weekStart->copy()->subWeek()->format('o-\WW');
    $nextW = $weekStart->copy()->addWeek()->format('o-\WW');
@endphp

<div class="mb-6 flex items-center justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold">{{ $gruppe->name }}</h1>
        <p class="text-sm text-gray-500">
            KW {{ $isoWeek }} • {{ $weekStart->format('d.m.Y') }} – {{ $weekEnd->format('d.m.Y') }}
        </p>
    </div>

    <div class="flex items-center gap-2">
        <a href="{{ route('gruppen.index') }}" class="px-3 py-2 rounded-lg border hover:bg-gray-50">← Gruppen</a>
        <a href="{{ route('gruppen.show', $gruppe).'?w='.$prevW }}" class="px-3 py-2 rounded-lg border hover:bg-gray-50">Vorherige Woche</a>
        <a href="{{ route('gruppen.show', $gruppe).'?w='.$nextW }}" class="px-3 py-2 rounded-lg border hover:bg-gray-50">Nächste Woche</a>
    </div>
</div>


{{-- oben: Form-Start --}}
<form method="POST" action="{{ route('gruppen.anwesenheit.save', $gruppe) }}">
  @csrf
  <input type="hidden" name="w" value="{{ $kw }}">

  <table class="min-w-full bg-white rounded-xl shadow-sm">
    <thead>
      <tr class="bg-gray-50">
        <th class="px-3 py-2 text-left">Teilnehmer</th>
        @foreach($weekDays as $day)
          <th class="px-3 py-2 text-left">
            {{ $day->isoFormat('dd DD.MM.') }} {{-- Mo 23.09. --}}
          </th>
        @endforeach
        <th class="px-3 py-2 text-left">Verspätung (Woche)</th>
        <th class="px-3 py-2 text-left">KW-Summe (Min.)</th>
      </tr>
    </thead>
    <tbody>
      @foreach($members as $m)
        @php
          $tid   = $m->Teilnehmer_id;
          $kwSum = (int) ($sumFehl[$tid] ?? 0);
        @endphp
        <tr class="border-t">
          <td class="px-3 py-2 font-medium">{{ $m->Nachname }}, {{ $m->Vorname }}</td>

          {{-- Status je Tag --}}
          @foreach($weekDays as $day)
            @php
              $dstr = $day->toDateString();
              $current = $att[$tid][$dstr]['status'] ?? 'anwesend';
            @endphp
            <td class="px-2 py-2">
              <select
                name="anwesenheit[{{ $tid }}][{{ $dstr }}]"
                class="w-full border rounded-md px-2 py-1 text-sm"
              >
                    <option value="anwesend">anwesend</option>
                    <option value="anwesend_verspaetet">anwesend (verspätet)</option>
                    <option value="abwesend">abwesend</option>
                    <option value="entschuldigt">entschuldigt</option>
                    <option value="religioeser_feiertag">religiöser Feiertag</option>

                </option>
              </select>
            </td>
          @endforeach

          {{-- EIN Feld für Verspätungs-Minuten (Woche) --}}
          <td class="px-3 py-2">
            <select
              name="fehlminuten_total[{{ $tid }}]"
              class="w-full border rounded-md px-2 py-1 text-sm"
              title="Viertelstündlich, wird bei allen 'verspätet'-Tagen dieser Woche addiert"
            >
              @for ($i = 0; $i <= 300; $i += 15)
                <option value="{{ $i }}">{{ $i }} min</option>
              @endfor
            </select>
          </td>

          {{-- aktuelle KW-Summe (bereits gespeichert) --}}
          <td class="px-3 py-2 text-sm text-gray-700">
            {{ $kwSum }}
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div class="mt-4 flex items-center gap-2">
    <a href="{{ route('gruppen.show', ['gruppe'=>$gruppe, 'w'=>$prevKw]) }}" class="px-3 py-2 border rounded-lg">← Vorwoche</a>
    <button class="px-4 py-2 rounded-lg bg-blue-600 text-white">Speichern</button>
    <a href="{{ route('gruppen.show', ['gruppe'=>$gruppe, 'w'=>$nextKw]) }}" class="px-3 py-2 border rounded-lg">Nächste Woche →</a>
  </div>
</form>


{{-- Alpine für die kleinen Toggles (via Breeze schon vorhanden). Falls nicht, diese Zeile entfernen und global einbinden. --}}
<script src="//unpkg.com/alpinejs" defer></script>
@endsection
