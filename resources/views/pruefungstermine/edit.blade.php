@extends('layouts.app')
@section('title', $termin->exists ? 'Prüfungstermin bearbeiten' : 'Prüfungstermin anlegen')

@section('content')
  <h1 class="text-2xl font-bold mb-4">
    {{ $termin->exists ? 'Prüfungstermin bearbeiten' : 'Prüfungstermin anlegen' }}
  </h1>

  @php
    // Sinnvolle Defaults für neue Termine (morgen 09:00–11:00), falls nichts im old() steht.
    $startVal = old('start_at', optional($termin->start_at)->format('Y-m-d\TH:i'));
    $endVal   = old('end_at',   optional($termin->end_at)->format('Y-m-d\TH:i'));
    if (!$termin->exists && !$startVal) {
        $startVal = now()->addDay()->setTime(9,0)->format('Y-m-d\TH:i');
        $endVal   = now()->addDay()->setTime(11,0)->format('Y-m-d\TH:i');
    }
  @endphp

  <form method="POST"
        action="{{ $termin->exists ? route('pruefungstermine.update',$termin) : route('pruefungstermine.store') }}"
        class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-3xl">
    @csrf
    @if($termin->exists) @method('PUT') @endif

    {{-- Niveau --}}
    <div class="md:col-span-1">
        <label class="block text-sm mb-1">Niveau *</label>
            <select name="niveau_id" class="border rounded w-full px-3 py-2" required>
                <option value="">— auswählen —</option>
                    @foreach($niveaus as $n)
                        <option value="{{ $n->niveau_id }}" @selected(old('niveau_id', $termin->niveau_id) == $n->niveau_id)>
                    {{ $n->code }} — {{ $n->label }}
                </option>
            @endforeach
        </select>
    @error('niveau_id')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror

    </div>

    {{-- Institut --}}
    <div class="md:col-span-1">
      <label class="block text-sm mb-1">Institut</label>
      <input name="institut" value="{{ old('institut',$termin->institut) }}" class="border rounded w-full px-3 py-2">
      @error('institut')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
    </div>

    {{-- Titel (optional, z. B. „ÖIF Modul B1“) --}}
    <div class="md:col-span-1">
      <label class="block text-sm mb-1">Titel</label>
      <input name="titel" value="{{ old('titel',$termin->titel ?? '') }}" class="border rounded w-full px-3 py-2">
      @error('titel')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
    </div>

    {{-- Bezeichnung (Zusatzinfo/Anmerkung) --}}
    <div class="md:col-span-1">
      <label class="block text-sm mb-1">Bezeichnung</label>
      <input name="bezeichnung" value="{{ old('bezeichnung',$termin->bezeichnung) }}" class="border rounded w-full px-3 py-2">
      @error('bezeichnung')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
    </div>

    {{-- Beginn (mit Uhrzeit) --}}
    <div class="md:col-span-1">
      <label class="block text-sm mb-1">Beginn *</label>
      <input type="datetime-local" name="start_at" value="{{ $startVal }}" class="border rounded w-full px-3 py-2" required>
      @error('start_at')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
    </div>

    {{-- Ende (mit Uhrzeit) --}}
    <div class="md:col-span-1">
      <label class="block text-sm mb-1">Ende *</label>
      <input type="datetime-local" name="end_at" value="{{ $endVal }}" class="border rounded w-full px-3 py-2" required>
      @error('end_at')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
    </div>

    {{-- Hinweis: Das reine Datum wird automatisch aus „Beginn“ übernommen. --}}
    <div class="md:col-span-2 text-sm text-gray-500 -mt-2">
      Hinweis: Das Feld <em>Datum</em> wird automatisch aus dem Beginn-Datum gesetzt.
      Überschneidungen mit anderen Terminen werden beim Speichern geprüft.
    </div>

    <div class="md:col-span-2">
      <button class="px-4 py-2 bg-blue-600 text-white rounded">
        {{ $termin->exists ? 'Speichern' : 'Anlegen' }}
      </button>
      <a href="{{ route('pruefungstermine.index') }}" class="ml-2 px-4 py-2 border rounded">Abbrechen</a>
    </div>
  </form>
@endsection
