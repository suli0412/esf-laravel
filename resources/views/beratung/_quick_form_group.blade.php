@php
  // Безопасные значения по умолчанию
  $arten           = $arten           ?? collect();
  $themen          = $themen          ?? collect();
  $teilnehmerList  = $teilnehmerList  ?? collect();
  $mitarbeiterList = $mitarbeiterList ?? collect();
@endphp

<form action="{{ route('gruppen_beratungen.store') }}" method="POST" class="bg-white rounded-xl shadow-sm p-4 space-y-3">
  @csrf

  <div class="grid grid-cols-12 gap-3">
    {{-- Art --}}
    <div class="col-span-3">
      <label class="block text-sm mb-1">Art *</label>
      <select name="art_id" class="border rounded w-full px-2 py-2" required>
        <option value=""></option>
        @foreach($arten as $a)
          <option value="{{ $a->Art_id }}">{{ $a->Code }} — {{ $a->Bezeichnung }}</option>
        @endforeach
      </select>
    </div>

    {{-- Thema (Katalog, optional) --}}
    <div class="col-span-4">
      <label class="block text-sm mb-1">Thema (Katalog)</label>
      <select name="thema_id" class="border rounded w-full px-2 py-2">
        <option value=""></option>
        @foreach($themen as $th)
          <option value="{{ $th->Thema_id }}">{{ $th->Bezeichnung }}</option>
        @endforeach
      </select>
    </div>

    {{-- Datum --}}
    <div class="col-span-3">
      <label class="block text-sm mb-1">Datum *</label>
      <input type="date" name="datum" value="{{ now()->toDateString() }}" class="border rounded w-full px-2 py-2" required>
    </div>

    {{-- Dauer --}}
    <div class="col-span-2">
      <label class="block text-sm mb-1">Dauer (h)</label>
      <input type="number" step="0.25" min="0" max="24" name="dauer_h" class="border rounded w-full px-2 py-2" placeholder="z.B. 1.5">
    </div>

    {{-- Mitarbeiter: если $mitarbeiter задан — скрытое поле, иначе выпадающий список --}}
    @if(!empty($mitarbeiter))
      <input type="hidden" name="mitarbeiter_id" value="{{ $mitarbeiter->Mitarbeiter_id }}">
      <div class="col-span-12 text-sm text-gray-600">
        Verantwortlich: <strong>{{ $mitarbeiter->Nachname }}, {{ $mitarbeiter->Vorname }}</strong>
      </div>
    @else
      <div class="col-span-4">
        <label class="block text-sm mb-1">Mitarbeiter *</label>
        <select name="mitarbeiter_id" class="border rounded w-full px-2 py-2" required>
          <option value=""></option>
          @foreach($mitarbeiterList as $m)
            <option value="{{ $m->Mitarbeiter_id }}">{{ $m->Nachname }}, {{ $m->Vorname }}</option>
          @endforeach
        </select>
      </div>
    @endif

    {{-- Свободное поле "Thema" и "Inhalt" для групповых --}}
    <div class="col-span-4">
      <label class="block text-sm mb-1">Thema (frei)</label>
      <input type="text" name="thema" class="border rounded w-full px-2 py-2" placeholder="Optionaler Titel/Thema">
    </div>

    <div class="col-span-12">
      <label class="block text-sm mb-1">Inhalt</label>
      <textarea name="inhalt" rows="3" class="border rounded w-full px-3 py-2" placeholder="Kurzbeschreibung der Gruppenberatung"></textarea>
    </div>

    {{-- Teilnehmer*innen Mehrfachwahl --}}
    <div class="col-span-12">
      <label class="block text-sm mb-1">Teilnehmer*innen *</label>
      <select name="teilnehmer_ids[]" multiple required class="border rounded w-full px-2 py-2 h-40">
        @foreach($teilnehmerList as $t)
          <option value="{{ $t->Teilnehmer_id }}">{{ $t->Nachname }}, {{ $t->Vorname }}</option>
        @endforeach
      </select>
      <p class="text-xs text-gray-500 mt-1">Tipp: Strg/Cmd oder Shift zum Mehrfachauswählen.</p>
    </div>

    {{-- Unterlagen --}}
    <div class="col-span-12">
      <label class="inline-flex items-center gap-2 text-sm">
        <input type="checkbox" name="TNUnterlagen" value="1" class="rounded">
        Teilnehmer*innen-Unterlagen vorhanden
      </label>
    </div>
  </div>

  <div class="pt-2">
    <button class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Speichern</button>
  </div>
</form>
