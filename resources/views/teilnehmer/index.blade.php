@extends('layouts.app')
@section('title','Teilnehmer')

@section('content')
<div class="mb-6">
  <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
    <h1 class="text-2xl font-bold">Teilnehmer</h1>

    <div class="flex items-center gap-2">
      {{-- Ansicht umschalten --}}
      @php $qs = request()->except('view'); @endphp
      <a href="{{ route('teilnehmer.index', array_merge($qs, ['view'=>'table'])) }}"
         class="px-3 py-2 rounded border {{ $view==='table'?'bg-gray-900 text-white':'bg-white' }}">
        Tabelle
      </a>
      <a href="{{ route('teilnehmer.index', array_merge($qs, ['view'=>'cards'])) }}"
         class="px-3 py-2 rounded border {{ $view==='cards'?'bg-gray-900 text-white':'bg-white' }}">
        Karten
      </a>
      <a href="{{ route('teilnehmer.create') }}"
         class="px-4 py-2 rounded bg-blue-600 text-white">+ Neuer Teilnehmer</a>
    </div>
  </div>

  {{-- Filterleiste --}}
  <form method="GET" action="{{ route('teilnehmer.index') }}"
        class="mt-4 grid grid-cols-1 md:grid-cols-5 gap-3 bg-white p-3 rounded-xl shadow-sm">

    <div class="col-span-2">
      <label class="block text-sm text-gray-600 mb-1">Suche (Name/Email/Tel.)</label>
      <input name="q" value="{{ $q }}" class="w-full border rounded px-3 py-2" placeholder="z. B. Müller oder mueller@...">
    </div>

    <div>
      <label class="block text-sm text-gray-600 mb-1">Gruppe</label>
      <select name="gruppe_id" class="w-full border rounded px-3 py-2">
        <option value="">— alle —</option>
        @foreach($gruppen as $g)
          <option value="{{ $g->gruppe_id }}" @selected($gruppeId == $g->gruppe_id)>{{ $g->name }}</option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="block text-sm text-gray-600 mb-1">Dokumente</label>
      <select name="has_docs" class="w-full border rounded px-3 py-2">
        <option value="">— egal —</option>
        <option value="1" @selected($hasDocs==='1')>nur mit Dokumenten</option>
        <option value="0" @selected($hasDocs==='0')>ohne Dokumente</option>
      </select>
    </div>

    <div>
      <label class="block text-sm text-gray-600 mb-1">Sortierung</label>
      <select name="sort" class="w-full border rounded px-3 py-2">
        <option value="name_asc"    @selected($sort==='name_asc')>Name A→Z</option>
        <option value="gruppe"      @selected($sort==='gruppe')>Gruppe</option>
        <option value="created_desc"@selected($sort==='created_desc')>Neueste zuerst</option>
        <option value="updated_desc"@selected($sort==='updated_desc')>Zuletzt bearbeitet</option>
      </select>
    </div>

    <div class="md:col-span-5 flex items-center gap-2">
      <button class="px-4 py-2 bg-gray-900 text-white rounded">Filtern</button>
      @if($q || $gruppeId || $hasDocs !== null || $sort !== 'name_asc')
        <a href="{{ route('teilnehmer.index') }}" class="px-3 py-2 border rounded bg-white">Reset</a>
      @endif
      <div class="ml-auto text-sm text-gray-500">
        {{ $rows->total() }} Einträge
      </div>
    </div>
  </form>
</div>

{{-- Helper für Level (Out bevorzugt, sonst In) --}}
@php
  $pick = function($row, $out, $in) {
    return $row->$out ?: $row->$in ?: '—';
  };
@endphp

@if($view === 'cards')
  {{-- Kartenansicht --}}
  <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @forelse($rows as $t)
      <div class="bg-white rounded-xl shadow-sm p-4 border">
        <div class="flex items-start justify-between gap-3">
          <div>
            <a href="{{ route('teilnehmer.edit', $t) }}" class="text-lg font-semibold hover:underline">
              {{ $t->Nachname }}, {{ $t->Vorname }}
            </a>
            <div class="text-sm text-gray-600 mt-1">
              {{ $t->Email ?? '—' }} · {{ $t->Telefonnummer ?? '—' }}
            </div>
            <div class="mt-1">
              @if($t->gruppe_id)
                <span class="inline-flex items-center text-xs px-2 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                  {{ optional($t->gruppe)->name ?? ('Gruppe #'.$t->gruppe_id) }}
                </span>
              @else
                <span class="inline-flex items-center text-xs px-2 py-1 rounded-full bg-gray-50 text-gray-700 border">ohne Gruppe</span>
              @endif
            </div>
          </div>
          <div class="text-right text-sm text-gray-500">
            <div>Dok: {{ $t->dokumente_count }}</div>
            <div>Prak: {{ $t->praktika_count }}</div>
          </div>
        </div>

        <div class="mt-3 grid grid-cols-3 gap-2 text-xs">
          <div class="rounded border px-2 py-1">
            <div class="text-gray-500">Deutsch</div>
            <div class="font-medium">
              {{ $pick($t,'de_sprechen_out','de_sprechen_in') }}
              {{-- Alternative: mehrere anzeigen: Lesen/Hören/Schreiben/Sprechen --}}
            </div>
          </div>
          <div class="rounded border px-2 py-1">
            <div class="text-gray-500">Englisch</div>
            <div class="font-medium">{{ $pick($t,'en_out','en_in') }}</div>
          </div>
          <div class="rounded border px-2 py-1">
            <div class="text-gray-500">Mathe</div>
            <div class="font-medium">{{ $pick($t,'ma_out','ma_in') }}</div>
          </div>
        </div>

        <div class="mt-4 flex items-center gap-2">
          <a href="{{ route('teilnehmer.show', $t) }}" class="px-3 py-2 text-sm rounded border">Details</a>
          <a href="{{ route('teilnehmer.edit', $t) }}" class="px-3 py-2 text-sm rounded bg-blue-600 text-white">Bearbeiten</a>
        </div>
      </div>
    @empty
      <div class="text-gray-500">Keine Teilnehmer gefunden.</div>
    @endforelse
  </div>
@else
  {{-- Tabellenansicht --}}
  <div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-left">
      <thead class="bg-gray-50 sticky top-0">
        <tr class="text-sm text-gray-600">
          <th class="px-3 py-2">Name</th>
          <th class="px-3 py-2">Kontakt</th>
          <th class="px-3 py-2">Gruppe</th>
          <th class="px-3 py-2">DE</th>
          <th class="px-3 py-2">EN</th>
          <th class="px-3 py-2">MA</th>
          <th class="px-3 py-2 text-right">Dok</th>
          <th class="px-3 py-2 text-right">Prak</th>
          <th class="px-3 py-2">Aktionen</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $t)
          <tr class="border-t">
            <td class="px-3 py-2">
              <a href="{{ route('teilnehmer.edit', $t) }}" class="font-medium hover:underline">
                {{ $t->Nachname }}, {{ $t->Vorname }}
              </a>
              <div class="text-xs text-gray-500">
                erstellt {{ optional($t->created_at)->format('d.m.Y') }},
                geändert {{ optional($t->updated_at)->format('d.m.Y') }}
              </div>
            </td>
            <td class="px-3 py-2 text-sm">
              <div>{{ $t->Email ?? '—' }}</div>
              <div class="text-gray-500">{{ $t->Telefonnummer ?? '—' }}</div>
            </td>
            <td class="px-3 py-2">
              @if($t->gruppe_id)
                <span class="inline-flex items-center text-xs px-2 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                  {{ optional($t->gruppe)->name ?? ('#'.$t->gruppe_id) }}
                </span>
              @else
                <span class="inline-flex items-center text-xs px-2 py-1 rounded-full bg-gray-50 text-gray-700 border">—</span>
              @endif
            </td>
            <td class="px-3 py-2 text-sm">{{ $pick($t,'de_sprechen_out','de_sprechen_in') }}</td>
            <td class="px-3 py-2 text-sm">{{ $pick($t,'en_out','en_in') }}</td>
            <td class="px-3 py-2 text-sm">{{ $pick($t,'ma_out','ma_in') }}</td>
            <td class="px-3 py-2 text-right">{{ $t->dokumente_count }}</td>
            <td class="px-3 py-2 text-right">{{ $t->praktika_count }}</td>
            <td class="px-3 py-2">
              <div class="flex items-center gap-2">
                <a href="{{ route('teilnehmer.show', $t) }}" class="text-sm text-green-700 hover:underline">Details</a>
                <span class="text-gray-300">|</span>
                <a href="{{ route('teilnehmer.edit', $t) }}" class="text-sm text-blue-700 hover:underline">Bearbeiten</a>
              </div>
            </td>
          </tr>
        @empty
          <tr><td class="px-3 py-6 text-gray-500" colspan="9">Keine Teilnehmer gefunden.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endif

<div class="mt-4">
  {{ $rows->onEachSide(1)->links() }}
</div>
@endsection
