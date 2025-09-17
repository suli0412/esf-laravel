{{-- edit --}}
@extends('layouts.app')
@section('title','Thema bearbeiten')
@section('content')
<h2 class="text-2xl font-bold mb-4">Thema bearbeiten</h2>
<form action="{{ route('beratungsthemen.update',$item) }}" method="POST" class="bg-white p-6 rounded shadow-sm max-w-2xl">
  @csrf @method('PUT')
  <label class="block text-sm mb-1">Bezeichnung</label>
  <input name="Bezeichnung" value="{{ old('Bezeichnung',$item->Bezeichnung) }}" class="border rounded w-full px-3 py-2 mb-3">
  <label class="block text-sm mb-1">Beschreibung</label>
  <textarea name="Beschreibung" rows="4" class="border rounded w-full px-3 py-2 mb-4">{{ old('Beschreibung',$item->Beschreibung) }}</textarea>
  <div class="flex gap-2">
    <a href="{{ route('beratungsthemen.index') }}" class="px-4 py-2 border rounded">Abbrechen</a>
    <button class="px-4 py-2 bg-green-600 text-white rounded">Aktualisieren</button>
  </div>
</form>
@endsection
