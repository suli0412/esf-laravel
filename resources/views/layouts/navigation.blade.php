<nav x-data="{ open: false }" class="sticky top-0 z-40 bg-white/80 backdrop-blur border-b border-gray-200 shadow-sm">
    @php
        $user = Auth::user();
        $isAdmin = $user && (
            (method_exists($user,'isAdmin') && $user->isAdmin())
            || (!method_exists($user,'isAdmin') && (($user->is_admin ?? false) || (method_exists($user,'hasRole') && $user->hasRole('admin'))))
        );

        // Primärnavigation (nur anzeigen, wenn Route existiert)
        $primaryLinks = [
            ['label' => 'Dashboard',        'route' => 'dashboard'],
            ['label' => 'Teilnehmer',       'route' => 'teilnehmer.index'],
            ['label' => 'Mitarbeiter',      'route' => 'mitarbeiter.index'],
            ['label' => 'Projekte',         'route' => 'projekte.index'],
            ['label' => 'Gruppen',          'route' => 'gruppen.index'],
            ['label' => 'Anwesenheit',      'route' => 'anwesenheit.index'],
            ['label' => 'Beratungen',       'route' => 'beratungen.index'],
            ['label' => 'Prüfungstermine',  'route' => 'pruefungstermine.index'],
            ['label' => 'Dokumente',        'route' => 'dokumente.index'],
        ];
        $primaryLinks = array_values(array_filter($primaryLinks, fn($l) => Route::has($l['route'])));

        // Admin-Links (nur für Admins, nur falls vorhanden)
        $adminLinks = [
            ['label' => 'Kompetenzen',          'route' => 'admin.kompetenzen.index'],
            ['label' => 'Niveaus',              'route' => 'admin.niveaus.index'],
            ['label' => 'Prüfungstermine (Admin)', 'route' => 'admin.pruefungstermine.index'],
            ['label' => 'Benutzer',             'route' => 'users.index'],
        ];
        $adminLinks = array_values(array_filter($adminLinks, fn($l) => Route::has($l['route'])));

        // kleine Helper
        $isActive = fn($name) => request()->routeIs($name);
        $initials = function($name) {
            $parts = preg_split('/\s+/', trim((string)$name));
            $i = strtoupper(mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[1] ?? '', 0, 1));
            return $i ?: 'U';
        };
    @endphp

    <!-- Primary Navigation -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
            <div class="flex items-center gap-6">
                <!-- Logo -->
                <div class="shrink-0">
                    <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}" class="inline-flex items-center gap-2">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-900" />
                        <span class="hidden sm:inline text-gray-900 font-semibold tracking-tight">ESF</span>
                    </a>
                </div>

                <!-- Desktop: Links -->
                <div class="hidden md:flex md:items-center md:gap-1">
                    @foreach ($primaryLinks as $link)
                        <x-nav-link :href="route($link['route'])" :active="$isActive($link['route'])">
                            {{ __($link['label']) }}
                        </x-nav-link>
                    @endforeach
                </div>
            </div>

            <!-- Right: Auth / User -->
            <div class="flex items-center gap-3">
                @guest
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}"
                           class="text-sm font-medium text-gray-600 hover:text-gray-900">
                            Anmelden
                        </a>
                    @endif
                    @if (Route::has('register') && config('esf.allow_registration', false))
                        <a href="{{ route('register') }}"
                           class="hidden sm:inline-block text-sm font-medium text-white bg-gray-900 hover:bg-gray-800 px-3 py-2 rounded-md">
                            Registrieren
                        </a>
                    @endif
                @endguest

                @auth
                    <!-- Admin Badge -->
                    @if ($isAdmin)
                        <span class="hidden sm:inline-flex items-center rounded-full border border-amber-300 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800">
                            Admin
                        </span>
                    @endif

                    <!-- User Dropdown -->
                    <x-dropdown align="right" width="56">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-2 px-2.5 py-2 rounded-md border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none">
                                <span class="hidden sm:inline">{{ Auth::user()->name }}</span>
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-gray-900 text-white text-xs font-semibold">
                                    {{ $initials(Auth::user()->name) }}
                                </span>
                                <svg class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @if (Route::has('profile.edit'))
                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('Profil') }}
                                </x-dropdown-link>
                            @endif

                            @if ($isAdmin && count($adminLinks))
                                <div class="px-3 pt-2 pb-1 text-xs font-semibold text-gray-500">Admin</div>
                                @foreach ($adminLinks as $alink)
                                    <x-dropdown-link :href="route($alink['route'])">
                                        {{ __($alink['label']) }}
                                    </x-dropdown-link>
                                @endforeach
                            @endif

                            <div class="border-t my-2"></div>

                            <!-- Logout -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                                 onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Abmelden') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @endauth

                <!-- Hamburger (mobile) -->
                <button @click="open = !open"
                        class="md:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none"
                        aria-label="Menu">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div :class="{'block': open, 'hidden': !open}" class="md:hidden hidden border-t border-gray-200 bg-white">
        <div class="px-4 py-3 space-y-1">
            @foreach ($primaryLinks as $link)
                <x-responsive-nav-link :href="route($link['route'])" :active="$isActive($link['route'])">
                    {{ __($link['label']) }}
                </x-responsive-nav-link>
            @endforeach
        </div>

        @auth
            <div class="border-t border-gray-200"></div>
            <div class="px-4 py-3">
                <div class="font-medium text-base text-gray-900">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="px-4 pb-3 space-y-1">
                @if (Route::has('profile.edit'))
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profil') }}
                    </x-responsive-nav-link>
                @endif

                @if ($isAdmin && count($adminLinks))
                    <div class="px-2 pt-2 pb-1 text-xs font-semibold text-gray-500">Admin</div>
                    @foreach ($adminLinks as $alink)
                        <x-responsive-nav-link :href="route($alink['route'])">
                            {{ __($alink['label']) }}
                        </x-responsive-nav-link>
                    @endforeach
                @endif

                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Abmelden') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        @endauth

        @guest
            <div class="px-4 py-3 border-t space-y-1">
                @if (Route::has('login'))
                    <x-responsive-nav-link :href="route('login')">
                        {{ __('Anmelden') }}
                    </x-responsive-nav-link>
                @endif
                @if (Route::has('register') && config('esf.allow_registration', false))
                    <x-responsive-nav-link :href="route('register')">
                        {{ __('Registrieren') }}
                    </x-responsive-nav-link>
                @endif
            </div>
        @endguest
    </div>
</nav>
