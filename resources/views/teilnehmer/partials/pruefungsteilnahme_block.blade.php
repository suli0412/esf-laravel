@php
  // nutze Relation aus Teilnehmer-Modell (pruefungstermine)
  $prfs = $teilnehmer->pruefungstermine()
            ->with('niveau')
            ->orderBy('datum','desc')
            ->get();
@endphp

@if($prfs->isEmpty())
  <p class="text-sm text-gray-500">Keine Prüfungsteilnahmen.</p>
@else
  <table class="w-full text-sm">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-3 py-2 text-left">Datum</th>
        <th class="px-3 py-2 text-left">Niveau</th>
        <th class="px-3 py-2 text-left">Bezeichnung</th>
        <th class="px-3 py-2 text-left">Institut</th>
        <th class="px-3 py-2 text-left">Bestanden</th>
        <th class="px-3 py-2 text-left">Selbstzahler</th>
        <th class="px-3 py-2 w-32"></th>
      </tr>
    </thead>
    <tbody>
      @foreach($prfs as $t)
        <tr class="border-t">
          <td class="px-3 py-2">{{ optional($t->datum)->format('d.m.Y') }}</td>
          <td class="px-3 py-2">{{ $t->niveau?->code }}</td>
          <td class="px-3 py-2">{{ $t->bezeichnung }}</td>
          <td class="px-3 py-2">{{ $t->institut }}</td>
          <td class="px-3 py-2">
            @if($t->pivot->bestanden === null) offen
            @elseif($t->pivot->bestanden) Ja
            @else Nein
            @endif
          </td>
          <td class="px-3 py-2">{{ $t->pivot->selbstzahler ? 'Ja' : 'Nein' }}</td>
          <td class="px-3 py-2 text-right">
            <a class="text-blue-600 hover:underline" href="{{ route('pruefungstermine.show',$t) }}">Termin öffnen</a>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>
@endif

<div class="mt-3 text-sm">
  <a class="text-blue-600 hover:underline" href="{{ route('pruefungstermine.index') }}">Zu den Prüfungsterminen</a>
</div>
