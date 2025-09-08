@extends('layouts.app')

@section('title','Neuer Teilnehmer')

@section('content')
<form action="{{ route('teilnehmer.store') }}" method="POST" class="space-y-6">
    @csrf
    @include('teilnehmer._form')
</form>
@endsection

