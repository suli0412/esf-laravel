@extends('layouts.app')
@section('title','Beratungen')

@section('content')
<div class="flex items-center justify-between mb-6 gap-3">
  <h2 class="text-2xl font-bold">Beratungen</h2>

  <div class="flex items-center gap-2">
    {{-- Toggle-Buttons für Schnell-Formulare --}}
    <button type="button" id="btnNewInd" class="px-3 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
      Neue Einzelberatung
    </button>
    <button type="button" id="btnNewGrp" class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">
      Neue Gruppenberatung
    </button>

    {{-- Suche --}}
    <form method="GET" action="{{ route('beratungen.index') }}" class="flex items-center gap-2">
      <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Suche: TN/Mitarbeiter/Thema"
             class="border rounded px-3 py-2">
      @if(!empty($q))
        <a href="{{ route('beratungen.index') }}" class="px-3 py-2 border rounded">Zurücksetzen</a>
      @endif
      <button class="px-4 py-2 bg-gray-800 text-white rounded">Suchen</button>
    </form>
  </div>
</div>

@if(session('success'))
  <div class="mb-4 rounded border bg-green-50 text-green-800 px-3 py-2">{{ session('success') }}</div>
@endif

{{-- Schnell: Individuelle Beratung (einklappbar) --}}
<div id="panelInd" class="hidden mb-6 bg-white rounded-xl shadow-sm p-4">
  <div class="flex items-center justify-between mb-3">
    <h3 class="font-semibold">Schnell: Individuelle Beratung</h3>
    <button type="button" class="text-sm text-gray-500 hover:underline" data-close="panelInd">schließen</button>
  </div>

  @include('beratungen._quick_form_individual', [
    // kein vorselektierter TN/Mitarbeiter -> Dropdowns anzeigen
    'teilnehmer'      => null,
    'mitarbeiter'     => null,
    'arten'           => $arten,
    'themen'          => $themen,
    'teilnehmerList'  => $teilnehmerList ?? null,
    'mitarbeiterList' => $mitarbeiterList ?? null,
  ])
</div>

{{-- Schnell: Gruppenberatung (einklappbar) --}}
<div id="panelGrp" class="hidden mb-6 bg-white rounded-xl shadow-sm p-4">
  <div class="flex items-center justify-between mb-3">
    <h3 class="font-semibold">Schnell: Gruppenberatung</h3>
    <button type="button" class="text-sm text-gray-500 hover:underline" data-close="panelGrp">schließen</button>
  </div>

  @include('beratungen._quick_form_group', [
    'arten'           => $arten,
    'themen'          => $themen,
    'teilnehmerList'  => $teilnehmerList ?? null,
    'mitarbeiterList' => $mitarbeiterList ?? null,
  ])
</div>

{{-- Tabelle --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
  <table class="w-full text-left">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-3 py-2">Datum</th>
        <th class="px-3 py-2">Teilnehmer</th>
        <th class="px-3 py-2">Mitarbeiter</th>
        <th class="px-3 py-2">Art</th>
        <th class="px-3 py-2">Thema</th>
        <th class="px-3 py-2">Dauer</th>
        <th class="px-3 py-2">Notizen</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $r)
        <tr class="border-t">
          <td class="px-3 py-2 whitespace-nowrap">{{ optional($r->datum)->format('d.m.Y') }}</td>
          <td class="px-3 py-2">
            @if($r->teilnehmer)
              <a href="{{ route('teilnehmer.show', $r->teilnehmer) }}" class="text-blue-600 hover:underline">
                {{ $r->teilnehmer->Nachname }}, {{ $r->teilnehmer->Vorname }}
              </a>
            @else — @endif
          </td>
          <td class="px-3 py-2">
            @if($r->mitarbeiter)
              <a href="{{ route('mitarbeiter.show', $r->mitarbeiter) }}" class="text-blue-600 hover:underline">
                {{ $r->mitarbeiter->Nachname }}, {{ $r->mitarbeiter->Vorname }}
              </a>
            @else — @endif
          </td>
          <td class="px-3 py-2">{{ $r->art?->Code }} {{ $r->art?->Bezeichnung }}</td>
          <td class="px-3 py-2">{{ $r->thema?->Bezeichnung }}</td>
          <td class="px-3 py-2">{{ $r->dauer_h ?? '—' }}</td>
          <td class="px-3 py-2 max-w-[28rem]">
            <span class="line-clamp-2">{{ $r->notizen }}</span>
          </td>
        </tr>
      @empty
        <tr><td class="px-3 py-6 text-gray-500" colspan="7">Keine Einträge.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $rows->links() }}</div>

{{-- kleine JS-Helfer zum Ein-/Ausblenden --}}
<script>
  document.getElementById('btnNewInd')?.addEventListener('click', () => {
    document.getElementById('panelInd')?.classList.toggle('hidden');
    document.getElementById('panelGrp')?.classList.add('hidden');
  });
  document.getElementById('btnNewGrp')?.addEventListener('click', () => {
    document.getElementById('panelGrp')?.classList.toggle('hidden');
    document.getElementById('panelInd')?.classList.add('hidden');
  });
  document.querySelectorAll('[data-close]').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = btn.getAttribute('data-close');
      document.getElementById(id)?.classList.add('hidden');
    });
  });
</script>
@endsection
