@extends('layouts.app')
@section('title','Niveaus')
@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-2xl font-bold">Niveaus</h1>
  <a href="{{ route('admin.niveaus.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">Neu</a>
</div>

@if(session('success'))
  <div class="mb-3 text-green-700">{{ session('success') }}</div>
@endif

@if($records->isEmpty())
  <p class="text-gray-500">Noch keine Niveaus definiert.</p>
@else
<table class="w-full text-sm">
  <thead class="bg-gray-50">
    <tr>
      <th class="px-3 py-2 text-left">Sort</th>
      <th class="px-3 py-2 text-left">Code</th>
      <th class="px-3 py-2 text-left">Label</th>
      <th class="px-3 py-2 w-32">Aktionen</th>
    </tr>
  </thead>
  <tbody>
  @foreach($records as $n)
    <tr class="border-t">
      <td class="px-3 py-2">{{ $n->sort_order }}</td>
      <td class="px-3 py-2">{{ $n->code }}</td>
      <td class="px-3 py-2">{{ $n->label }}</td>
      <td class="px-3 py-2">
        <a href="{{ route('admin.niveaus.edit',$n) }}" class="text-blue-600 hover:underline mr-2">Bearbeiten</a>
        <form action="{{ route('admin.niveaus.destroy',$n) }}" method="POST" class="inline" onsubmit="return confirm('Löschen?')">
          @csrf @method('DELETE')
          <button class="text-red-600 hover:underline">Löschen</button>
        </form>
      </td>
    </tr>
  @endforeach
  </tbody>
</table>
<div class="mt-3">{{ $records->links() }}</div>
@endif
@endsection
