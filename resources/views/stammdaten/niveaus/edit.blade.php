@extends('layouts.app')
@section('title', $item->exists ? 'Niveau bearbeiten' : 'Niveau anlegen')

@section('content')
<h1 class="text-2xl font-bold mb-4">{{ $item->exists ? 'Niveau bearbeiten' : 'Niveau anlegen' }}</h1>

<form method="POST" action="{{ $item->exists ? route('niveaus.update',$item) : route('niveaus.store') }}" class="max-w-lg space-y-3">
  @csrf
  @if($item->exists) @method('PUT') @endif

  <div>
    <label class="block text-sm mb-1">Code *</label>
    <input name="code" value="{{ old('code',$item->code) }}" class="border rounded w-full px-3 py-2" required>
    @error('code')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
  </div>

  <div>
    <label class="block text-sm mb-1">Label *</label>
    <input name="label" value="{{ old('label',$item->label) }}" class="border rounded w-full px-3 py-2" required>
    @error('label')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
  </div>

  <div>
    <label class="block text-sm mb-1">Sortierung *</label>
    <input type="number" min="0" name="sort_order" value="{{ old('sort_order',$item->sort_order) }}" class="border rounded w-full px-3 py-2" required>
    @error('sort_order')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
  </div>

  <div class="pt-2">
    <button class="px-4 py-2 bg-blue-600 text-white rounded">{{ $item->exists ? 'Speichern' : 'Anlegen' }}</button>
    <a href="{{ route('niveaus.index') }}" class="ml-2 px-4 py-2 border rounded">Abbrechen</a>
  </div>
</form>
@endsection
