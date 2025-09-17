@extends('layouts.app')
@section('title','Anwesenheit')

@section('content')
<div class="flex items-center justify-between mb-6 gap-3">
  <h2 class="text-2xl font-bold">Anwesenheit</h2>

  <form method="GET" action="{{ route('anwesenheit.index') }}" class="flex flex-wrap items-end gap-2">
    <div>
      <label class="text-sm text-gray-600">Suche (TN)</label>
      <input name="q" value="{{ $q }}" class="border rounded px-3 py-2" placeholder="Name/Email">
    </div>
    <div>
      <label class="text-sm text-gray-600">Von</label>
      <input type="date" name="von" value="{{ $von }}" class="border rounded px-3 py-2">
    </div>
    <div>
      <label class="text-sm text-gray-600">Bis</label>
      <input type="date" name="bis" value="{{ $bis }}" class="border rounded px-3 py-2">
    </div>
    <button class="px-4 py-2 bg-gray-800 text-white rounded">Filtern</button>
    @if($q || $von || $bis)
      <a href="{{ route('anwesenheit.index') }}" class="px-3 py-2 border rounded">Reset</a>
    @endif
  </form>

  <a href="{{ route('anwesenheit.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">+ Neuer Eintrag</a>
</div>

@if(session('success'))
  <div class="mb-4 rounded border bg-green-50 text-green-800 px-3 py-2">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
  <table class="w-full text-left">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-3 py-2">Datum</th>
        <th class="px-3 py-2">Teilnehmer</th>
        <th class="px-3 py-2">Status</th>
        <th class="px-3 py-2">Fehlmin.</th>
        <th class="px-3 py-2">Aktionen</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $r)
        <tr class="border-t">
          <td class="px-3 py-2 whitespace-nowrap">{{ $r->datum?->format('d.m.Y') }}</td>
          <td class="px-3 py-2">
            @if($r->teilnehmer)
              <a href="{{ route('teilnehmer.show', $r->teilnehmer) }}" class="text-blue-600 hover:underline">
                {{ $r->teilnehmer->Nachname }}, {{ $r->teilnehmer->Vorname }}
              </a>
            @else — @endif
          </td>
          <td class="px-3 py-2">{{ $r->status }}</td>
          <td class="px-3 py-2">{{ $r->fehlminuten }}</td>
          <td class="px-3 py-2">
            <a href="{{ route('anwesenheit.edit', $r) }}" class="text-yellow-700 hover:underline">Bearbeiten</a>
            <span class="mx-1 text-gray-300">|</span>
            <form action="{{ route('anwesenheit.destroy', $r) }}" method="POST" class="inline" onsubmit="return confirm('Löschen?')">
              @csrf @method('DELETE')
              <button class="text-red-700 hover:underline">Löschen</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td class="px-3 py-6 text-gray-500" colspan="5">Keine Einträge.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $rows->links() }}</div>
@endsection
