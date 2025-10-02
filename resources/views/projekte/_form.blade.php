{{-- Validierungsfehler wie zuvor ... --}}

<div class="grid grid-cols-2 gap-4">
  <div>
    <label for="code" class="block text-sm font-medium mb-1">Code *</label>
    <input id="code" name="code" value="{{ old('code', $projekt->code ?? '') }}"
           class="border rounded-lg w-full px-3 py-2 focus:ring-blue-500 focus:border-blue-500" required>
  </div>

  <div>
    <label for="bezeichnung" class="block text-sm font-medium mb-1">Bezeichnung *</label>
    <input id="bezeichnung" name="bezeichnung" value="{{ old('bezeichnung', $projekt->bezeichnung ?? '') }}"
           class="border rounded-lg w-full px-3 py-2 focus:ring-blue-500 focus:border-blue-500" required>
  </div>

  <div class="col-span-2">
    <label for="beschreibung" class="block text-sm font-medium mb-1">Kurze Beschreibung</label>
    <textarea id="beschreibung" name="beschreibung" rows="2"
              class="border rounded-lg w-full px-3 py-2 focus:ring-blue-500 focus:border-blue-500">{{ old('beschreibung', $projekt->beschreibung ?? '') }}</textarea>
  </div>

  <div class="col-span-2">
    <label for="inhalte" class="block text-sm font-medium mb-1">Inhalte</label>
    <textarea id="inhalte" name="inhalte" rows="4"
              class="border rounded-lg w-full px-3 py-2 focus:ring-blue-500 focus:border-blue-500">{{ old('inhalte', $projekt->inhalte ?? '') }}</textarea>
  </div>

  <div>
    <label for="start" class="block text-sm font-medium mb-1">Start</label>
    <input id="start" type="date" name="start"
           value="{{ old('start', optional($projekt->start ?? null)->toDateString()) }}"
           class="border rounded-lg w-full px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
  </div>

  <div>
    <label for="ende" class="block text-sm font-medium mb-1">
      Ende <span class="text-gray-400 text-xs">(leer = +1 Jahr ab Start)</span>
    </label>
    <input id="ende" type="date" name="ende"
           value="{{ old('ende', optional($projekt->ende ?? null)->toDateString()) }}"
           class="border rounded-lg w-full px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
  </div>

  <div>
    <label for="verantwortlicher_id" class="block text-sm font-medium mb-1">Verantwortlicher Mitarbeiter</label>
    <select id="verantwortlicher_id" name="verantwortlicher_id"
            class="border rounded-lg w-full px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
      <option value="">— keiner —</option>
      @foreach(($mitarbeiter ?? []) as $m)
        <option value="{{ $m->Mitarbeiter_id }}"
          @selected(old('verantwortlicher_id', $projekt->verantwortlicher_id ?? '') == $m->Mitarbeiter_id)>
          {{ $m->Nachname }} {{ $m->Vorname }}
        </option>
      @endforeach
    </select>
  </div>

  <div class="col-span-2">
    <label class="inline-flex items-center gap-2">
      <input type="hidden" name="aktiv" value="0">
      <input type="checkbox" name="aktiv" value="1" @checked(old('aktiv', ($projekt->aktiv ?? true)))>
      <span class="text-sm">Aktiv</span>
    </label>
  </div>
</div>
