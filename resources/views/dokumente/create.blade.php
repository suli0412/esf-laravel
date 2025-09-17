@extends('layouts.app')
@section('title','Neue Vorlage')
@section('content')
<h2 class="text-2xl font-bold mb-4">Neue Dokument-Vorlage</h2>
<form action="{{ route('dokumente.store') }}" method="POST">
  @include('dokumente._form')
</form>
@endsection
