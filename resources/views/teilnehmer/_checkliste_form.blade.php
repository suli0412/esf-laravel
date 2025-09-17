@extends('layouts.app')

@section('title','Teilnehmer bearbeiten')

@section('content')
<form action="{{ route('teilnehmer.update', $teilnehmer) }}" method="POST" class="space-y-6">
    @csrf @method('PUT')
    @include('teilnehmer._form')
</form>
@endsection

