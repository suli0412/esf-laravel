@extends('layouts.app')

@section('title','Teilnehmer bearbeiten')

@section('content')

{{-- ========= TEILNEHMER BEARBEITEN ========= --}}

{{-- FORM 1: Teilnehmer speichern (eins, zentrales Formular) --}}
<form id="teilnehmerForm" method="POST" action="{{ route('teilnehmer.update', $teilnehmer) }}" class="space-y-6">
  @csrf
  @method('PUT')

  {{-- Stammdaten/Adresse etc. --}}
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

  {{-- =========================
       Niveaus – EINTRITT
     ========================= --}}

  <div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-base font-semibold mb-4">Niveau bei <span class="font-bold">Eintritt</span></h3>
        <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="prune_missing" value="1">Fehlende Kompetenzen entfernen (nicht gesetzte Einträge löschen)
        </label>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- Deutsch --}}
       <div>
        <label for="de_lesen_in" class="block text-sm font-medium mb-1">Deutsch: Leseverstehen</label>
        <select id="de_lesen_in" name="de_lesen_in" class="border rounded-lg w-full px-3 py-2">
          <option value="">— bitte wählen —</option>
          @foreach(($levelsDe ?? []) as $opt)
            <option value="{{ $opt }}" @selected(old('de_lesen_in', $teilnehmer->de_lesen_in) === $opt)>{{ $opt }}</option>
          @endforeach
        </select>
        @error('de_lesen_in')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label for="de_hoeren_in" class="block text-sm font-medium mb-1">Deutsch: Hörverstehen</label>
        <select id="de_hoeren_in" name="de_hoeren_in" class="border rounded-lg w-full px-3 py-2">
          <option value="">— bitte wählen —</option>
          @foreach(($levelsDe ?? []) as $opt)
            <option value="{{ $opt }}" @selected(old('de_hoeren_in', $teilnehmer->de_hoeren_in) === $opt)>{{ $opt }}</option>
          @endforeach
        </select>
        @error('de_hoeren_in')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label for="de_schreiben_in" class="block text-sm font-medium mb-1">Deutsch: Schreiben</label>
        <select id="de_schreiben_in" name="de_schreiben_in" class="border rounded-lg w-full px-3 py-2">
          <option value="">— bitte wählen —</option>
          @foreach(($levelsDe ?? []) as $opt)
            <option value="{{ $opt }}" @selected(old('de_schreiben_in', $teilnehmer->de_schreiben_in) === $opt)>{{ $opt }}</option>
          @endforeach
        </select>
        @error('de_schreiben_in')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label for="de_sprechen_in" class="block text-sm font-medium mb-1">Deutsch: Sprechen</label>
        <select id="de_sprechen_in" name="de_sprechen_in" class="border rounded-lg w-full px-3 py-2">
          <option value="">— bitte wählen —</option>
          @foreach(($levelsDe ?? []) as $opt)
            <option value="{{ $opt }}" @selected(old('de_sprechen_in', $teilnehmer->de_sprechen_in) === $opt)>{{ $opt }}</option>
          @endforeach
        </select>
        @error('de_sprechen_in')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Englisch --}}
      <div>
        <label for="en_in" class="block text-sm font-medium mb-1">Englisch</label>
        <select id="en_in" name="en_in" class="border rounded-lg w-full px-3 py-2">
          <option value="">— bitte wählen —</option>
          @foreach(($levelsEn ?? []) as $opt)
            <option value="{{ $opt }}" @selected(old('en_in', $teilnehmer->en_in) === $opt)>{{ $opt }}</option>
          @endforeach
        </select>
        @error('en_in')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Mathematik --}}
      <div>
        <label for="ma_in" class="block text-sm font-medium mb-1">Mathematik</label>
        <select id="ma_in" name="ma_in" class="border rounded-lg w-full px-3 py-2">
          <option value="">— bitte wählen —</option>
          @foreach(($levelsMa ?? []) as $opt)
            <option value="{{ $opt }}" @selected(old('ma_in', $teilnehmer->ma_in) === $opt)>{{ $opt }}</option>
          @endforeach
        </select>
        @error('ma_in')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
      </div>
    </div>
  </div>

  {{-- =========================
       Niveaus – AUSSTIEG (gleiches Set)
     ========================= --}}
  <div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-base font-semibold mb-4">Niveau bei <span class="font-bold">Ausstieg</span></h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      {{-- Deutsch --}}
      <div>
        <label for="de_lesen_out" class="block text-sm font-medium mb-1">Deutsch: Leseverstehen</label>
        <select id="de_lesen_out" name="de_lesen_out" class="border rounded-lg w-full px-3 py-2">
          <option value="">— bitte wählen —</option>
          @foreach(($levelsDe ?? []) as $opt)
            <option value="{{ $opt }}" @selected(old('de_lesen_out', $teilnehmer->de_lesen_out) === $opt)>{{ $opt }}</option>
          @endforeach
        </select>
        @error('de_lesen_out')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label for="de_hoeren_out" class="block text-sm font-medium mb-1">Deutsch: Hörverstehen</label>
        <select id="de_hoeren_out" name="de_hoeren_out" class="border rounded-lg w-full px-3 py-2">
          <option value="">— bitte wählen —</option>
          @foreach(($levelsDe ?? []) as $opt)
            <option value="{{ $opt }}" @selected(old('de_hoeren_out', $teilnehmer->de_hoeren_out) === $opt)>{{ $opt }}</option>
          @endforeach
        </select>
        @error('de_hoeren_out')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label for="de_schreiben_out" class="block text-sm font-medium mb-1">Deutsch: Schreiben</label>
        <select id="de_schreiben_out" name="de_schreiben_out" class="border rounded-lg w-full px-3 py-2">
          <option value="">— bitte wählen —</option>
          @foreach(($levelsDe ?? []) as $opt)
            <option value="{{ $opt }}" @selected(old('de_schreiben_out', $teilnehmer->de_schreiben_out) === $opt)>{{ $opt }}</option>
          @endforeach
        </select>
        @error('de_schreiben_out')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label for="de_sprechen_out" class="block text-sm font-medium mb-1">Deutsch: Sprechen</label>
        <select id="de_sprechen_out" name="de_sprechen_out" class="border rounded-lg w-full px-3 py-2">
          <option value="">— bitte wählen —</option>
          @foreach(($levelsDe ?? []) as $opt)
            <option value="{{ $opt }}" @selected(old('de_sprechen_out', $teilnehmer->de_sprechen_out) === $opt)>{{ $opt }}</option>
          @endforeach
        </select>
        @error('de_sprechen_out')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Englisch --}}
      <div>
        <label for="en_out" class="block text-sm font-medium mb-1">Englisch</label>
        <select id="en_out" name="en_out" class="border rounded-lg w-full px-3 py-2">
          <option value="">— bitte wählen —</option>
          @foreach(($levelsEn ?? []) as $opt)
            <option value="{{ $opt }}" @selected(old('en_out', $teilnehmer->en_out) === $opt)>{{ $opt }}</option>
          @endforeach
        </select>
        @error('en_out')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
      </div>

      {{-- Mathematik --}}
      <div>
        <label for="ma_out" class="block text-sm font-medium mb-1">Mathematik</label>
        <select id="ma_out" name="ma_out" class="border rounded-lg w-full px-3 py-2">
          <option value="">— bitte wählen —</option>
          @foreach(($levelsMa ?? []) as $opt)
            <option value="{{ $opt }}" @selected(old('ma_out', $teilnehmer->ma_out) === $opt)>{{ $opt }}</option>
          @endforeach
        </select>
        @error('ma_out')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
      </div>
    </div>
  </div>

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
{{-- Dokumente --}}
<section id="dokumente" class="bg-white rounded-xl shadow-sm p-5 mt-6">
  <h2 class="text-lg font-semibold mb-4">Dokumente</h2>




  {{-- Liste vorhandener Dokumente --}}
  <div class="mt-5">
    @if($teilnehmer->dokumente->isEmpty())
      <p class="text-sm text-gray-500">Keine Dokumente vorhanden.</p>
    @else
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="text-left px-2 py-2">Typ</th>
            <th class="text-left px-2 py-2">Datei</th>
            <th class="text-left px-2 py-2">Hochgeladen</th>
            <th class="text-right px-2 py-2">Aktionen</th>
          </tr>
        </thead>
        <tbody>
        @foreach($teilnehmer->dokumente as $doc)
          <tr class="border-t">
            <td class="px-2 py-2">{{ $doc->typ ?? '—' }}</td>
            <td class="px-2 py-2">
              {{ $doc->original_name ?? basename($doc->dokument_pfad) }}
            </td>
            <td class="px-2 py-2">
              {{ optional($doc->hochgeladen_am)->format('d.m.Y H:i') ?? '—' }}
            </td>
            <td class="px-2 py-2 text-right space-x-2">
              <a class="px-2 py-1 border rounded hover:bg-gray-50"
                 href="{{ route('teilnehmer.dokumente.download', [$teilnehmer, $doc]) }}">
                Download
              </a>
              <form action="{{ route('teilnehmer.dokumente.destroy', [$teilnehmer, $doc]) }}"
                    method="post" class="inline"
                    onsubmit="return confirm('Dokument wirklich löschen?');">
                @csrf @method('DELETE')
                <button class="px-2 py-1 border rounded text-red-600 hover:bg-red-50">Löschen</button>
              </form>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    @endif
  </div>
  <div>
      {{-- Upload-Form --}}
<form action="{{ route('teilnehmer.dokumente.store', $teilnehmer) }}"
      method="POST" enctype="multipart/form-data">
    @csrf

    <input type="file" name="file" required>

    @php
        $types = $docTypes ?? \App\Models\TeilnehmerDokument::TYPEN; // ['PDF','Foto','Sonstiges']
    @endphp
    <select name="typ" required>
        @foreach($types as $t)
            <option value="{{ $t }}">{{ $t }}</option>
        @endforeach
    </select>

    <input type="text" name="titel" placeholder="Titel (optional)">
    <button type="submit" class="px-2 py-1 border rounded text-green-600 hover:bg-red-50">Hochladen</button>
</form>
  </div>


@endsection

