@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
  <div class="bg-white rounded-2xl shadow border p-6">
    <h1 class="text-xl font-semibold mb-4">Beratung anlegen</h1>

    <form method="POST" action="{{ route('beratungen.store') }}">
      @csrf

      {{-- Teilnehmer (vorausgewählt, wenn per ?teilnehmer=... aufgerufen) --}}
      @php
        $tn = $teilnehmer ?? null;
        $tnId = old('teilnehmer_id', $tn->Teilnehmer_id ?? '');
      @endphp
      <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Teilnehmer</label>
        @if($tn)
          <div class="px-3 py-2 border rounded bg-gray-50">
            {{ $tn->Nachname }} {{ $tn->Vorname }}
          </div>
          <input type="hidden" name="teilnehmer_id" value="{{ $tnId }}">
        @else
          <input type="number" name="teilnehmer_id" value="{{ $tnId }}" class="w-full border rounded px-3 py-2" required>
        @endif
        @error('teilnehmer_id')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
      </div>

      {{-- Art --}}
      <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Art</label>
        <select name="art_id" class="w-full border rounded px-3 py-2" required>
          <option value="">— auswählen —</option>
          @foreach($arten as $a)
            @php
              $aid   = $a->Art_id ?? $a->art_id ?? $a->getKey();
              $albl  = $a->Bezeichnung ?? $a->bezeichnung ?? ($a->Code ?? ('Art #'.$aid));
            @endphp
            <option value="{{ $aid }}" @selected(old('art_id') == $aid)>{{ $albl }}</option>
          @endforeach
        </select>
        @error('art_id')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
      </div>

      {{-- Thema --}}
      <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Thema</label>
        <select name="thema_id" class="w-full border rounded px-3 py-2" required>
          <option value="">— auswählen —</option>
          @foreach($themen as $t)
            @php
              $tid   = $t->Thema_id ?? $t->thema_id ?? $t->getKey();
              $tlbl  = $t->Bezeichnung ?? $t->bezeichnung ?? ('Thema #'.$tid);
            @endphp
            <option value="{{ $tid }}" @selected(old('thema_id') == $tid)>{{ $tlbl }}</option>
          @endforeach
        </select>
        @error('thema_id')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
      </div>

      {{-- Mitarbeiter (optional) --}}
      <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Mitarbeiter (optional)</label>
        <select name="mitarbeiter_id" class="w-full border rounded px-3 py-2">
          <option value="">— keiner —</option>
          @foreach($mitarbeiter as $ma)
            @php
              $mid  = $ma->Mitarbeiter_id ?? $ma->mitarbeiter_id ?? $ma->getKey();
              $mlbl = trim(($ma->Nachname ?? '').' '.($ma->Vorname ?? ''));
            @endphp
            <option value="{{ $mid }}" @selected(old('mitarbeiter_id') == $mid)>{{ $mlbl ?: ('Mitarbeiter #'.$mid) }}</option>
          @endforeach
        </select>
        @error('mitarbeiter_id')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
      </div>

      {{-- Datum --}}
      <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Datum</label>
        <input type="date" name="datum"
               value="{{ old('datum') }}"
               class="w-full border rounded px-3 py-2" required>
        @error('datum')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
      </div>

      {{-- Dauer (h) --}}
      <div class="mb-4">
        <label class="block text-sm font-medium mb-1">Dauer (h)</label>
        <input type="number" name="dauer_h" step="0.25" min="0" max="24"
               value="{{ old('dauer_h') }}"
               class="w-full border rounded px-3 py-2">
        @error('dauer_h')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
      </div>

      {{-- Notizen --}}
      <div class="mb-6">
        <label class="block text-sm font-medium mb-1">Notizen</label>
        <textarea name="notizen" rows="5" class="w-full border rounded px-3 py-2">{{ old('notizen') }}</textarea>
        @error('notizen')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
      </div>

      <div class="flex items-center justify-end gap-2">
        <a href="{{ url()->previous() }}" class="px-3 py-2 rounded border text-sm">Abbrechen</a>
        <button class="px-4 py-2 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">Anlegen</button>
      </div>
    </form>
  </div>
</div>
@endsection
