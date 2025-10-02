@extends('layouts.app')

@section('title','Neues Projekt')

@section('content')
  <h2 class="text-2xl font-bold mb-6">Neues Projekt</h2>

  <form action="{{ route('projekte.store') }}" method="POST"
        class="bg-white p-6 rounded-xl shadow-sm max-w-2xl space-y-6">
    @csrf

    @include('projekte._form', ['projekt' => null])

    <div class="flex gap-3">
      <a href="{{ route('projekte.index') }}"
         class="px-4 py-2 border rounded-lg hover:bg-gray-50">
        Abbrechen
      </a>
      <button type="submit"
              class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
        Speichern
      </button>
    </div>



  </form>
@endsection
