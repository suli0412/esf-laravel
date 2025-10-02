<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', config('app.name','ESF'))</title>

  @vite(['resources/css/app.css','resources/js/app.js'])
  <style>[x-cloak]{display:none!important}</style>
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
<div class="min-h-screen flex flex-col">

  {{-- Topbar --}}
  <header class="bg-white border-b">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
      <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}" class="flex items-center gap-3">
        <img src="{{ asset('images/org-logo.png') }}" alt="ESF" class="h-9 w-auto object-contain">
        <span class="font-semibold tracking-wide">{{ config('app.name','ESF') }}</span>
      </a>

      <nav class="flex items-center gap-2">
        @php
          $navLink = fn($active) =>
            'px-3 py-2 rounded-lg text-sm ' .
            ($active ? 'bg-blue-600 text-white' : 'bg-white border text-gray-700 hover:bg-gray-50');
        @endphp

        {{-- Hauptnavigation (sichtbar nach Login) --}}
        @auth
          @if (Route::has('dashboard'))
            <a href="{{ route('dashboard') }}"
               class="{{ $navLink(request()->routeIs('dashboard')) }}">
              Dashboard
            </a>
          @endif

          @can('teilnehmer.view')
            @if (Route::has('teilnehmer.index'))
              <a href="{{ route('teilnehmer.index') }}"
                 class="{{ $navLink(request()->routeIs('teilnehmer.*')) }}">
                Teilnehmer
              </a>
            @endif
          @endcan

          @if (Route::has('gruppen.index'))
            <a href="{{ route('gruppen.index') }}"
               class="{{ $navLink(request()->routeIs('gruppen.*')) }}">
              Gruppen
            </a>
          @endif

          @if (Route::has('anwesenheit.index'))
            <a href="{{ route('anwesenheit.index') }}"
               class="{{ $navLink(request()->routeIs('anwesenheit.*')) }}">
              Anwesenheit
            </a>
          @endif

          @if (Route::has('pruefungstermine.index'))
            <a href="{{ route('pruefungstermine.index') }}"
               class="{{ $navLink(request()->routeIs('pruefungstermine.*')) }}">
              Prüfungstermine
            </a>
          @endif

          @can('reports.view')
            @if (Route::has('activity.index') && (auth()->user()?->can('activity.view') || auth()->user()?->hasRole('admin')))
              <a href="{{ route('activity.index') }}"
                 class="{{ $navLink(request()->routeIs('activity.*')) }}">
                Protokolle / Logs
              </a>
            @endif
          @endcan

          @can('users.view')
            @if (Route::has('admin.users.index'))
              <a href="{{ route('admin.users.index') }}"
                 class="{{ $navLink(request()->routeIs('admin.users.*')) }}">
                Benutzer & Rollen
              </a>
            @endif
          @endcan
        @endauth

        {{-- Rechts: User-Info / Login --}}
        @auth
          <div class="ml-3 flex items-center gap-2">
            <span class="text-sm text-gray-600 hidden sm:inline">
              Hallo, {{ auth()->user()->name ?? trim((auth()->user()->Vorname ?? '').' '.(auth()->user()->Nachname ?? '')) ?: 'User' }}
            </span>

            {{-- Account-Menü --}}
            <div x-data="{ open:false }" class="relative">
              <button type="button" @click="open=!open" @keydown.escape.window="open=false"
                      class="flex items-center gap-2 rounded-full bg-white/80 px-2.5 py-1.5 shadow hover:shadow-md border border-gray-200">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600 text-white text-sm font-semibold">
                  {{ strtoupper(mb_substr(auth()->user()->name ?? auth()->user()->Vorname ?? 'U', 0, 1)) }}
                </span>
                <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"/>
                </svg>
                <span class="sr-only">Benutzermenü öffnen</span>
              </button>

              <div x-cloak x-show="open" @click.outside="open=false"
                   class="absolute right-0 mt-2 w-56 rounded-xl border border-gray-200 bg-white/95 backdrop-blur-xl shadow-lg">
                <div class="px-4 py-3">
                  <p class="text-sm font-medium text-gray-900">
                    {{ auth()->user()->name ?? trim((auth()->user()->Vorname ?? '').' '.(auth()->user()->Nachname ?? '')) }}
                  </p>
                  @if(auth()->user()->email)
                    <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                  @endif
                </div>

                @if (Route::has('profile.edit'))
                  <a href="{{ route('profile.edit') }}"
                     class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    Profil
                  </a>
                  <div class="h-px bg-gray-100"></div>
                @endif

                <form method="POST" action="{{ route('logout') }}" class="p-2">
                  @csrf
                  <button type="submit"
                          class="w-full text-left rounded-lg px-3 py-2 text-sm text-rose-600 hover:bg-rose-50">
                    Abmelden
                  </button>
                </form>
              </div>
            </div>

            {{-- NoScript-Fallback --}}
            <noscript>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="px-3 py-2 rounded-lg bg-gray-800 hover:bg-gray-900 text-white text-sm">
                  Logout
                </button>
              </form>
            </noscript>
          </div>
        @endauth

        @guest
          @if (Route::has('login'))
            <a href="{{ route('login') }}"
               class="px-3 py-2 rounded-lg bg-gray-800 hover:bg-gray-900 text-white text-sm">
              Login
            </a>
          @endif
        @endguest
      </nav>
    </div>
  </header>

  {{-- Globale Flash-Meldungen --}}
  @if (session('success') || session('status') || $errors->any())
    <div class="bg-white border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        @if (session('success'))
          <div class="mb-2 rounded-md border border-emerald-200 bg-emerald-50 text-emerald-800 px-3 py-2">
            {{ session('success') }}
          </div>
        @endif
        @if (session('status'))
          <div class="mb-2 rounded-md border border-blue-200 bg-blue-50 text-blue-800 px-3 py-2">
            {{ session('status') }}
          </div>
        @endif
        @if ($errors->any())
          <div class="rounded-md border border-rose-200 bg-rose-50 text-rose-700 px-3 py-2">
            Bitte Eingaben prüfen.
          </div>
        @endif
      </div>
    </div>
  @endif

  {{-- Dein bestehendes Modal (Beratung) --}}
  <div
    x-data="{ open:false, payload:{} }"
    x-on:open-beratung.window="open = true; payload = $event.detail"
    x-show="open"
    class="fixed inset-0 z-50 flex items-center justify-center"
    style="display:none">
    <div class="absolute inset-0 bg-black/40" @click="open=false"></div>
    <div class="relative bg-white rounded-2xl shadow p-6 w-full max-w-xl">
      <h3 class="text-lg font-semibold mb-3">Beratung</h3>
      <div class="text-sm text-gray-600">
        Lädt Details für ID: <span x-text="payload.id"></span>
      </div>
      <div class="mt-5 flex justify-end gap-2">
        <button class="px-3 py-2 rounded-lg border" @click="open=false">Schließen</button>
      </div>
    </div>
  </div>

  {{-- Optionaler Page-Header --}}
  @if (isset($header))
    <div class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        {{ $header }}
      </div>
    </div>
  @elseif (View::hasSection('header'))
    <div class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        @yield('header')
      </div>
    </div>
  @endif

  {{-- Page Content --}}
  <main class="flex-1 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      @if (isset($slot))
        {{ $slot }}
      @else
        @yield('content')
      @endif
    </div>
  </main>

  <footer class="border-t bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 text-xs text-gray-500">
      © {{ date('Y') }} {{ config('app.name','ESF') }} · ISOP GmbH
    </div>
  </footer>
</div>
</body>
</html>
