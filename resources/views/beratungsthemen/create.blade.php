{{-- create --}}
@extends('layouts.app')
@section('title','Neues Thema')
@section('content')
<h2 class="text-2xl font-bold mb-4">Neues Thema</h2>
<form action="{{ route('beratungsthemen.store') }}" method="POST" class="bg-white p-6 rounded shadow-sm max-w-2xl">
  @csrf
  <label class="block text-sm mb-1">Bezeichnung</label>
  <input name="Bezeichnung" value="{{ old('Bezeichnung') }}" class="border rounded w-full px-3 py-2 mb-3">
  <label class="block text-sm mb-1">Beschreibung</label>
  <textarea name="Beschreibung" rows="4" class="border rounded w-full px-3 py-2 mb-4">{{ old('Beschreibung') }}</textarea>
  <div class="flex gap-2">
    <a href="{{ route('beratungsthemen.index') }}" class="px-4 py-2 border rounded">Abbrechen</a>
    <button class="px-4 py-2 bg-green-600 text-white rounded">Speichern</button>
  </div>
</form>
@endsection
