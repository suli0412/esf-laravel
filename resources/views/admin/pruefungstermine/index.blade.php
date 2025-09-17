@extends('layouts.app')
@section('title','Prüfungstermine')
@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-2xl font-bold">Prüfungstermine</h1>
  <a href="{{ route('admin.pruefungstermine.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">Neu</a>
</div>

@if(session('success'))
  <div class="mb-3 text-green-700">{{ session('success') }}</div>
@endif

@if($termine->isEmpty())
  <p class="text-gray-500">Keine Prüfungstermine.</p>
@else
<table class="w-full text-sm">
  <thead class="bg-gray-50">
    <tr>
      <th class="px-3 py-2 text-left">Datum</th>
      <th class="px-3 py-2 text-left">Bezeichnung</th>
      <th class="px-3 py-2 text-left">Niveau</th>
      <th class="px-3 py-2 text-left">Institut</th>
      <th class="px-3 py-2 w-40">Aktionen</th>
    </tr>
  </thead>
  <tbody>
  @foreach($termine as $t)
    <tr class="border-t">
      <td class="px-3 py-2">{{ \Illuminate\Support\Carbon::parse($t->datum)->format('d.m.Y') }}</td>
      <td class="px-3 py-2">
        <a class="text-blue-700 hover:underline" href="{{ route('admin.pruefungstermine.show',$t) }}">
          {{ $t->bezeichnung ?? '—' }}
        </a>
      </td>
      <td class="px-3 py-2">{{ $t->niveau?->code }} {{ $t->niveau?->label ? '– '.$t->niveau->label : '' }}</td>
      <td class="px-3 py-2">{{ $t->institut ?? '—' }}</td>
      <td class="px-3 py-2">
        <a href="{{ route('admin.pruefungstermine.edit',$t) }}" class="text-blue-600 hover:underline mr-2">Bearbeiten</a>
        <form action="{{ route('admin.pruefungstermine.destroy',$t) }}" method="POST" class="inline" onsubmit="return confirm('Löschen?')">
          @csrf @method('DELETE')
          <button class="text-red-600 hover:underline">Löschen</button>
        </form>
      </td>
    </tr>
  @endforeach
  </tbody>
</table>
<div class="mt-3">{{ $termine->links() }}</div>
@endif
@endsection
