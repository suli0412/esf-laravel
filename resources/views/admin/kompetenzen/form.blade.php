@extends('layouts.app')
@section('title', $kompetenz->exists ? 'Kompetenz bearbeiten' : 'Kompetenz anlegen')
@section('content')
<h1 class="text-2xl font-bold mb-4">{{ $kompetenz->exists ? 'Kompetenz bearbeiten' : 'Kompetenz anlegen' }}</h1>

@if ($errors->any())
  <div class="mb-3 text-red-700">
    <ul class="list-disc ml-5">
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ $kompetenz->exists ? route('admin.kompetenzen.update',$kompetenz) : route('admin.kompetenzen.store') }}" class="grid grid-cols-2 gap-4 max-w-3xl">
  @csrf
  @if($kompetenz->exists) @method('PUT') @endif

  <div>
    <label class="block text-sm mb-1">Code *</label>
    <input name="code" value="{{ old('code',$kompetenz->code) }}" class="border rounded w-full px-3 py-2" required>
  </div>
  <div>
    <label class="block text-sm mb-1">Bezeichnung *</label>
    <input name="bezeichnung" value="{{ old('bezeichnung',$kompetenz->bezeichnung) }}" class="border rounded w-full px-3 py-2" required>
  </div>

  <div class="col-span-2 flex gap-2">
    <button class="px-4 py-2 bg-blue-600 text-white rounded">{{ $kompetenz->exists ? 'Speichern' : 'Anlegen' }}</button>
    <a href="{{ route('admin.kompetenzen.index') }}" class="px-4 py-2 rounded border">Abbrechen</a>
  </div>
</form>
@endsection
