@extends('layouts.guest')
@section('title','Neues Passwort setzen')

@section('content')
  <div class="rounded-2xl bg-white/90 backdrop-blur border border-slate-200 shadow-sm p-6">
    <h1 class="text-2xl font-bold mb-6">Neues Passwort setzen</h1>

    @if ($errors->any())
      <div class="mb-4 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 px-3 py-2">
        Bitte Eingaben prüfen.
      </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
      @csrf
      <input type="hidden" name="token" value="{{ $request->route('token') }}">
      <input type="hidden" name="email" value="{{ request('email') }}">

      <div>
        <label class="block text-sm mb-1">Neues Passwort</label>
        <input type="password" name="password" required
               class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
        @error('password')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="block text-sm mb-1">Passwort bestätigen</label>
        <input type="password" name="password_confirmation" required
               class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
      </div>

      <button class="w-full rounded-lg bg-slate-900 text-white py-2.5 hover:bg-black">
        Speichern
      </button>
    </form>
  </div>
@endsection
