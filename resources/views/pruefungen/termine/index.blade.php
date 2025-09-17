@extends('layouts.app')
@section('title','Prüfungstermine')

@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-2xl font-bold">Prüfungstermine</h1>
  <div class="space-x-2">
    <a href="{{ route('pruefungstermine.create') }}" class="px-3 py-2 bg-blue-600 text-white rounded">Neuer Termin</a>
  </div>
</div>

<form method="GET" class="mb-4 flex items-end gap-3">
  <div>
    <label class="block text-sm mb-1">Niveau</label>
    <select name="niveau_id" class="border rounded px-3 py-2">
      <option value="">Alle</option>
      @foreach($niveaus as $n)
        <option value="{{ $n->niveau_id }}" @selected($niveauId==$n->niveau_id)>
          {{ $n->code }} — {{ $n->label }}
        </option>
      @endforeach
    </select>
  </div>
  <button class="px-3 py-2 border rounded">Filtern</button>
</form>

<table class="w-full text-sm mb-8">
  <thead class="bg-gray-50">
    <tr>
      <th class="px-3 py-2 text-left">Datum</th>
      <th class="px-3 py-2 text-left">Niveau</th>
      <th class="px-3 py-2 text-left">Bezeichnung</th>
      <th class="px-3 py-2 text-left">Institut</th>
      <th class="px-3 py-2 w-32"></th>
    </tr>
  </thead>
  <tbody>
    @foreach($termine as $t)
      <tr class="border-t">
        <td class="px-3 py-2">{{ optional($t->datum)->format('d.m.Y') }}</td>
        <td class="px-3 py-2">{{ $t->niveau?->code }}</td>
        <td class="px-3 py-2">{{ $t->bezeichnung }}</td>
        <td class="px-3 py-2">{{ $t->institut }}</td>
        <td class="px-3 py-2 text-right">
          <a href="{{ route('pruefungstermine.show',$t) }}" class="text-blue-600 hover:underline">Öffnen</a>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>

{{ $termine->links() }}

<hr class="my-8">

<h2 class="text-lg font-semibold mb-2">Kalender importieren (.ics)</h2>
<form method="POST" action="{{ route('pruefungstermine.import') }}" enctype="multipart/form-data" class="grid grid-cols-4 gap-3 max-w-3xl">
  @csrf
  <div class="col-span-2">
    <label class="block text-sm mb-1">.ics Datei *</label>
    <input type="file" name="ics" accept=".ics,text/plain" class="border rounded w-full px-3 py-2" required>
    @error('ics')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
  </div>
  <div class="col-span-1">
    <label class="block text-sm mb-1">Niveau (Default)</label>
    <select name="niveau_id" class="border rounded w-full px-3 py-2">
      <option value="">(aus SUMMARY ermitteln)</option>
      @foreach($niveaus as $n)
        <option value="{{ $n->niveau_id }}">{{ $n->code }} — {{ $n->label }}</option>
      @endforeach
    </select>
  </div>
  <div class="col-span-1">
    <label class="block text-sm mb-1">Institut (optional)</label>
    <input name="institut" class="border rounded w-full px-3 py-2">
  </div>
  <div class="col-span-4">
    <button class="px-4 py-2 bg-blue-600 text-white rounded">Import starten</button>
  </div>
</form>
@endsection
