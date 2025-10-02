@extends('layouts.app')
@section('title','Projekt bearbeiten')

@section('content')
  <h2 class="text-2xl font-bold mb-6">Projekt bearbeiten</h2>

  <form action="{{ route('projekte.update', $projekt) }}" method="POST"
        class="bg-white p-6 rounded-xl shadow-sm max-w-2xl space-y-6">
    @csrf
    @method('PUT')

    @include('projekte._form', ['projekt' => $projekt])

    <div class="flex gap-3">
      <a href="{{ route('projekte.show', $projekt) }}"
         class="px-4 py-2 border rounded-lg hover:bg-gray-50">
        Abbrechen
      </a>
      <button type="submit"
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
        Aktualisieren
      </button>
    </div>
  </form>
@endsection
