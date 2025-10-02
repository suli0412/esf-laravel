@extends('layouts.guest')
@section('title','Passwort zurücksetzen')

@section('content')
  <div class="rounded-2xl bg-white/90 backdrop-blur border border-slate-200 shadow-sm p-6">
    <h1 class="text-2xl font-bold mb-1">Passwort vergessen?</h1>
    <p class="text-slate-500 mb-6">
      Gib deine E-Mail ein. Wir senden dir einen Link zum Zurücksetzen.
    </p>

    @if (session('status'))
      <div class="mb-4 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-2">
        {{ session('status') }}
      </div>
    @endif

    @if ($errors->any())
      <div class="mb-4 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 px-3 py-2">
        Bitte Eingaben prüfen.
      </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
      @csrf
      <div>
        <label class="block text-sm mb-1">E-Mail</label>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus
               class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('email')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
      </div>

      <button class="w-full rounded-lg bg-slate-900 text-white py-2.5 hover:bg-black">
        Reset-Link senden
      </button>
    </form>
  </div>
@endsection
