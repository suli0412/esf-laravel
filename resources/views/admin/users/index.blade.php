@extends('layouts.app')
@section('title','Benutzerverwaltung')

@section('content')
<div class="flex items-center justify-between mb-6 gap-3">
  <h1 class="text-2xl font-bold">Benutzer</h1>

  <form method="GET" class="flex items-end gap-2">
    <div>
      <label class="text-sm text-gray-600">Suche</label>
      <input type="text" name="q" value="{{ $q }}" class="border rounded px-3 py-2" placeholder="Name oder E-Mail">
    </div>
    <button class="px-4 py-2 bg-gray-800 text-white rounded">Filtern</button>
    @if($q)
      <a href="{{ route('admin.users.index') }}" class="px-3 py-2 border rounded">Reset</a>
    @endif
  </form>

  @can('users.manage')
    <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">+ Neuer Benutzer</a>
  @endcan
</div>

@if(session('success'))
  <div class="mb-4 rounded border bg-green-50 text-green-800 px-3 py-2">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="mb-4 rounded border bg-red-50 text-red-800 px-3 py-2">{{ session('error') }}</div>
@endif

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
  <table class="w-full text-left">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-3 py-2">Name</th>
        <th class="px-3 py-2">E-Mail</th>
        <th class="px-3 py-2">Rollen</th>
        <th class="px-3 py-2 w-56">Aktionen</th>
      </tr>
    </thead>
    <tbody>
      @forelse($users as $u)
        <tr class="border-t">
          <td class="px-3 py-2">{{ $u->name }}</td>
          <td class="px-3 py-2">{{ $u->email }}</td>
          <td class="px-3 py-2">
            @forelse($u->roles as $r)
              <span class="inline-block px-2 py-0.5 text-xs rounded bg-gray-100 border mr-1">{{ $r->name }}</span>
            @empty
              <span class="text-gray-400">—</span>
            @endforelse
          </td>
          <td class="px-3 py-2">
            <div class="flex items-center gap-3">
              @can('users.manage')
                <a href="{{ route('admin.users.edit', $u) }}" class="text-blue-700 hover:underline">Bearbeiten</a>
                <div>
                </div>
                <div>
                </div>
                <form action="{{ route('admin.users.sendReset', $u) }}" method="POST" onsubmit="return confirm('Reset-Link an {{ $u->email }} senden?')">
                  @csrf
                  <button class="text-indigo-700 hover:underline" type="submit">Reset-Link</button>
                </form>
                <div>
                </div>
                <div>
                </div>
                <div>
                </div>
                <div>
                </div>
                @if(auth()->id() !== $u->id)
                  <form action="{{ route('admin.users.destroy', $u) }}" method="POST" onsubmit="return confirm('Diesen Benutzer löschen?')">
                    @csrf @method('DELETE')
                    <button class="text-red-700 hover:underline" type="submit">Löschen</button>
                  </form>
                @endif
              @endcan
            </div>
          </td>
        </tr>
      @empty
        <tr><td class="px-3 py-6 text-gray-500" colspan="4">Keine Benutzer gefunden.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $users->links() }}</div>
@endsection
