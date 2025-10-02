<!doctype html>
<html lang="{{ str_replace('_','-',app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', config('app.name'))</title>
  @vite(['resources/css/app.css','resources/js/app.js'])
  <style>[x-cloak]{display:none!important}</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100">
  <div class="absolute inset-0 -z-10 opacity-20 pointer-events-none">
    <div class="h-[40rem] w-[40rem] bg-indigo-200/40 rounded-full blur-3xl absolute -top-20 -left-20"></div>
    <div class="h-[40rem] w-[40rem] bg-sky-200/40 rounded-full blur-3xl absolute -bottom-20 -right-24"></div>
  </div>

  <header class="max-w-7xl mx-auto px-4 py-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
      <div class="h-10 w-10 rounded-xl bg-indigo-600/90 text-white grid place-items-center font-bold">ESF</div>
      <div class="text-lg font-semibold text-slate-800">{{ config('app.name', 'ESF') }}</div>
    </div>
    @if (Route::has('login'))
      <nav class="text-sm">
        @auth
          <a href="{{ url('/') }}" class="px-3 py-2 rounded-lg bg-slate-900 text-white">Dashboard</a>
        @else
          <a href="{{ route('login') }}" class="px-3 py-2 rounded-lg bg-slate-900 text-white">Login</a>

        @endauth
      </nav>
    @endif
  </header>

  <main class="px-4">
    <div class="mx-auto max-w-md">
      @yield('content')
    </div>
  </main>

  <footer class="mt-12 pb-6 text-center text-xs text-slate-500">
    &copy; {{ date('Y') }} {{ config('app.name') }} · Alle Rechte vorbehalten
  </footer>
</body>
</html>
