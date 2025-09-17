@extends('layouts.app')
@section('title','Anwesenheit – Neuer Eintrag')

@section('content')
<h2 class="text-2xl font-bold mb-4">Neuer Anwesenheits-Eintrag</h2>

<form action="{{ route('anwesenheit.store') }}" method="POST" class="bg-white p-6 rounded-xl shadow-sm max-w-3xl space-y-4">
  @csrf
  @include('anwesenheit._form', ['anwesenheit' => null])
  <div class="flex gap-2">
    <a href="{{ route('anwesenheit.index') }}" class="px-4 py-2 border rounded">Abbrechen</a>
    <button class="px-4 py-2 bg-green-600 text-white rounded">Speichern</button>
  </div>
</form>
@endsection
