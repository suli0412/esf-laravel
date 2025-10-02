@extends('layouts.guest')
@section('title','E-Mail bestätigen')

@section('content')
  <div class="rounded-2xl bg-white/90 backdrop-blur border border-slate-200 shadow-sm p-6">
    <h1 class="text-2xl font-bold mb-2">E-Mail bestätigen</h1>
    <p class="text-slate-600 mb-4">
      Wir haben dir einen Bestätigungslink gesendet. Kein Link erhalten?
    </p>

    @if (session('status') == 'verification-link-sent')
      <div class="mb-4 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 px-3 py-2">
        Neuer Bestätigungslink wurde gesendet.
      </div>
    @endif

    <div class="flex items-center gap-3">
      <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button class="rounded-lg bg-slate-900 text-white px-4 py-2">Link erneut senden</button>
      </form>

      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="rounded-lg border px-4 py-2">Abmelden</button>
      </form>
    </div>
  </div>
@endsection
