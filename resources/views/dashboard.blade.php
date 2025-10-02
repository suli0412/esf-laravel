@extends('layouts.app')

@section('content')
    {{-- Gäste sehen ein Login-Fenster --}}
    @guest
        <div class="min-h-[60vh] grid place-items-center">
            <div class="w-full max-w-md bg-white rounded-2xl shadow p-6 border">
                <div class="flex items-center gap-3 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-blue-600">
                        <path fill-rule="evenodd" d="M11.25 4.5a.75.75 0 01.75-.75h2.25a3 3 0 013 3v11.25a.75.75 0 01-1.28.53l-2.72-2.72a.75.75 0 00-.53-.22H12a.75.75 0 01-.75-.75V4.5zM9 6.75H6.75a.75.75 0 00-.75.75v9a.75.75 0 00.75.75H9A2.25 2.25 0 0011.25 15V9A2.25 2.25 0 009 6.75z" clip-rule="evenodd" />
                    </svg>
                    <h1 class="text-xl font-semibold">Anmelden</h1>
                </div>

                @if ($errors->any())
                    <div class="mb-3 text-sm text-red-600">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm mb-1">E-Mail</label>
                        <input type="email" name="email" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required autofocus />
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Passwort</label>
                        <input type="password" name="password" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required />
                    </div>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="remember" class="rounded border-gray-300"> Angemeldet bleiben
                        </label>
                        @if (Route::has('password.request'))
                            <a class="text-sm text-blue-600 hover:underline" href="{{ route('password.request') }}">
                                Passwort vergessen?
                            </a>
                        @endif
                    </div>
                    <button class="w-full px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white">Login</button>
                </form>
            </div>
        </div>
        @php return; @endphp
    @endguest

    @php
        use Illuminate\Support\Facades\DB;

        $teilnehmerCount = DB::table('teilnehmer')->count();
        $gruppenCount    = DB::table('gruppen')->count();
        $projekteCount   = DB::table('projekte')->count();

        $today = \Carbon\Carbon::today()->toDateString();

        // Status-Strings korrekt zählen
        $anwToday = DB::table('teilnehmer_anwesenheit')
            ->selectRaw("
                SUM(CASE WHEN status IN ('anwesend','anwesend_verspaetet') THEN 1 ELSE 0 END) AS present,
                SUM(CASE WHEN status = 'abwesend' THEN 1 ELSE 0 END) AS absent
            ")
            ->whereDate('datum', $today)
            ->first();

        // nächste Prüfungstermine (Tabellenname wie bisher)
        $pruefungen = DB::table('pruefungstermin')
            ->select('termin_id','datum','institut','bezeichnung','titel','niveau_id')
            ->whereDate('datum', '>=', $today)
            ->orderBy('datum')
            ->limit(4)
            ->get();

        // Anwesenheit (Woche)
        $start = \Carbon\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
        $end   = \Carbon\Carbon::now()->endOfWeek(\Carbon\Carbon::SUNDAY)->toDateString();
        $week  = DB::table('teilnehmer_anwesenheit')
            ->selectRaw("
                DATE(datum) as d,
                SUM(CASE WHEN status IN ('anwesend','anwesend_verspaetet') THEN 1 ELSE 0 END) AS present,
                COUNT(*) as total
            ")
            ->whereBetween('datum', [$start, $end])
            ->groupBy('d')
            ->orderBy('d')
            ->get()
            ->keyBy('d');

        $period = new \Carbon\CarbonPeriod($start, $end);
    @endphp

    <div class="space-y-6">
        {{-- Header + Schnellaktionen --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Dashboard</h1>
                <p class="text-sm text-gray-500">Schneller Überblick & Aktionen</p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @can('teilnehmer.create')
                    @if (Route::has('teilnehmer.create'))
                        <a href="{{ route('teilnehmer.create') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm shadow">
                            <span>+ Teilnehmer</span>
                        </a>
                    @endif
                @endcan

                @can('gruppen.manage')
                    @if (Route::has('gruppen.create'))
                        <a href="{{ route('gruppen.create') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm shadow">
                            <span>+ Gruppe</span>
                        </a>
                    @endif
                @endcan

                @can('gruppen.manage')
                    @if (Route::has('projekte.create'))
                        <a href="{{ route('projekte.create') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm shadow">
                            <span>+ Projekt</span>
                        </a>
                    @endif
                @endcan

                {{-- Logs Schnellzugriff (wenn Route + Recht vorhanden) --}}
                @can('audit.view')
                    @if(Route::has('admin.logs.index'))
                        <a href="{{ route('admin.logs.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-700 hover:bg-slate-800 text-white text-sm shadow">
                            <span> Logs</span>
                        </a>
                    @elseif(Route::has('activity.index'))
                        <a href="{{ route('activity.index') }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-700 hover:bg-slate-800 text-white text-sm shadow">
                            <span> Logs</span>
                        </a>
                    @endif
                @endcan
            </div>
        </div>



        {{-- KPIs --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="rounded-2xl border p-4 shadow bg-white">
                <div class="text-sm text-gray-500">Teilnehmer</div>
                <div class="mt-1 flex items-baseline gap-2">
                    <div class="text-2xl font-semibold">{{ $teilnehmerCount }}</div>
                </div>
            </div>
            <div class="rounded-2xl border p-4 shadow bg-white">
                <div class="text-sm text-gray-500">Gruppen</div>
                <div class="mt-1 text-2xl font-semibold">{{ $gruppenCount }}</div>
            </div>
            <div class="rounded-2xl border p-4 shadow bg-white">
                <div class="text-sm text-gray-500">Projekte</div>
                <div class="mt-1 text-2xl font-semibold">{{ $projekteCount }}</div>
            </div>
            <div class="rounded-2xl border p-4 shadow bg-white">
                <div class="text-sm text-gray-500">Heute anwesend</div>
                <div class="mt-1 text-2xl font-semibold">{{ (int)($anwToday->present ?? 0) }}</div>
            </div>
            <div class="rounded-2xl border p-4 shadow bg-white">
                <div class="text-sm text-gray-500">Heute abwesend</div>
                <div class="mt-1 text-2xl font-semibold">{{ (int)($anwToday->absent ?? 0) }}</div>
            </div>
        </div>

        {{-- Hauptgitter: Navigation / Anwesenheit / Termine --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            {{-- Navigationstafeln --}}
            <div class="xl:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">

                @if (Route::has('teilnehmer.index'))
                    <a href="{{ route('teilnehmer.index') }}" class="group rounded-2xl border bg-white p-5 shadow hover:shadow-md transition">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl p-3 bg-blue-50">👤</div>
                            <div>
                                <div class="font-semibold">Teilnehmer</div>
                                <div class="text-sm text-gray-500">Listen, Suchen, Anlegen, Bearbeiten</div>
                            </div>
                        </div>
                    </a>
                @endif

                @if (Route::has('gruppen.index'))
                    <a href="{{ route('gruppen.index') }}" class="group rounded-2xl border bg-white p-5 shadow hover:shadow-md transition">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl p-3 bg-emerald-50">👥</div>
                            <div>
                                <div class="font-semibold">Gruppen</div>
                                <div class="text-sm text-gray-500">Zuweisungen & Übersicht</div>
                            </div>
                        </div>
                    </a>
                @endif

                @if (Route::has('projekte.index'))
                    <a href="{{ route('projekte.index') }}" class="group rounded-2xl border bg-white p-5 shadow hover:shadow-md transition">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl p-3 bg-violet-50">📁</div>
                            <div>
                                <div class="font-semibold">Projekte</div>
                                <div class="text-sm text-gray-500">Verwaltung & Details</div>
                            </div>
                        </div>
                    </a>
                @endif

                @if (Route::has('anwesenheit.index'))
                    <a href="{{ route('anwesenheit.index') }}" class="group rounded-2xl border bg-white p-5 shadow hover:shadow-md transition">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl p-3 bg-indigo-50">🗓️</div>
                            <div>
                                <div class="font-semibold">Anwesenheit</div>
                                <div class="text-sm text-gray-500">Heute eintragen & prüfen</div>
                            </div>
                        </div>
                    </a>
                @endif

                @if (Route::has('pruefungstermine.index'))
                    <a href="{{ route('pruefungstermine.index') }}" class="group rounded-2xl border bg-white p-5 shadow hover:shadow-md transition">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl p-3 bg-amber-50">🎓</div>
                            <div>
                                <div class="font-semibold">Prüfungstermine</div>
                                <div class="text-sm text-gray-500">Termine & Niveaus</div>
                            </div>
                        </div>
                    </a>
                @endif

                @if (Route::has('beratungen.index'))
                    <a href="{{ route('beratungen.index') }}" class="group rounded-2xl border bg-white p-5 shadow hover:shadow-md transition">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl p-3 bg-teal-50">💬</div>
                            <div>
                                <div class="font-semibold">Beratungen (Gruppen & Einzeln)</div>
                                <div class="text-sm text-gray-500">Planen & protokollieren</div>
                            </div>
                        </div>
                    </a>
                @endif

                @if (Route::has('dokumente.index'))
                    <a href="{{ route('dokumente.index') }}" class="group rounded-2xl border bg-white p-5 shadow hover:shadow-md transition">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl p-3 bg-slate-100">📄</div>
                            <div>
                                <div class="font-semibold">Dokumente</div>
                                <div class="text-sm text-gray-500">Bestätigungen & Vorlagen</div>
                            </div>
                        </div>
                    </a>
                @endif

                {{-- Mitarbeiter / Adminbereich --}}
                @if (Route::has('mitarbeiter.index'))
                    <a href="{{ route('mitarbeiter.index') }}" class="group rounded-2xl border bg-white p-5 shadow hover:shadow-md transition">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl p-3 bg-cyan-50">🧑‍💼</div>
                            <div>
                                <div class="font-semibold">Mitarbeiter</div>
                                <div class="text-sm text-gray-500">Kontaktdaten & Rollen</div>
                            </div>
                        </div>
                    </a>
                @elseif (Route::has('admin.users.index'))
                    @can('users.view')
                        <a href="{{ route('admin.users.index') }}" class="group rounded-2xl border bg-white p-5 shadow hover:shadow-md transition">
                            <div class="flex items-center gap-3">
                                <div class="rounded-xl p-3 bg-cyan-50">🧑‍💼</div>
                                <div>
                                    <div class="font-semibold">Admin: Benutzer & Rollen</div>
                                    <div class="text-sm text-gray-500">Verwalten & Berechtigen</div>
                                </div>
                            </div>
                        </a>
                    @endcan
                @endif

                {{-- Aktivitäts-Logs --}}
                @can('audit.view')
                    @if(Route::has('admin.logs.index') || Route::has('activity.index'))
                        <a href="{{ Route::has('admin.logs.index') ? route('admin.logs.index') : route('activity.index') }}"
                           class="group rounded-2xl border bg-white p-5 shadow hover:shadow-md transition">
                            <div class="flex items-center gap-3">
                                <div class="rounded-xl p-3 bg-gray-100">📜</div>
                                <div>
                                    <div class="font-semibold">Aktivitäts-Logs</div>
                                    <div class="text-sm text-gray-500">Wer hat was gemacht?</div>
                                </div>
                            </div>
                        </a>
                    @endif
                @endcan

                {{-- Platzhalter für künftige Karten --}}
                @if (Route::has('endbericht.stats'))
                    <a href="{{ route('endbericht.stats') }}" class="group rounded-2xl border bg-white p-5 shadow hover:shadow-md transition">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl p-3 bg-rose-50">📊</div>
                            <div>
                                <div class="font-semibold">Endbericht Statistik</div>
                                <div class="text-sm text-gray-500">Auswertung (Beta)</div>
                            </div>
                        </div>
                    </a>
                @else
                    <div class="rounded-2xl border bg-white p-5 opacity-60 cursor-not-allowed">
                        <div class="flex items-center gap-3">
                            <div class="rounded-xl p-3 bg-rose-50">📊</div>
                            <div>
                                <div class="font-semibold">Endbericht Statistik</div>
                                <div class="text-sm text-gray-500">Bald verfügbar</div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Rechte Spalte: Woche & Termine --}}
            <div class="space-y-6">
                {{-- Anwesenheit (Woche) --}}
                <div class="bg-white rounded-2xl shadow p-5 border">
                    <h2 class="font-semibold mb-3">Anwesenheit (Woche)</h2>
                    <div class="space-y-2">
                        @foreach($period as $day)
                            @php
                                $key = $day->toDateString();
                                $present = (int)($week[$key]->present ?? 0);
                                $total   = (int)($week[$key]->total ?? 0);
                                $rate    = $total > 0 ? round($present / max(1,$total) * 100) : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between text-xs text-gray-500">
                                    <span>{{ $day->format('D d.m') }}</span>
                                    <span>{{ $present }}/{{ $total }} ({{ $rate }}%)</span>
                                </div>
                                <div class="h-2 bg-gray-200 rounded overflow-hidden">
                                    <div class="h-2 rounded bg-emerald-600" style="width: {{ $rate }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Nächste Prüfungstermine --}}
                <div class="bg-white rounded-2xl shadow p-5 border">
                    <h2 class="font-semibold mb-3">Nächste Prüfungstermine</h2>
                    @if($pruefungen->isEmpty())
                        <div class="text-sm text-gray-500">Keine anstehenden Termine.</div>
                    @else
                        <ul class="divide-y">
                            @foreach($pruefungen as $p)
                                <li class="py-2 flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-sm font-medium">{{ $p->titel ?? $p->bezeichnung }}</div>
                                        <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($p->datum)->format('d.m.Y') }} • {{ $p->institut }}</div>
                                    </div>
                                    @if (Route::has('pruefungstermine.index'))
                                        <a href="{{ route('pruefungstermine.index') }}" class="text-xs px-2 py-1 rounded bg-amber-100">Details</a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kalender + To-Dos --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Einfacher Monatskalender --}}
            <div class="bg-white rounded-2xl shadow p-5 border lg:col-span-2">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold">Kalender</h2>
                    @php
                        $today = \Carbon\Carbon::today();
                        $current = \Carbon\Carbon::parse(request('month', $today->format('Y-m-01')));
                        $start = $current->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::MONDAY);
                        $end   = $current->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
                        $periodCal = new \Carbon\CarbonPeriod($start, $end);
                        $weekdays = ['Mo','Di','Mi','Do','Fr','Sa','So'];
                    @endphp
                    <div class="flex items-center gap-2">
                        <a class="px-2 py-1 text-sm rounded bg-gray-100 hover:bg-gray-200" href="{{ request()->fullUrlWithQuery(['month' => $current->copy()->subMonth()->format('Y-m-01')]) }}">«</a>
                        <div class="text-sm text-gray-600">{{ $current->locale('de')->translatedFormat('F Y') }}</div>
                        <a class="px-2 py-1 text-sm rounded bg-gray-100 hover:bg-gray-200" href="{{ request()->fullUrlWithQuery(['month' => $current->copy()->addMonth()->format('Y-m-01')]) }}">»</a>
                    </div>
                </div>
                <div class="grid grid-cols-7 text-xs text-gray-500 mb-1">
                    @foreach($weekdays as $wd)
                        <div class="px-2 py-1">{{ $wd }}</div>
                    @endforeach
                </div>
                <div class="grid grid-cols-7 gap-1">
                    @foreach($periodCal as $day)
                        @php
                            $isCurrentMonth = $day->month === $current->month;
                            $isToday = $day->isSameDay($today);
                        @endphp
                        <div class="h-20 rounded border text-sm p-1 {{ $isCurrentMonth ? 'bg-white' : 'bg-gray-50 text-gray-400' }} {{ $isToday ? 'ring-2 ring-blue-500' : '' }}">
                            <div class="text-right">{{ $day->day }}</div>
                            {{-- Platzhalter für Termine/ToDos --}}
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Rollenspezifische To-Dos --}}
            <div class="bg-white rounded-2xl shadow p-5 border">
                <h2 class="font-semibold mb-3">Wichtige To-Dos</h2>

                @role('Admin')
                    <div class="mb-2 text-xs uppercase text-gray-500">Für Admin</div>
                    <ul class="space-y-2 list-disc list-inside text-sm">
                        <li>Neue Mitarbeiter anlegen & Rollen zuweisen</li>
                        <li>Permissions prüfen/anpassen</li>
                        <li>Backups & Wartung (Monatsende)</li>
                    </ul>
                @endrole

                @can('anwesenheit.manage')
                    <div class="mt-4 mb-2 text-xs uppercase text-gray-500">Für Mitarbeiter</div>
                    <ul class="space-y-2 list-disc list-inside text-sm">
                        <li>Heutige Anwesenheit eintragen</li>
                        <li>Praktikumsdaten aktualisieren</li>
                        <li>Beratungsprotokolle nachtragen</li>
                    </ul>
                @endcan

                @can('beratung.manage')
                    <div class="mt-4 mb-2 text-xs uppercase text-gray-500">Für Coach</div>
                    <ul class="space-y-2 list-disc list-inside text-sm">
                        <li>Beratungstermine dieser Woche bestätigen</li>
                        <li>Dokumente/Unterlagen hochladen</li>
                    </ul>
                @endcan
            </div>
        </div>
    </div>
@endsection
