@extends('layouts.guest')
@section('title','Anmeldung')

@section('content')
  <div class="rounded-2xl bg-white/90 backdrop-blur border border-slate-200 shadow-sm p-6">

    {{-- Logo oben --}}
    <div class="mb-5 text-center">
      <a href="{{ url('/') }}" class="inline-block">
        <img
          src="{{ asset('images/logo.jpg') }}"
          alt="{{ config('app.name','ESF') }}"
          class="h-16 w-auto mx-auto object-contain"
        >
      </a>
    </div>

    <h1 class="text-2xl font-bold mb-1">Willkommen zurück</h1>
    <p class="text-slate-500 mb-6">Melde dich bei deinem Konto an.</p>

    @if ($errors->any())
      <div class="mb-4 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 px-3 py-2">
        Bitte Eingaben prüfen.
      </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
      @csrf
      <div>
        <label class="block text-sm mb-1">E-Mail</label>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus
               class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('email')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <div class="flex items-center justify-between">
          <label class="block text-sm mb-1">Passwort</label>
          @if (Route::has('password.request'))
            <a class="text-xs text-indigo-600 hover:underline" href="{{ route('password.request') }}">
              Passwort vergessen?
            </a>
          @endif
        </div>
        <input type="password" name="password" required
               class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('password')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
      </div>

      <div class="flex items-center gap-2">
        <input id="remember" type="checkbox" name="remember" class="h-4 w-4 rounded border-slate-300">
        <label for="remember" class="text-sm text-slate-600">Angemeldet bleiben</label>
      </div>

      <button class="w-full rounded-lg bg-slate-900 text-white py-2.5 hover:bg-black">
        Einloggen
      </button>
    </form>


  </div>
@endsection
