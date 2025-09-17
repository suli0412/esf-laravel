{{-- resources/views/projekte/index.blade.php --}}
@extends('layouts.app')
@section('title','Projekte')

@section('content')
<div class="flex items-center justify-between mb-6 gap-3">
  <h2 class="text-2xl font-bold">Projekte</h2>
  <a href="{{ route('projekte.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">
    + Neues Projekt
  </a>
</div>

@if(session('success'))
  <div class="mb-4 rounded border bg-green-50 text-green-800 px-3 py-2">
    {{ session('success') }}
  </div>
@endif

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
  <table class="w-full text-left">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-3 py-2">Code</th>
        <th class="px-3 py-2">Bezeichnung</th>
        <th class="px-3 py-2">Start</th>
        <th class="px-3 py-2">Ende</th>
        <th class="px-3 py-2">Status</th>
        <th class="px-3 py-2">Aktionen</th>
      </tr>
    </thead>
    <tbody>
      @forelse($rows as $p)
        <tr class="border-t">
          <td class="px-3 py-2 whitespace-nowrap">{{ $p->code }}</td>
          <td class="px-3 py-2">
            <a href="{{ route('projekte.show', $p) }}" class="text-blue-600 hover:underline">
              {{ $p->bezeichnung }}
            </a>
          </td>
          <td class="px-3 py-2 whitespace-nowrap">{{ optional($p->start)->format('d.m.Y') }}</td>
          <td class="px-3 py-2 whitespace-nowrap">{{ optional($p->ende)->format('d.m.Y') }}</td>
          <td class="px-3 py-2">
            @if($p->aktiv)
              <span class="px-2 py-1 rounded bg-green-100 text-green-700 text-xs">aktiv</span>
            @else
              <span class="px-2 py-1 rounded bg-gray-100 text-gray-700 text-xs">inaktiv</span>
            @endif
          </td>
          <td class="px-3 py-2">
            <a href="{{ route('projekte.show', $p) }}" class="text-blue-600 hover:underline">Öffnen</a>
            <span class="mx-1 text-gray-300">|</span>
            <a href="{{ route('projekte.edit', $p) }}" class="text-yellow-700 hover:underline">Bearbeiten</a>
            <span class="mx-1 text-gray-300">|</span>
            <form action="{{ route('projekte.destroy', $p) }}" method="POST" class="inline"
                  onsubmit="return confirm('Löschen?')">
              @csrf @method('DELETE')
              <button class="text-red-700 hover:underline">Löschen</button>
            </form>
          </td>
        </tr>
      @empty
        <tr>
          <td class="px-3 py-6 text-gray-500" colspan="6">Keine Einträge.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $rows->links() }}</div>
@endsection
