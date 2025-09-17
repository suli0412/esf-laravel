@extends('layouts.app')
@section('title', $termin->exists ? 'Prüfungstermin bearbeiten' : 'Prüfungstermin anlegen')

@section('content')
<h1 class="text-2xl font-bold mb-4">{{ $termin->exists ? 'Prüfungstermin bearbeiten' : 'Prüfungstermin anlegen' }}</h1>

<form method="POST" action="{{ $termin->exists ? route('pruefungstermine.update',$termin) : route('pruefungstermine.store') }}" class="grid grid-cols-2 gap-4 max-w-3xl">
  @csrf
  @if($termin->exists) @method('PUT') @endif

  <div>
    <label class="block text-sm mb-1">Niveau *</label>
    <select name="niveau_id" class="border rounded w-full px-3 py-2" required>
      @foreach($niveaus as $n)
        <option value="{{ $n->niveau_id }}" @selected(old('niveau_id',$termin->niveau_id)==$n->niveau_id)>
          {{ $n->code }} — {{ $n->label }}
        </option>
      @endforeach
    </select>
    @error('niveau_id')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
  </div>

  <div>
    <label class="block text-sm mb-1">Bezeichnung</label>
    <input name="bezeichnung" value="{{ old('bezeichnung',$termin->bezeichnung) }}" class="border rounded w-full px-3 py-2">
    @error('bezeichnung')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
  </div>

  <div>
    <label class="block text-sm mb-1">Datum *</label>
    <input type="date" name="datum" value="{{ old('datum', optional($termin->datum)->toDateString()) }}" class="border rounded w-full px-3 py-2" required>
    @error('datum')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
  </div>

  <div>
    <label class="block text-sm mb-1">Institut</label>
    <input name="institut" value="{{ old('institut',$termin->institut) }}" class="border rounded w-full px-3 py-2">
    @error('institut')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
  </div>

  <div class="col-span-2">
    <button class="px-4 py-2 bg-blue-600 text-white rounded">{{ $termin->exists ? 'Speichern' : 'Anlegen' }}</button>
    <a href="{{ route('pruefungstermine.index') }}" class="ml-2 px-4 py-2 border rounded">Abbrechen</a>
  </div>
</form>
@endsection
