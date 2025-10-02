@extends('layouts.app')

@section('title', 'Gruppe • '.$gruppe->name.' • KW '.\Carbon\Carbon::parse($days[0])->isoWeek)

@section('content')
@php
  $monday = \Carbon\Carbon::parse($days[0]);
  $sunday = \Carbon\Carbon::parse($days[count($days)-1]);
  $headline = 'KW '.$monday->isoWeek.' • '.$monday->format('d.m.').' – '.$sunday->format('d.m.Y');
  $fehlOpts = collect(range(0, 20))->map(fn($i)=>$i*15); // 0..300 in 15er-Schritten
@endphp

<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold">{{ $gruppe->name }}</h1>
    <p class="text-sm text-gray-500">{{ $headline }}</p>
  </div>

  <div class="flex items-center gap-2">
    <a href="{{ route('gruppen.show', [$gruppe, 'kw' => $prevKw]) }}"
       class="px-3 py-2 border rounded-lg hover:bg-gray-50">« Vorherige Woche</a>

    <a href="{{ route('gruppen.show', [$gruppe, 'kw' => $nextKw]) }}"
       class="px-3 py-2 border rounded-lg hover:bg-gray-50">Nächste Woche »</a>
  </div>
</div>

<form method="POST" action="{{ route('gruppen.anwesenheit.save', $gruppe) }}" class="space-y-4">
  @csrf
  <input type="hidden" name="kw" value="{{ $kw }}">

  <div class="bg-white rounded-xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="bg-gray-50">
          <th class="text-left px-3 py-2 w-56">Teilnehmer:in</th>
          @foreach($days as $d)
            @php $dt = \Carbon\Carbon::parse($d); @endphp
            <th class="text-left px-2 py-2 min-w-[160px]">
              <div class="font-medium">{{ $dt->isoFormat('dd, DD.MM') }}</div>
              <div class="text-xs text-gray-500">{{ $dt->isoFormat('dddd') }}</div>
            </th>
          @endforeach
          <th class="text-right px-3 py-2 w-28">Fehlmin. (KW)</th>
        </tr>
      </thead>
      <tbody>
        @forelse($members as $m)
          @php $tid = $m->Teilnehmer_id; @endphp
          <tr class="border-t">
            <td class="px-3 py-2 font-medium">
              {{ $m->Nachname }}, {{ $m->Vorname }}
            </td>

            @foreach($days as $d)
              @php
                $preset = $att[$tid][$d] ?? ['status' => 'anwesend', 'fehlminuten' => 0];
                $s = $preset['status'];
                $fm = (int) $preset['fehlminuten'];
              @endphp
              <td class="px-2 py-2 align-top">
                <div x-data="{ s: '{{ $s }}' }" class="space-y-1">
                  <select x-model="s"
                          name="status[{{ $tid }}][{{ $d }}]"
                          class="w-full rounded border-gray-300 text-sm">
                    @foreach($stati as $opt)
                      <option value="{{ $opt }}">{{ str_replace('_',' ',ucfirst($opt)) }}</option>
                    @endforeach
                  </select>

                  <div x-show="s === 'anwesend_verspaetet'" class="flex items-center gap-1">
                    <label class="text-xs text-gray-500">Fehlmin:</label>
                    <select name="fehlminuten[{{ $tid }}][{{ $d }}]"
                            class="rounded border-gray-300 text-sm">
                      @foreach($fehlOpts as $min)
                        <option value="{{ $min }}" @selected($fm===$min)>
                          {{ $min===0 ? '—' : $min.' min' }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                </div>
              </td>
            @endforeach

            <td class="px-3 py-2 text-right font-semibold">
              {{ (int)($sumFehl[$tid] ?? 0) }} min
            </td>
          </tr>
        @empty
          <tr><td colspan="{{ 2 + count($days) }}" class="px-3 py-6 text-center text-gray-500">
            Keine Mitglieder in dieser Gruppe.
          </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="flex items-center justify-between">
    <div class="text-sm text-gray-500">
      Tipp: Standard ist <strong>„anwesend“</strong>. Nur bei „anwesend mit Verspätung“ bitte Fehlminuten wählen.
    </div>
    <button class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
      Woche speichern
    </button>
  </div>
</form>

{{-- Mitglieder verwalten (Attach/Detach) --}}
<div class="mt-8 bg-white rounded-xl shadow-sm p-5">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-lg font-semibold">Mitglieder verwalten</h2>
    <a href="{{ route('gruppen.index') }}" class="text-sm text-blue-600 hover:underline">Zur Gruppen-Übersicht</a>
  </div>
  <p class="text-sm text-gray-600">
    Teilnehmer:innen hinzufügen/entfernen kannst du weiterhin über die vorhandenen Routen
    <code>gruppen.mitglieder.attach</code> / <code>gruppen.mitglieder.detach</code>.
  </p>
</div>

@php
  $gruppe->loadMissing(['createdBy:id,name,Vorname,Nachname','updatedBy:id,name,Vorname,Nachname']);
@endphp

@include('partials.audit', ['model' => $gruppe])

@endsection
