@extends('layouts.app')
@section('title','Mitarbeiter bearbeiten')

@section('content')
<h2 class="text-2xl font-bold mb-4">Mitarbeiter bearbeiten</h2>

<form action="{{ route('mitarbeiter.update', $mitarbeiter) }}" method="POST" class="bg-white p-6 rounded-xl shadow-sm max-w-2xl space-y-4">
  @csrf
  @method('PUT')

  @if($errors->any())
    <div class="rounded border border-red-200 bg-red-50 text-red-800 px-3 py-2">
      <ul class="list-disc list-inside text-sm">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
      </ul>
    </div>
  @endif

  <div class="grid grid-cols-2 gap-4">
    <div>
      <label class="block text-sm mb-1">Nachname *</label>
      <input name="Nachname" value="{{ old('Nachname',$mitarbeiter->Nachname) }}" class="border rounded w-full px-3 py-2" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Vorname *</label>
      <input name="Vorname" value="{{ old('Vorname',$mitarbeiter->Vorname) }}" class="border rounded w-full px-3 py-2" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Tätigkeit *</label>
      <select name="Taetigkeit" class="border rounded w-full px-3 py-2" required>
        @php
          $taetigkeiten = $taetigkeiten ?? ['Leitung','Verwaltung','Beratung','Bildung','Teamleitung','Praktikant','Andere'];
        @endphp
        @foreach($taetigkeiten as $t)
          <option value="{{ $t }}" @selected(old('Taetigkeit',$mitarbeiter->Taetigkeit)===$t)>{{ $t }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm mb-1">E-Mail *</label>
      <input type="email" name="Email" value="{{ old('Email',$mitarbeiter->Email) }}" class="border rounded w-full px-3 py-2" required>
    </div>
    <div class="col-span-2">
      <label class="block text-sm mb-1">Telefonnummer</label>
      <input name="Telefonnummer" value="{{ old('Telefonnummer',$mitarbeiter->Telefonnummer) }}" class="border rounded w-full px-3 py-2">
    </div>
  </div>

  <div class="flex gap-2">
    <a href="{{ route('mitarbeiter.show', $mitarbeiter) }}" class="px-4 py-2 border rounded">Abbrechen</a>
    <button class="px-4 py-2 bg-green-600 text-white rounded">Aktualisieren</button>
  </div>
</form>
@endsection
