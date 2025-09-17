@extends('layouts.app')
@section('title','Neuer Mitarbeiter')

@section('content')
<h2 class="text-2xl font-bold mb-4">Neuer Mitarbeiter</h2>

<form action="{{ route('mitarbeiter.store') }}" method="POST" class="bg-white p-6 rounded-xl shadow-sm max-w-2xl space-y-4">
  @csrf

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
      <input name="Nachname" value="{{ old('Nachname') }}" class="border rounded w-full px-3 py-2" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Vorname *</label>
      <input name="Vorname" value="{{ old('Vorname') }}" class="border rounded w-full px-3 py-2" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Tätigkeit *</label>
      <select name="Taetigkeit" class="border rounded w-full px-3 py-2" required>
        <option value=""></option>
        @foreach($taetigkeiten as $t)
          <option value="{{ $t }}" @selected(old('Taetigkeit')===$t)>{{ $t }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm mb-1">E-Mail *</label>
      <input type="email" name="Email" value="{{ old('Email') }}" class="border rounded w-full px-3 py-2" required>
    </div>
    <div class="col-span-2">
      <label class="block text-sm mb-1">Telefonnummer</label>
      <input name="Telefonnummer" value="{{ old('Telefonnummer') }}" class="border rounded w-full px-3 py-2">
    </div>
  </div>

  <div class="flex gap-2">
    <a href="{{ route('mitarbeiter.index') }}" class="px-4 py-2 border rounded">Abbrechen</a>
    <button class="px-4 py-2 bg-green-600 text-white rounded">Speichern</button>
  </div>
</form>
@endsection
