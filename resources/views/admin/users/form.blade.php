@extends('layouts.app')
@section('title', $mode === 'create' ? 'Benutzer anlegen' : 'Benutzer bearbeiten')

@section('content')
<h1 class="text-2xl font-bold mb-4">
  {{ $mode === 'create' ? 'Neuer Benutzer' : 'Benutzer bearbeiten' }}
</h1>

@if($errors->any())
  <div class="mb-4 px-3 py-2 bg-red-50 text-red-800 rounded">
    <ul class="list-disc ml-4">
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
@endif

<form method="POST" action="{{ $mode==='create' ? route('admin.users.store') : route('admin.users.update',$user) }}">
  @csrf
  @if($mode==='edit') @method('PUT') @endif

  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white rounded-xl p-4 shadow-sm">
    <div>
      <label class="block text-sm text-gray-600">Name *</label>
      <input name="name" value="{{ old('name',$user->name) }}" class="border rounded w-full px-3 py-2" required>
    </div>
    <div>
      <label class="block text-sm text-gray-600">Email *</label>
      <input name="email" type="email" value="{{ old('email',$user->email) }}" class="border rounded w-full px-3 py-2" required>
    </div>
    <div>
      <label class="block text-sm text-gray-600">Passwort @if($mode==='create') (leer = Reset-Mail) @else (leer = unverändert) @endif</label>
      <input name="password" type="password" class="border rounded w-full px-3 py-2">
      @if($mode==='create')
      <label class="inline-flex items-center mt-2 text-sm">
        <input type="checkbox" name="send_reset" value="1" class="mr-2" checked>
        Passwort-Zurücksetzen-Mail senden
      </label>
      @endif
    </div>

    <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <div class="font-semibold mb-2">Rollen</div>
        <div class="grid grid-cols-2 gap-2">
          @foreach($roles as $role)
            <label class="inline-flex items-center">
              <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                     @checked( collect(old('roles', $user->roles->pluck('name')->all()))->contains($role->name) ) class="mr-2">
              {{ $role->name }}
            </label>
          @endforeach
        </div>
      </div>

      <div>
        <div class="font-semibold mb-2">Zusätzliche Berechtigungen</div>
        <div class="grid grid-cols-2 gap-2 max-h-56 overflow-auto border rounded p-2">
          @foreach($permissions as $perm)
            <label class="inline-flex items-center">
              <input type="checkbox" name="perms[]" value="{{ $perm->name }}"
                     @checked( collect(old('perms', $user->permissions->pluck('name')->all()))->contains($perm->name) ) class="mr-2">
              {{ $perm->name }}
            </label>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  <div class="mt-4 flex gap-2">
    <button class="px-4 py-2 bg-blue-600 text-white rounded">Speichern</button>
    <a href="{{ route('admin.users.index') }}" class="px-4 py-2 border rounded">Abbrechen</a>
  </div>
</form>
@endsection
