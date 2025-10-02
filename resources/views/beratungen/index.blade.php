@extends('layouts.app')
@section('title','Einzelberatungen')

@section('content')
<div class="flex items-center justify-between mb-6 gap-3">
  <h1 class="text-2xl font-bold">Einzelberatungen</h1>
  @can('beratung.manage')
    <a href="{{ route('beratungen.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">+ Neue Beratung</a>
  @endcan

@can('beratung.manage')
  {{-- >>> komplette Karten/Forms für Einzelberatung & Gruppenberatung <<< --}}
@endcan

</div>

@include('partials.filters-beratung', ['scope' => 'einzel'])

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
  <table class="w-full text-left">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-3 py-2">Datum</th>
        <th class="px-3 py-2">Teilnehmer</th>
        <th class="px-3 py-2">Mitarbeiter</th>
        <th class="px-3 py-2">Art/Thema</th>
        <th class="px-3 py-2 text-right">Dauer</th>
        <th class="px-3 py-2 w-40">Aktionen</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $r)
      <tr class="border-t">
        <td class="px-3 py-2 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($r->datum)->format('d.m.Y H:i') }}</td>
        <td class="px-3 py-2">
          @if($r->teilnehmer)
            <a class="text-blue-700 hover:underline" href="{{ route('teilnehmer.show',$r->teilnehmer) }}">
              {{ $r->teilnehmer->Nachname }}, {{ $r->teilnehmer->Vorname }}
            </a>
          @else — @endif
        </td>
        <td class="px-3 py-2">{{ $r->mitarbeiter?->Nachname }}, {{ $r->mitarbeiter?->Vorname }}</td>
        <td class="px-3 py-2">
          @if($r->art)<span class="px-2 py-0.5 text-xs rounded bg-gray-100 border mr-1">{{ $r->art->name }}</span>@endif
          @if($r->thema)<span class="px-2 py-0.5 text-xs rounded bg-gray-100 border">{{ $r->thema->name }}</span>@endif
        </td>
        <td class="px-3 py-2 text-right">{{ $r->dauer_minuten }} min</td>
        <td class="px-3 py-2">
          <div class="flex items-center gap-3">
            @can('beratung.manage')
              <a href="{{ route('beratungen.edit',$r) }}" class="text-yellow-700 hover:underline">Bearbeiten</a>
              <form action="{{ route('beratungen.destroy',$r) }}" method="POST" onsubmit="return confirm('Löschen?')" class="inline">
                @csrf @method('DELETE')
                <button class="text-red-700 hover:underline" type="submit">Löschen</button>
              </form>
            @endcan
          </div>
        </td>
      </tr>
      @empty
      <tr><td class="px-3 py-6 text-gray-500" colspan="6">Keine Einträge.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $rows->links() }}</div>
@endsection
