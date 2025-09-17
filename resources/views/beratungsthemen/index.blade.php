@extends('layouts.app')
@section('title','Beratungsthemen')
@section('content')
<div class="flex items-center justify-between mb-6">
  <h2 class="text-2xl font-bold">Beratungsthemen</h2>
  <a href="{{ route('beratungsthemen.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">Neu</a>
</div>

@if(session('success'))
  <div class="mb-4 border bg-green-50 text-green-800 px-3 py-2 rounded">{{ session('success') }}</div>
@endif

<div class="bg-white rounded shadow-sm overflow-hidden">
  <table class="w-full text-left">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-3 py-2">ID</th>
        <th class="px-3 py-2">Bezeichnung</th>
        <th class="px-3 py-2">Beschreibung</th>
        <th class="px-3 py-2">Aktionen</th>
      </tr>
    </thead>
    <tbody>
      @forelse($items as $it)
      <tr class="border-t">
        <td class="px-3 py-2">{{ $it->Thema_id }}</td>
        <td class="px-3 py-2">{{ $it->Bezeichnung }}</td>
        <td class="px-3 py-2">{{ \Illuminate\Support\Str::limit($it->Beschreibung, 80) }}</td>
        <td class="px-3 py-2">
          <a href="{{ route('beratungsthemen.edit',$it) }}" class="text-blue-600">Bearbeiten</a>
          <form action="{{ route('beratungsthemen.destroy',$it) }}" method="POST" class="inline" onsubmit="return confirm('Löschen?')">
            @csrf @method('DELETE')
            <button class="text-red-700 ml-2">Löschen</button>
          </form>
        </td>
      </tr>
      @empty
      <tr><td colspan="4" class="px-3 py-6 text-gray-500">Keine Einträge.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $items->links() }}</div>
@endsection
