@extends('layouts.app')
@section('title','Anwesenheit bearbeiten')

@section('content')
<h2 class="text-2xl font-bold mb-4">Anwesenheit bearbeiten</h2>

<form action="{{ route('anwesenheit.update', $anwesenheit) }}" method="POST" class="bg-white p-6 rounded-xl shadow-sm max-w-3xl space-y-4">
  @csrf @method('PUT')
  @include('anwesenheit._form', ['anwesenheit' => $anwesenheit])
  <div class="flex gap-2">
    <a href="{{ route('anwesenheit.index') }}" class="px-4 py-2 border rounded">Abbrechen</a>
    <button class="px-4 py-2 bg-green-600 text-white rounded">Aktualisieren</button>
  </div>
</form>
@endsection


