@extends('layouts.app')
@section('title','Neue Gruppe')

@section('content')
  <div class="max-w-xl">
    <h2 class="text-2xl font-bold mb-4">Neue Gruppe anlegen</h2>

    {{-- Validierungs-/Fehlermeldungen --}}
    @if ($errors->any())
      <div class="mb-4 rounded border border-red-200 bg-red-50 text-red-800 px-3 py-2 text-sm">
        <ul class="list-disc list-inside">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('gruppen.store') }}" class="space-y-4">
      @csrf

      <div>
        <label class="block text-sm font-medium mb-1">Name <span class="text-red-600">*</span></label>
        <input name="name" value="{{ old('name') }}" required
               class="w-full border rounded px-3 py-2" placeholder="z. B. ESF Basisgruppe A">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Code (optional)</label>
        <input name="code" value="{{ old('code') }}"
               class="w-full border rounded px-3 py-2" placeholder="z. B. BGA-2025">
        <p class="text-xs text-gray-500 mt-1">Muss eindeutig sein, wenn gesetzt.</p>
      </div>

      @php
        // Falls die Spalte 'aktiv' vorhanden ist, Checkbox anzeigen
        $hasAktiv = \Illuminate\Support\Facades\Schema::hasColumn('gruppen','aktiv');
      @endphp
      @if($hasAktiv)
        <div class="flex items-center gap-2">
          <input id="aktiv" type="checkbox" name="aktiv" value="1" class="h-4 w-4"
                 {{ old('aktiv', true) ? 'checked' : '' }}>
          <label for="aktiv" class="text-sm">Aktiv</label>
        </div>
      @endif

      <div class="flex items-center gap-2 pt-2">
        <a href="{{ route('gruppen.index') }}" class="px-3 py-2 border rounded hover:bg-gray-50">Abbrechen</a>
        <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Speichern</button>
      </div>
    </form>
  </div>
@endsection
