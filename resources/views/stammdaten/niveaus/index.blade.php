@extends('layouts.app')
@section('title','Niveaus')

@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-2xl font-bold">Niveaus</h1>
  <a href="{{ route('niveaus.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded">Neu</a>
</div>

<table class="w-full text-sm">
  <thead class="bg-gray-50">
    <tr>
      <th class="px-3 py-2 text-left">Sort</th>
      <th class="px-3 py-2 text-left">Code</th>
      <th class="px-3 py-2 text-left">Label</th>
      <th class="px-3 py-2"></th>
    </tr>
  </thead>
  <tbody>
    @forelse($rows as $r)
      <tr class="border-t">
        <td class="px-3 py-2">{{ $r->sort_order }}</td>
        <td class="px-3 py-2">{{ $r->code }}</td>
        <td class="px-3 py-2">{{ $r->label }}</td>
        <td class="px-3 py-2 text-right">
          <a href="{{ route('niveaus.edit',$r) }}" class="text-blue-600 hover:underline mr-3">Bearbeiten</a>
          <form action="{{ route('niveaus.destroy',$r) }}" method="POST" class="inline" onsubmit="return confirm('Löschen?')">
            @csrf @method('DELETE')
            <button class="text-red-600 hover:underline">Löschen</button>
          </form>
        </td>
      </tr>
    @empty
      <tr><td colspan="4" class="px-3 py-4 text-gray-500">Keine Einträge.</td></tr>
    @endforelse
  </tbody>
</table>
@endsection
