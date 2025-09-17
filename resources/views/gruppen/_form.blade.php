<div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
  <div class="grid grid-cols-2 gap-4">
    <div>
      <label class="block text-sm mb-1">Name *</label>
      <input type="text" name="name" value="{{ old('name', $gruppe->name) }}" required class="border rounded w-full px-3 py-2">
      @error('name') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
    </div>
    <div>
      <label class="block text-sm mb-1">Code</label>
      <input type="text" name="code" value="{{ old('code', $gruppe->code) }}" class="border rounded w-full px-3 py-2">
      @error('code') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
    </div>

    <div>
      <label class="block text-sm mb-1">Projekt</label>
      <select name="projekt_id" class="border rounded w-full px-3 py-2">
        <option value=""></option>
        @foreach($projekte as $p)
          <option value="{{ $p->projekt_id }}" @selected(old('projekt_id', $gruppe->projekt_id)==$p->projekt_id)>
            {{ $p->bezeichnung }}
          </option>
        @endforeach
      </select>
      @error('projekt_id') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
    </div>

    <div>
      <label class="block text-sm mb-1">Standard-Mitarbeiter*in</label>
      <select name="standard_mitarbeiter_id" class="border rounded w-full px-3 py-2">
        <option value=""></option>
        @foreach($mitarbeiter as $m)
          <option value="{{ $m->Mitarbeiter_id }}" @selected(old('standard_mitarbeiter_id', $gruppe->standard_mitarbeiter_id)==$m->Mitarbeiter_id)>
            {{ $m->Nachname }}, {{ $m->Vorname }}
          </option>
        @endforeach
      </select>
      @error('standard_mitarbeiter_id') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
    </div>
  </div>

  <label class="inline-flex items-center gap-2">
    <input type="checkbox" name="aktiv" value="1" @checked(old('aktiv', $gruppe->aktiv))>
    <span>Aktiv</span>
  </label>

  <div class="pt-2">
    <button class="px-4 py-2 bg-blue-600 text-white rounded">Speichern</button>
    <a href="{{ route('gruppen.index') }}" class="ml-2 px-4 py-2 border rounded">Zurück</a>
  </div>
</div>
