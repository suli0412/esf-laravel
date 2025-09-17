@extends('layouts.app')
@section('title','Neue Gruppe')
@section('content')
  <h2 class="text-2xl font-bold mb-4">Neue Gruppe</h2>
  <form action="{{ route('gruppen.store') }}" method="POST" class="space-y-6">
    @csrf
    @include('gruppen._form')
  </form>
@endsection
