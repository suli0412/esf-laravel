@extends('layouts.app')
@section('title', $termin->exists ? 'Prüfungstermin bearbeiten' : 'Prüfungstermin anlegen')
@section('content')
<h1 class="text-2xl font-bold mb-4">{{ $termin->exists ? 'Prüfungstermin bearbeiten' : 'Prüfungstermin anlegen' }}</h1>

@if ($errors->any())
  <div class="mb-3 text-red-700">
    <ul class="list-disc ml-5">
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ $termin->exists ? route('admin.pruefungstermine.update',$termin) : route('admin.pruefungstermine.store') }}" class="grid grid-cols-3 gap-4 max-w-4xl">
  @csrf
  @if($termin->exists) @method('PUT') @endif

  <div>
    <label class="block text-sm mb-1">Datum *</label>
    <input type="date" name="datum" value="{{ old('datum',$termin->datum) }}" class="border rounded w-full px-3 py-2" required>
  </div>

  <div>
    <label class="block text-sm mb-1">Niveau *</label>
    <select name="niveau_id" class="border rounded w-full px-3 py-2" required>
      <option value="">— bitte wählen —</option>
      @foreach($niveaus as $n)
        <option value="{{ $n->niveau_id }}" @selected(old('niveau_id',$termin->niveau_id) == $n->niveau_id)>
          {{ $n->code }} {{ $n->label ? '– '.$n->label : '' }}
        </option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm mb-1">Institut</label>
    <input name="institut" value="{{ old('institut',$termin->institut) }}" class="border rounded w-full px-3 py-2">
  </div>

  <div class="col-span-3">
    <label class="block text-sm mb-1">Bezeichnung</label>
    <input name="bezeichnung" value="{{ old('bezeichnung',$termin->bezeichnung) }}" class="border rounded w-full px-3 py-2" placeholder="z. B. ÖSD A2 (Schriftlich)">
  </div>

  <div class="col-span-3 flex gap-2">
    <button class="px-4 py-2 bg-blue-600 text-white rounded">{{ $termin->exists ? 'Speichern' : 'Anlegen' }}</button>
    <a href="{{ route('admin.pruefungstermine.index') }}" class="px-4 py-2 rounded border">Abbrechen</a>
  </div>
</form>
@endsection
