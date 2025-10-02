@extends('layouts.app')
@section('title','Benutzer anlegen')

@section('content')
<h1 class="text-2xl font-bold mb-6">Benutzer anlegen</h1>

@if ($errors->any())
  <div class="mb-4 rounded border bg-red-50 text-red-800 px-3 py-2">
    <ul class="list-disc list-inside">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form action="{{ route('admin.users.store') }}" method="POST" class="bg-white rounded-xl shadow-sm p-5">
  @include('admin.users._form', ['roles' => $roles, 'userRoles' => []])
</form>
@endsection
