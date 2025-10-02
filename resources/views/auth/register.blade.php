@extends('layouts.guest')
@section('title','Registrieren')

@section('content')
  <div class="rounded-2xl bg-white/90 backdrop-blur border border-slate-200 shadow-sm p-6">
    <h1 class="text-2xl font-bold mb-1">Konto erstellen</h1>
    <p class="text-slate-500 mb-6">Schnell & unkompliziert.</p>

    @if ($errors->any())
      <div class="mb-4 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 px-3 py-2">
        Bitte Eingaben prüfen.
      </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
      @csrf
      <div>
        <label class="block text-sm mb-1">Name</label>
        <input name="name" value="{{ old('name') }}" required
               class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('name')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-sm mb-1">E-Mail</label>
          <input type="email" name="email" value="{{ old('email') }}" required
                 class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
          @error('email')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
          <label class="block text-sm mb-1">Passwort</label>
          <input type="password" name="password" required
                 class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
          @error('password')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>
      </div>

      <div>
        <label class="block text-sm mb-1">Passwort bestätigen</label>
        <input type="password" name="password_confirmation" required
               class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <button class="w-full rounded-lg bg-slate-900 text-white py-2.5 hover:bg-black">
        Registrieren
      </button>
    </form>

    <p class="text-xs text-slate-500 mt-6 text-center">
      Bereits registriert?
      <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Zum Login</a>
    </p>
  </div>
@endsection
