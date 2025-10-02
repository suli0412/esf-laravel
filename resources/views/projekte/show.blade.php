@extends('layouts.app')
@section('title','Projekt · '.$projekt->code)

@section('content')
  @if(session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 text-green-800 px-4 py-2 text-sm">
      {{ session('success') }}
    </div>
  @endif

  <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h2 class="text-xl font-bold mb-4">Projekt-Details</h2>

    <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
      <div>
        <dt class="text-gray-500">Code</dt>
        <dd class="font-medium">{{ $projekt->code }}</dd>
      </div>
      <div>
        <dt class="text-gray-500">Bezeichnung</dt>
        <dd class="font-medium">{{ $projekt->bezeichnung }}</dd>
      </div>
      <div>
        <dt class="text-gray-500">Start</dt>
        <dd>{{ $projekt->start?->format('d.m.Y') ?? '—' }}</dd>
      </div>
      <div>
        <dt class="text-gray-500">Ende</dt>
        <dd>{{ $projekt->ende?->format('d.m.Y') ?? '—' }}</dd>
      </div>
      <div>
        <dt class="text-gray-500">Status</dt>
        <dd>
          @if($projekt->aktiv)
            <span class="px-2 py-1 rounded bg-green-100 text-green-700 text-xs font-medium">aktiv</span>
          @else
            <span class="px-2 py-1 rounded bg-gray-100 text-gray-700 text-xs font-medium">inaktiv</span>
          @endif
        </dd>
      </div>
    </dl>

    <div class="mt-6 flex gap-3">
      <a href="{{ route('projekte.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
        Zurück
      </a>
      <a href="{{ route('projekte.edit', $projekt) }}" class="px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600">
        Bearbeiten
      </a>
      <form action="{{ route('projekte.destroy', $projekt) }}" method="POST"
            onsubmit="return confirm('Projekt wirklich löschen?')">
        @csrf @method('DELETE')
        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
          Löschen
        </button>
      </form>
    </div>
  </div>

  {{-- TODO: hier später Teilnehmer_Projekt-Historie ausgeben --}}
@endsection
