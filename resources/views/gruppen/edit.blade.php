@extends('layouts.app')
@section('title','Gruppe bearbeiten')

@section('content')
  <div class="max-w-2xl">
    <h2 class="text-2xl font-bold mb-4">Gruppe bearbeiten</h2>

    {{-- Meldungen --}}
    @if(session('success'))
      <div class="mb-3 rounded border bg-green-50 text-green-800 px-3 py-2">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
      <div class="mb-4 rounded border border-red-200 bg-red-50 text-red-800 px-3 py-2 text-sm">
        <ul class="list-disc list-inside">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('gruppen.update', $gruppe) }}" class="space-y-4">
      @csrf
      @method('PUT')

      <div>
        <label class="block text-sm font-medium mb-1">Name <span class="text-red-600">*</span></label>
        <input name="name" required
               class="w-full border rounded px-3 py-2"
               value="{{ old('name', $gruppe->name) }}">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Code (optional)</label>
        <input name="code"
               class="w-full border rounded px-3 py-2"
               value="{{ old('code', $gruppe->code) }}">
        <p class="text-xs text-gray-500 mt-1">Muss eindeutig sein, wenn gesetzt.</p>
      </div>

      {{-- Projekt (optional) --}}
      @if(isset($projekte) && $projekte->count())
        <div>
          <label class="block text-sm font-medium mb-1">Projekt (optional)</label>
          <select name="projekt_id" class="w-full border rounded px-3 py-2">
            <option value="">— kein Projekt —</option>
            @foreach($projekte as $p)
              <option value="{{ $p->projekt_id }}"
                @selected((int)old('projekt_id', (int)$gruppe->projekt_id) === (int)$p->projekt_id)>
                {{ $p->bezeichnung }}
              </option>
            @endforeach
          </select>
        </div>
      @endif

      {{-- Standard-Mitarbeiter (optional) --}}
      @if(isset($mitarbeiter) && $mitarbeiter->count())
        <div>
          <label class="block text-sm font-medium mb-1">Standard-Mitarbeiter (optional)</label>
          <select name="standard_mitarbeiter_id" class="w-full border rounded px-3 py-2">
            <option value="">— keiner —</option>
            @foreach($mitarbeiter as $m)
              <option value="{{ $m->Mitarbeiter_id }}"
                @selected((int)old('standard_mitarbeiter_id', (int)$gruppe->standard_mitarbeiter_id) === (int)$m->Mitarbeiter_id)>
                {{ $m->Nachname }}, {{ $m->Vorname }}
              </option>
            @endforeach
          </select>
        </div>
      @endif

      {{-- Aktiv-Checkbox nur anzeigen, wenn Spalte existiert --}}
      @php $hasAktiv = isset($gruppe->aktiv); @endphp
      @if($hasAktiv)
        <div class="flex items-center gap-2">
          <input id="aktiv" type="checkbox" name="aktiv" value="1" class="h-4 w-4"
                 {{ old('aktiv', (bool)$gruppe->aktiv) ? 'checked' : '' }}>
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
