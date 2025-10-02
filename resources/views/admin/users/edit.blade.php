@extends('layouts.app')
@section('title','Benutzer bearbeiten')

@section('content')
<h1 class="text-2xl font-bold mb-6">Benutzer bearbeiten</h1>

@if(session('success'))
  <div class="mb-4 rounded border bg-green-50 text-green-800 px-3 py-2">{{ session('success') }}</div>
@endif
@if ($errors->any())
  <div class="mb-4 rounded border bg-red-50 text-red-800 px-3 py-2">
    <ul class="list-disc list-inside">
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<form action="{{ route('admin.users.update', $user) }}" method="POST" class="bg-white rounded-xl shadow-sm p-5">
  @method('PUT')
  @include('admin.users._form', ['roles' => $roles, 'userRoles' => $userRoles, 'user' => $user])
</form>
@endsection
