@php
  $isEdit = ($mode ?? '') === 'edit';
  $tnId   = old('teilnehmer_id', $beratung->teilnehmer_id ?? ($teilnehmer->Teilnehmer_id ?? null));
@endphp

@php
  $isEdit = ($mode ?? '') === 'edit';
@endphp

<form method="POST" action="{{ $action }}">
  @csrf
  @if($isEdit) @method('PUT') @endif
  {{-- ... Rest deines Formulars ... --}}
</form>


<form method="POST" action="{{ $action }}">
  @csrf
  @if($isEdit) @method('PUT') @endif

  {{-- Teilnehmer (vorgewählt, falls via ?teilnehmer=... aufgerufen) --}}
  <div class="mb-4">
    <label class="block text-sm font-medium mb-1">Teilnehmer</label>
    @if(isset($teilnehmer) && $teilnehmer)
      <div class="px-3 py-2 border rounded bg-gray-50">
        {{ $teilnehmer->Nachname }} {{ $teilnehmer->Vorname }}
      </div>
      <input type="hidden" name="teilnehmer_id" value="{{ $tnId }}">
    @else
      <input type="number" name="teilnehmer_id" value="{{ $tnId }}"
             class="w-full border rounded px-3 py-2" required>
      <p class="text-xs text-gray-500 mt-1">Teilnehmer-ID eingeben (oder Formular über Teilnehmerseite öffnen).</p>
    @endif
    @error('teilnehmer_id')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
  </div>

  {{-- Art --}}
  <div class="mb-4">
    <label class="block text-sm font-medium mb-1">Art</label>
    <select name="art_id" class="w-full border rounded px-3 py-2" required>
      <option value="">– bitte wählen –</option>
      @foreach($arten as $art)
        @php
          $val = $art->Art_id ?? $art->art_id ?? $art->getKey();
          $label = $art->Bezeichnung ?? $art->bezeichnung ?? $art->Code ?? ('Art #' . $val);
        @endphp
        <option value="{{ $val }}" @selected(old('art_id', $beratung->art_id ?? null) == $val)>
          {{ $label }}
        </option>
      @endforeach
    </select>
    @error('art_id')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
  </div>

  {{-- Thema --}}
  <div class="mb-4">
    <label class="block text-sm font-medium mb-1">Thema</label>
    <select name="thema_id" class="w-full border rounded px-3 py-2" required>
      <option value="">– bitte wählen –</option>
      @foreach($themen as $thema)
        @php
          $val = $thema->Thema_id ?? $thema->thema_id ?? $thema->getKey();
          $label = $thema->Bezeichnung ?? $thema->bezeichnung ?? ('Thema #' . $val);
        @endphp
        <option value="{{ $val }}" @selected(old('thema_id', $beratung->thema_id ?? null) == $val)>
          {{ $label }}
        </option>
      @endforeach
    </select>
    @error('thema_id')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
  </div>

  {{-- Mitarbeiter (optional) --}}
  <div class="mb-4">
    <label class="block text-sm font-medium mb-1">Mitarbeiter (optional)</label>
    <select name="mitarbeiter_id" class="w-full border rounded px-3 py-2">
      <option value="">– keiner –</option>
      @foreach($mitarbeiter as $ma)
        @php
          $val = $ma->Mitarbeiter_id ?? $ma->mitarbeiter_id ?? $ma->getKey();
          $label = trim(($ma->Nachname ?? '').' '.($ma->Vorname ?? ''));
        @endphp
        <option value="{{ $val }}" @selected(old('mitarbeiter_id', $beratung->mitarbeiter_id ?? null) == $val)>
          {{ $label ?: ('Mitarbeiter #' . $val) }}
        </option>
      @endforeach
    </select>
    @error('mitarbeiter_id')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
  </div>

  {{-- Datum --}}
  <div class="mb-4">
    <label class="block text-sm font-medium mb-1">Datum</label>
    <input type="date" name="datum"
           value="{{ old('datum', isset($beratung->datum) ? \Illuminate\Support\Carbon::parse($beratung->datum)->format('Y-m-d') : '') }}"
           class="w-full border rounded px-3 py-2" required>
    @error('datum')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
  </div>

  {{-- Dauer (h) --}}
  <div class="mb-4">
    <label class="block text-sm font-medium mb-1">Dauer (h)</label>
    <input type="number" name="dauer_h" step="0.25" min="0" max="24"
           value="{{ old('dauer_h', $beratung->dauer_h ?? '') }}"
           class="w-full border rounded px-3 py-2">
    @error('dauer_h')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
  </div>

  {{-- Notizen --}}
  <div class="mb-6">
    <label class="block text-sm font-medium mb-1">Notizen</label>
    <textarea name="notizen" rows="5" class="w-full border rounded px-3 py-2">{{ old('notizen', $beratung->notizen ?? '') }}</textarea>
    @error('notizen')<div class="text-sm text-red-600 mt-1">{{ $message }}</div>@enderror
  </div>

  <div class="flex items-center justify-end gap-2">
    <a href="{{ isset($tnId) ? route('teilnehmer.show', $tnId) : url()->previous() }}"
       class="px-3 py-2 rounded border text-sm">Abbrechen</a>

    <button class="px-4 py-2 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">
      {{ $isEdit ? 'Speichern' : 'Anlegen' }}
    </button>
  </div>
</form>
