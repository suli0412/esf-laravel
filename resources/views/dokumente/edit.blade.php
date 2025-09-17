@extends('layouts.app')
@section('title', $dokument->exists ? 'Vorlage bearbeiten' : 'Vorlage anlegen')

@section('content')
  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">
      {{ $dokument->exists ? 'Vorlage bearbeiten' : 'Vorlage anlegen' }}
    </h2>

    @if($dokument->exists)
      <form action="{{ route('dokumente.destroy', $dokument) }}" method="POST"
            onsubmit="return confirm('Vorlage wirklich löschen?');">
        @csrf
        @method('DELETE')
        <button class="px-3 py-2 border rounded text-red-600">Löschen</button>
      </form>
    @endif
  </div>

  @if ($errors->any())
    <div class="mb-4 rounded border bg-red-50 text-red-800 px-3 py-2">
      <ul class="list-disc pl-5">
        @foreach ($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  @if(session('success'))
    <div class="mb-4 rounded border bg-green-50 text-green-800 px-3 py-2">
      {{ session('success') }}
    </div>
  @endif

  <form method="POST"
        action="{{ $dokument->exists ? route('dokumente.update', $dokument) : route('dokumente.store') }}"
        class="bg-white rounded-xl shadow-sm p-4 space-y-4">
    @csrf
    @if($dokument->exists)
      @method('PUT')
    @endif

    <div>
      <label class="block text-sm mb-1">Name *</label>
      <input type="text" name="name" value="{{ old('name', $dokument->name) }}"
             class="border rounded w-full px-3 py-2" required>
    </div>

    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="block text-sm mb-1">Slug (optional)</label>
        <input type="text" name="slug" value="{{ old('slug', $dokument->slug) }}"
               class="border rounded w-full px-3 py-2" placeholder="bestaetigung">
        <p class="text-xs text-gray-500 mt-1">If left empty, it will be generated from the Name.</p>
      </div>
      <div class="flex items-end">
        <label class="inline-flex items-center gap-2">
          <input type="checkbox" name="is_active" value="1"
                 {{ old('is_active', (int)$dokument->is_active) ? 'checked' : '' }}>
          Aktiv
        </label>
      </div>
    </div>

    <div>
      <label class="block text-sm mb-1">Body (HTML) *</label>
      <textarea name="body" rows="16" class="border rounded w-full px-3 py-2"
                placeholder="HTML mit Platzhaltern wie {Vorname}, {Nachname}, {Projekt}">{{ old('body', $dokument->body) }}</textarea>
      <p class="text-xs text-gray-500 mt-1">
        Use placehoders: {Anrede}, {Vorname}, {Nachname}, {Geburtsdatum},
        {Projekt}, {ProjektBeginn}, {ProjektEnde}, {MitarbeiterVorname}, {MitarbeiterNachname}, {Ort}, {Heute}, {Logo}, {Foto} …
      </p>
    </div>

    <div class="flex gap-2">
      <button class="px-4 py-2 bg-blue-600 text-white rounded">
        {{ $dokument->exists ? 'Speichern' : 'Anlegen' }}
      </button>
      <a href="{{ route('dokumente.index') }}" class="px-4 py-2 border rounded">Zurück</a>
    </div>
  </form>
@endsection
