@extends('layouts.app')
@section('title','Projekt · '.$projekt->code)

@section('content')
@if(session('success'))
  <div class="mb-4 rounded border bg-green-50 text-green-800 px-3 py-2">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl shadow-sm p-4 mb-6">
  <h2 class="text-xl font-semibold mb-2">Projekt</h2>
  <div class="grid grid-cols-2 gap-4 text-sm">
    <div><span class="text-gray-500">Code:</span> {{ $projekt->code }}</div>
    <div><span class="text-gray-500">Bezeichnung:</span> {{ $projekt->bezeichnung }}</div>
    <div><span class="text-gray-500">Start:</span> {{ $projekt->start?->format('d.m.Y') ?? '—' }}</div>
    <div><span class="text-gray-500">Ende:</span> {{ $projekt->ende?->format('d.m.Y') ?? '—' }}</div>
    <div><span class="text-gray-500">Status:</span> {{ $projekt->aktiv ? 'Aktiv' : 'Inaktiv' }}</div>
  </div>
  <div class="mt-4 flex gap-2">
    <a href="{{ route('projekte.index') }}" class="px-3 py-2 border rounded">Zurück</a>
    <a href="{{ route('projekte.edit', $projekt) }}" class="px-3 py-2 border rounded">Bearbeiten</a>
  </div>
</div>

{{-- Здесь позже можно вывести историю Teilnehmer_Projekt --}}
@endsection
