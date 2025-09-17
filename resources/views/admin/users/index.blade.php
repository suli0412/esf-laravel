@extends('layouts.app')

@section('content')
    <h1 class="text-2xl font-semibold mb-4">User & Rollen</h1>

    @if(session('success'))
        <div class="mb-4 rounded bg-green-100 p-3 text-green-800">{{ session('success') }}</div>
    @endif

    {{-- Neuer User --}}
    <div class="rounded-2xl border shadow p-4 mb-6">
        <h2 class="font-semibold mb-3">Neuen User anlegen</h2>
        <form method="POST" action="{{ route('admin.users.store') }}" class="grid md:grid-cols-4 gap-3">
            @csrf
            <input name="name" type="text" placeholder="Name" class="border rounded px-2 py-1" required>
            <input name="email" type="email" placeholder="E-Mail" class="border rounded px-2 py-1" required>
            <select name="roles[]" multiple size="3" class="border rounded px-2 py-1">
                @foreach($roles as $r)
                    <option value="{{ $r->name }}">{{ $r->name }}</option>
                @endforeach
            </select>
            <button class="px-3 py-2 rounded bg-blue-600 text-white">Anlegen</button>
        </form>
        <p class="text-sm text-gray-500 mt-2">
            Hinweis: Es wird ein zufälliges Temp-Passwort generiert und im Erfolgshinweis angezeigt.
        </p>
    </div>

    {{-- Bestehende User verwalten --}}
    <div class="space-y-4">
        @foreach($users as $u)
            <div class="rounded-2xl shadow p-4 border">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <div class="font-medium">{{ $u->name ?? $u->email }}</div>
                        <div class="text-sm text-gray-500">
                            Rollen: {{ $u->roles->pluck('name')->join(', ') ?: '—' }}
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.users.roles.update', $u) }}" class="flex items-center gap-3">
                        @csrf
                        <select name="roles[]" multiple size="4" class="border rounded px-2 py-1 min-w-64">
                            @foreach($roles as $r)
                                <option value="{{ $r->name }}" @selected($u->hasRole($r->name))>{{ $r->name }}</option>
                            @endforeach
                        </select>
                        <button class="px-3 py-2 rounded bg-blue-600 text-white">Speichern</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endsection
