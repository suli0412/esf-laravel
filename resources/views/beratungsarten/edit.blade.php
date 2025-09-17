@extends('layouts.app')
@section('title','Beratungsart bearbeiten')
@section('content')
<h2 class="text-2xl font-bold mb-4">Beratungsart bearbeiten</h2>
<form action="{{ route('beratungsarten.update',$item) }}" method="POST" class="bg-white p-6 rounded shadow-sm max-w-lg">
  @csrf @method('PUT')
  <label class="block text-sm mb-1">Code (3 Zeichen)</label>
  <input name="Code" value="{{ old('Code',$item->Code) }}" class="border rounded w-full px-3 py-2 mb-3">
  <label class="block text-sm mb-1">Bezeichnung</label>
  <input name="Bezeichnung" value="{{ old('Bezeichnung',$item->Bezeichnung) }}" class="border rounded w-full px-3 py-2 mb-4">
  @error('Code')<div class="text-red-700 text-sm mb-2">{{ $message }}</div>@enderror
  @error('Bezeichnung')<div class="text-red-700 text-sm mb-2">{{ $message }}</div>@enderror
  <div class="flex gap-2">
    <a href="{{ route('beratungsarten.index') }}" class="px-4 py-2 border rounded">Abbrechen</a>
    <button class="px-4 py-2 bg-green-600 text-white rounded">Aktualisieren</button>
  </div>
</form>
@endsection
