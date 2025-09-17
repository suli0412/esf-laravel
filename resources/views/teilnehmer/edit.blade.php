@extends('layouts.app')

@section('title','Teilnehmer bearbeiten')

@section('content')




{{-- ========= TEILNEHMER BEARBEITEN ========= --}}

{{-- FORM 1: Teilnehmer speichern (eins, zentrales Formular) --}}
<form id="teilnehmerForm" method="POST" action="{{ route('teilnehmer.update', $teilnehmer) }}" class="space-y-6">
  @csrf
  @method('PUT')

  {{-- Falls du bereits ein Teil-Formular hast, kannst du es hier einbinden: --}}
  @includeIf('teilnehmer._form', ['teilnehmer' => $teilnehmer])

  {{-- Gruppe (Teil des Hauptformulars, damit speicherbar) --}}
  @isset($gruppen)
    <div class="bg-white rounded-xl shadow-sm p-6">
      <label class="block text-sm font-medium mb-1">Gruppe (optional)</label>
      <select name="gruppe_id" class="border rounded w-full px-3 py-2">
        <option value="">— keine —</option>
        @foreach($gruppen as $g)
          <option value="{{ $g->gruppe_id }}"
            {{ (string)old('gruppe_id', $teilnehmer->gruppe_id) === (string)$g->gruppe_id ? 'selected' : '' }}>
            {{ $g->code ? $g->code.' — ' : '' }}{{ $g->name }}
          </option>
        @endforeach
      </select>
      @error('gruppe_id')
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
      @enderror
    </div>
  @endisset

  {{-- Buttons für Teilnehmer speichern/abbrechen --}}
  <div class="flex items-center gap-2">
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Teilnehmer speichern</button>
    <a href="{{ route('teilnehmer.show', $teilnehmer) }}" class="px-4 py-2 border rounded">Abbrechen</a>
  </div>
</form>

{{-- ===== AB HIER: SEPARATE FORMULARE (NICHT im Teilnehmer-Form verschachteln!) ===== --}}

{{-- PRAKTIKUM: Liste + Neu anlegen (eigene Form, eigene Route) --}}
<div id="praktika" class="bg-white rounded-xl shadow-sm p-6 mt-8">
  <h3 class="text-lg font-semibold mb-4">Praktikum</h3>

  {{-- Liste --}}
<table class="w-full text-sm mb-4">
  <thead class="bg-gray-50">
    <tr>
      <th class="px-3 py-2">Bereich</th>
      <th class="px-3 py-2">Firma</th>
      <th class="px-3 py-2">Zeitraum</th>
      <th class="px-3 py-2 text-right">Stunden</th>
      <th class="px-3 py-2 w-28">Aktionen</th>
    </tr>
  </thead>
  <tbody>
    @forelse($teilnehmer->praktika as $p)
      <tr class="border-t">
        <td class="px-3 py-2">{{ $p->bereich ?? '—' }}</td>
        <td class="px-3 py-2">{{ $p->firma ?? '—' }}</td>
        <td class="px-3 py-2">
          {{ optional($p->beginn)->format('d.m.Y') }} – {{ optional($p->ende)->format('d.m.Y') }}
        </td>
        <td class="px-3 py-2 text-right">
          {{ $p->stunden_ausmass !== null ? number_format((float)$p->stunden_ausmass, 2, ',', '.') : '—' }}
        </td>
        <td class="px-3 py-2">
          <form action="{{ route('praktika.destroy', ['teilnehmer' => $teilnehmer, 'praktikum' => $p]) }}"
                method="POST"
                onsubmit="return confirm('Löschen?')">
            @csrf
            @method('DELETE')
            <button class="text-red-600 hover:underline">Löschen</button>
          </form>
        </td>
      </tr>
    @empty
      <tr><td colspan="5" class="px-3 py-4 text-gray-500">Noch keine Einträge.</td></tr>
    @endforelse
  </tbody>
</table>


  {{-- Neues Praktikum hinzufügen (separate POST-Form) --}}
  <form action="{{ route('praktika.store', $teilnehmer) }}" method="POST" class="grid grid-cols-12 gap-3">
    @csrf
    <div class="col-span-3">
      <label class="block text-sm mb-1">Bereich</label>
      <input name="bereich" value="{{ old('bereich') }}" class="border rounded w-full px-3 py-2">
    </div>
    <div class="col-span-3">
      <label class="block text-sm mb-1">Firma</label>
      <input name="firma" value="{{ old('firma') }}" class="border rounded w-full px-3 py-2">
    </div>
    <div class="col-span-2">
      <label class="block text-sm mb-1">Land</label>
      <input name="land" value="{{ old('land') }}" class="border rounded w-full px-3 py-2">
    </div>
    <div class="col-span-2">
      <label class="block text-sm mb-1">Von</label>
      <input type="date" name="von" value="{{ old('von') }}" class="border rounded w-full px-3 py-2">
    </div>
    <div class="col-span-2">
      <label class="block text-sm mb-1">Bis</label>
      <input type="date" name="bis" value="{{ old('bis') }}" class="border rounded w-full px-3 py-2">
    </div>
    <div class="col-span-2">
      <label class="block text-sm mb-1">Stunden</label>
      <input type="number" step="0.01" min="0" name="stunden" value="{{ old('stunden') }}" class="border rounded w-full px-3 py-2">
    </div>
    <div class="col-span-10">
      <label class="block text-sm mb-1">Anmerkung</label>
      <input name="anmerkung" value="{{ old('anmerkung') }}" class="border rounded w-full px-3 py-2">
    </div>
    <div class="col-span-12">
      <button class="px-4 py-2 bg-blue-600 text-white rounded">Praktikum hinzufügen</button>
    </div>
  </form>
</div>

{{-- KOMPETENZSTAND (eigene Formulare/Partials – unbedingt außerhalb des Teilnehmer-Hauptformulars einbinden) --}}
@includeIf('teilnehmer.partials.kompetenz_form', [
  'kompetenzen' => $kompetenzen ?? [],
  'niveaus'     => $niveaus ?? [],
  'teilnehmer'  => $teilnehmer
])

@endsection
