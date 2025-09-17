@csrf
<div class="grid grid-cols-2 gap-4">
  <div>
    <label class="block text-sm mb-1">Name *</label>
    <input type="text" name="name" value="{{ old('name', $dokument->name ?? '') }}" class="border rounded w-full px-3 py-2" required>
  </div>
  <div>
    <label class="block text-sm mb-1">Slug *</label>
    <input type="text" name="slug" value="{{ old('slug', $dokument->slug ?? '') }}" class="border rounded w-full px-3 py-2" required>
  </div>
  <div class="col-span-2">
    <label class="block text-sm mb-1">Inhalt (HTML mit Platzhaltern)</label>
    <textarea name="body" rows="16" class="border rounded w-full px-3 py-2" required>{{ old('body', $dokument->body ?? '') }}</textarea>
    <p class="text-xs text-gray-500 mt-1">
      Verfügbare Platzhalter: {Anrede}, {Vorname}, {Nachname}, {Geburtsdatum}, {Heute}, {Ort}
    </p>
  </div>
  <div>
    <label class="inline-flex items-center gap-2">
      <input type="checkbox" name="is_active" value="1" {{ old('is_active', $dokument->is_active ?? true) ? 'checked' : '' }}>
      Aktiv
    </label>
  </div>
</div>
<div class="mt-4">
  <button class="px-4 py-2 bg-blue-600 text-white rounded">Speichern</button>
</div>
