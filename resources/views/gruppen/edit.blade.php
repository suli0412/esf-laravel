{{-- resources/views/gruppen/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Gruppe bearbeiten')

@section('content')
  <h2 class="text-2xl font-bold mb-4">Gruppe bearbeiten</h2>

<
<form action="{{ route('gruppen.update', ['gruppe' => $gruppe->gruppe_id]) }}" method="POST" class="space-y-4">
  @csrf
  @method('PUT')

    <div>
      <label class="block text-sm mb-1">Name *</label>
      <input type="text" name="name" value="{{ old('name', $gruppe->name) }}" class="border rounded px-3 py-2 w-full" required>
    </div>

    <div>
      <label class="block text-sm mb-1">Code</label>
      <input type="text" name="code" value="{{ old('code', $gruppe->code) }}" class="border rounded px-3 py-2 w-full">
    </div>

    <div>
      <label class="block text-sm mb-1">Projekt</label>
      <select name="projekt_id" class="border rounded px-3 py-2 w-full">
        <option value="">—</option>
        @foreach($projekte as $p)
          <option value="{{ $p->projekt_id }}" @selected(old('projekt_id', $gruppe->projekt_id)==$p->projekt_id)>
            {{ $p->bezeichnung }}
          </option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="block text-sm mb-1">Standard-Mitarbeiter</label>
      <select name="standard_mitarbeiter_id" class="border rounded px-3 py-2 w-full">
        <option value="">—</option>
        @foreach($mitarbeiter as $m)
          <option value="{{ $m->Mitarbeiter_id }}" @selected(old('standard_mitarbeiter_id', $gruppe->standard_mitarbeiter_id)==$m->Mitarbeiter_id)>
            {{ $m->Nachname }}, {{ $m->Vorname }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="flex items-center gap-2">
      <input id="aktiv" type="checkbox" name="aktiv" value="1" @checked(old('aktiv', (int)$gruppe->aktiv)===1)>
      <label for="aktiv">Aktiv</label>
    </div>

    <div class="flex gap-2">
      <button class="px-3 py-2 bg-blue-600 text-white rounded">Speichern</button>
      <a href="{{ route('gruppen.index') }}" class="px-4 py-2 border rounded">Abbrechen</a>
    </div>
  </form>
@endsection
