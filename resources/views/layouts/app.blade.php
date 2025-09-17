<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name','ESF') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
<div class="min-h-screen flex flex-col">

    {{-- Topbar --}}
    <header class="bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <img src="{{ asset('images/org-logo.png') }}" alt="ESF" class="h-9 w-auto object-contain">
                <span class="font-semibold tracking-wide">ESF</span>
            </a>


            <nav class="flex items-center gap-2">
                @if (Route::has('dashboard'))
                    <a href="{{ route('dashboard') }}"
                       class="px-3 py-2 rounded-lg bg-slate-700 hover:bg-slate-800 text-white text-sm">
                        Dashboard
                    </a>
                @endif

                @can('teilnehmer.view')
                    @if (Route::has('teilnehmer.index'))
                        <a href="{{ route('teilnehmer.index') }}"
                           class="px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm">
                            Teilnehmer
                        </a>
                    @endif
                @endcan

                @can('reports.view')
                    @if (Route::has('reports.index'))
                        <a href="{{ route('reports.index') }}"
                           class="px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm">
                            Reports
                        </a>
                    @endif
                @endcan

                @auth
                    <div class="ml-3 flex items-center gap-2">
                        <span class="text-sm text-gray-600 hidden sm:inline">Hallo, {{ auth()->user()->name ?? 'Dev' }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="px-3 py-2 rounded-lg bg-gray-800 hover:bg-gray-900 text-white text-sm">
                                Logout
                            </button>
                        </form>
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
            © {{ date('Y') }} ESF · Alle Rechte vorbehalten
        </div>
    </footer>
</div>
</body>
</html>
