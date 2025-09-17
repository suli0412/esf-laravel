@extends('layouts.app')
@section('title', $niveau->exists ? 'Niveau bearbeiten' : 'Niveau anlegen')
@section('content')
<h1 class="text-2xl font-bold mb-4">{{ $niveau->exists ? 'Niveau bearbeiten' : 'Niveau anlegen' }}</h1>

@if ($errors->any())
  <div class="mb-3 text-red-700">
    <ul class="list-disc ml-5">
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ $niveau->exists ? route('admin.niveaus.update',$niveau) : route('admin.niveaus.store') }}" class="grid grid-cols-3 gap-4 max-w-3xl">
  @csrf
  @if($niveau->exists) @method('PUT') @endif

  <div>
    <label class="block text-sm mb-1">Code *</label>
    <input name="code" value="{{ old('code',$niveau->code) }}" class="border rounded w-full px-3 py-2" required>
  </div>
  <div>
    <label class="block text-sm mb-1">Label</label>
    <input name="label" value="{{ old('label',$niveau->label) }}" class="border rounded w-full px-3 py-2">
  </div>
  <div>
    <label class="block text-sm mb-1">Sort *</label>
    <input type="number" name="sort_order" value="{{ old('sort_order',$niveau->sort_order) }}" class="border rounded w-full px-3 py-2" required>
  </div>

  <div class="col-span-3 flex gap-2">
    <button class="px-4 py-2 bg-blue-600 text-white rounded">{{ $niveau->exists ? 'Speichern' : 'Anlegen' }}</button>
    <a href="{{ route('admin.niveaus.index') }}" class="px-4 py-2 rounded border">Abbrechen</a>
  </div>
</form>
@endsection
