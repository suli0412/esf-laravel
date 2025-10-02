{{-- resources/views/gruppen_beratungen/index.blade.php --}}
@extends('layouts.app')
@section('title','Gruppen­beratungen')

@php use Illuminate\Support\Facades\Route; @endphp

@section('content')
<div class="flex items-center justify-between gap-3 mb-6">
  <h1 class="text-2xl font-bold">Gruppen­beratungen</h1>

  @if(Route::has('gruppen_beratungen.create'))
    @can('beratung.manage')
      <a href="{{ route('gruppen_beratungen.create') }}"
         class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
        + Neu
      </a>
    @endcan
  @endif
</div>

{{-- Filterleiste --}}
<form method="GET" action="{{ route('gruppen_beratungen.index') }}"
      class="grid grid-cols-1 md:grid-cols-6 gap-3 bg-white p-3 rounded-xl shadow-sm mb-4">
  <div class="md:col-span-2">
    <label class="text-xs text-gray-600">Suche (Betreff/Inhalt)</label>
    <input name="q" value="{{ $q }}" class="w-full border rounded px-3 py-2" placeholder="z. B. Bewerbung">
  </div>
  <div>
    <label class="text-xs text-gray-600">Von</label>
    <input type="date" name="von" value="{{ $von }}" class="w-full border rounded px-3 py-2">
  </div>
  <div>
    <label class="text-xs text-gray-600">Bis</label>
    <input type="date" name="bis" value="{{ $bis }}" class="w-full border rounded px-3 py-2">
  </div>
  <div>
    <label class="text-xs text-gray-600">Gruppe</label>
    <select name="gruppe_id" class="w-full border rounded px-3 py-2">
      <option value="">– alle –</option>
      @foreach($gruppen as $g)
        <option value="{{ $g->gruppe_id }}" @selected($gruppeId == $g->gruppe_id)>{{ $g->name }}</option>
      @endforeach
    </select>
  </div>
  <div>
    <label class="text-xs text-gray-600">Mitarbeiter*in</label>
    <select name="mitarbeiter_id" class="w-full border rounded px-3 py-2">
      <option value="">– alle –</option>
      @foreach($mitarbeiter as $m)
        <option value="{{ $m->Mitarbeiter_id }}" @selected($mitarbeiterId == $m->Mitarbeiter_id)>
          {{ $m->Nachname }}, {{ $m->Vorname }}
        </option>
      @endforeach
    </select>
  </div>
  <div>
    <label class="text-xs text-gray-600">Art</label>
    <select name="art_id" class="w-full border rounded px-3 py-2">
      <option value="">– alle –</option>
      @foreach($arten as $a)
        <option value="{{ $a->Art_id }}" @selected($artId == $a->Art_id)>{{ $a->Bezeichnung }}</option>
      @endforeach
    </select>
  </div>
  <div>
    <label class="text-xs text-gray-600">Thema (Katalog)</label>
    <select name="thema_id" class="w-full border rounded px-3 py-2">
      <option value="">– alle –</option>
      @foreach($themen as $t)
        <option value="{{ $t->Thema_id }}" @selected($themaId == $t->Thema_id)>{{ $t->Bezeichnung }}</option>
      @endforeach
    </select>
  </div>

  <div class="md:col-span-6 flex items-center gap-2 pt-1">
    <button class="px-4 py-2 rounded bg-gray-800 text-white">Filtern</button>
    @if($q || $von || $bis || $gruppeId || $mitarbeiterId || $artId || $themaId)
      <a href="{{ route('gruppen_beratungen.index') }}" class="px-3 py-2 rounded border">Reset</a>
    @endif
  </div>
</form>

{{-- Liste --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
  <table class="w-full text-left">
    <thead class="bg-gray-50 text-sm">
      <tr>
        <th class="px-3 py-2">Datum</th>
        <th class="px-3 py-2">Gruppe</th>
        <th class="px-3 py-2">Art</th>
        <th class="px-3 py-2">Thema (Katalog)</th>
        <th class="px-3 py-2">Betreff / Inhalt</th>
        <th class="px-3 py-2">Mitarbeiter*in</th>
        <th class="px-3 py-2 text-right">Dauer</th>
        <th class="px-3 py-2 text-right">TN</th>
        <th class="px-3 py-2">Aktionen</th>
      </tr>
    </thead>
    <tbody class="text-sm">
      @forelse($rows as $row)
        @php
          $datum = $row->datum ? \Illuminate\Support\Carbon::parse($row->datum)->format('d.m.Y') : '';
          $inhaltKurz = \Illuminate\Support\Str::limit(strip_tags($row->inhalt ?? ''), 70);
        @endphp
        <tr class="border-t">
          <td class="px-3 py-2 whitespace-nowrap">{{ $datum }}</td>
          <td class="px-3 py-2">{{ $row->gruppe?->name ?? '—' }}</td>
          <td class="px-3 py-2">{{ $row->art?->Bezeichnung ?? '—' }}</td>
          <td class="px-3 py-2">{{ $row->thema?->Bezeichnung ?? '—' }}</td>
          <td class="px-3 py-2">
            <div class="font-medium">{{ $row->thema ?? '—' }}</div>
            @if($inhaltKurz)
              <div class="text-gray-500">{{ $inhaltKurz }}</div>
            @endif
            @if($row->TNUnterlagen)
              <span class="inline-block mt-1 text-[11px] px-2 py-0.5 rounded bg-emerald-50 text-emerald-700">Unterlagen</span>
            @endif
          </td>
          <td class="px-3 py-2">
            @if($row->mitarbeiter)
              {{ $row->mitarbeiter->Nachname }}, {{ $row->mitarbeiter->Vorname }}
            @else
              —
            @endif
          </td>
          <td class="px-3 py-2 text-right">{{ $row->dauer_h ? number_format($row->dauer_h,1,',','.') . ' h' : '—' }}</td>
          <td class="px-3 py-2 text-right">{{ $row->teilnehmer_count }}</td>
          <td class="px-3 py-2 whitespace-nowrap">
            <div class="flex items-center gap-2">
              @if(Route::has('gruppen_beratungen.edit'))
                @can('beratung.manage')
                  <a href="{{ route('gruppen_beratungen.edit', $row) }}" class="text-blue-700 hover:underline">Bearbeiten</a>
                @endcan
              @endif

              @if(Route::has('gruppen_beratungen.destroy'))
                @can('beratung.manage')
                  <form action="{{ route('gruppen_beratungen.destroy', $row) }}" method="POST"
                        onsubmit="return confirm('Diesen Eintrag löschen?')" class="inline">
                    @csrf @method('DELETE')
                    <button class="text-red-700 hover:underline">Löschen</button>
                  </form>
                @endcan
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="9" class="px-3 py-10 text-center text-gray-500">Keine Einträge gefunden.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">
  {{ $rows->links() }}
</div>
@endsection
