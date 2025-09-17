@extends('layouts.app')

@section('content')
    {{-- Gäste sehen ein Login-Fenster --}}
    @guest
        <div class="max-w-md mx-auto">
            <div class="bg-white rounded-2xl shadow p-6 border">
                <h1 class="text-xl font-semibold mb-4">Anmelden</h1>
                @if ($errors->any())
                    <div class="mb-3 text-sm text-red-600">{{ $errors->first() }}</div>
                @endif
                <form method="POST" action="{{ route('login') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm mb-1">E-Mail</label>
                        <input type="email" name="email" class="w-full border rounded px-3 py-2" required autofocus />
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Passwort</label>
                        <input type="password" name="password" class="w-full border rounded px-3 py-2" required />
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
                    <button class="w-full px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white">Login</button>
                </form>
            </div>
        </div>
        @php return; @endphp
    @endguest

    {{-- Überschrift + Schnellaktionen (+ Teilnehmer, + Gruppe) --}}
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Dashboard</h1>
        <div class="flex items-center gap-2">
            @can('teilnehmer.create')
                @if (Route::has('teilnehmer.create'))
                    <a href="{{ route('teilnehmer.create') }}"
                       class="px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm">+ Teilnehmer</a>
                @endif
            @endcan
            @can('gruppen.manage')
                @if (Route::has('gruppen.create'))
                    <a href="{{ route('gruppen.create') }}"
                       class="px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm">+ Gruppe</a>
                @endif
            @endcan
        </div>
    </div>

    @php
        use Illuminate\Support\Facades\DB;
        $teilnehmerCount = DB::table('teilnehmer')->count();
        $gruppenCount    = DB::table('gruppen')->count();
        $projekteCount   = DB::table('Projekte')->count();

        $today = \Carbon\Carbon::today()->toDateString();
        $anwToday = DB::table('teilnehmer_anwesenheit')
            ->selectRaw('SUM(CASE WHEN status=1 THEN 1 ELSE 0 END) AS present, SUM(CASE WHEN status=0 THEN 1 ELSE 0 END) AS absent')
            ->whereDate('datum', $today)
            ->first();

        // nächste Prüfungstermine
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
            ->selectRaw('DATE(datum) as d,
                         SUM(CASE WHEN status=1 THEN 1 ELSE 0 END) AS present,
                         COUNT(*) as total')
            ->whereBetween('datum', [$start, $end])
            ->groupBy('d')->orderBy('d')->get()->keyBy('d');

        $period = new \Carbon\CarbonPeriod($start, $end);
    @endphp

    {{-- KPI-Karten --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="rounded-2xl border p-4 shadow">
            <div class="text-sm text-gray-500">Teilnehmer</div>
            <div class="text-2xl font-semibold">{{ $teilnehmerCount }}</div>
        </div>
        <div class="rounded-2xl border p-4 shadow">
            <div class="text-sm text-gray-500">Gruppen</div>
            <div class="text-2xl font-semibold">{{ $gruppenCount }}</div>
        </div>
        <div class="rounded-2xl border p-4 shadow">
            <div class="text-sm text-gray-500">Projekte</div>
            <div class="text-2xl font-semibold">{{ $projekteCount }}</div>
        </div>
        <div class="rounded-2xl border p-4 shadow">
            <div class="text-sm text-gray-500">Heute anwesend</div>
            <div class="text-2xl font-semibold">{{ (int)($anwToday->present ?? 0) }}</div>
        </div>
        <div class="rounded-2xl border p-4 shadow">
            <div class="text-sm text-gray-500">Abwesend: {{ (int)($anwToday->absent ?? 0) }}</div>
            <div class="text-2xl font-semibold">&nbsp;</div>
        </div>
    </div>


 <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Teilnehmer --}}
    @if (Route::has('teilnehmer.index'))
        <a href="{{ route('teilnehmer.index') }}" class="px-4 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white">
            Teilnehmer
        </a>
    @endif

    {{-- Gruppen --}}
    @if (Route::has('gruppen.index'))
        <a href="{{ route('gruppen.index') }}" class="px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white">
            Gruppen
        </a>
    @endif

    {{-- Anwesenheit --}}
    @if (Route::has('anwesenheit.index'))
        <a href="{{ route('anwesenheit.index') }}" class="px-4 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white">
            Anwesenheit
        </a>
    @endif

    {{-- Prüfungstermine --}}
    @if (Route::has('pruefungstermin.index'))
        <a href="{{ route('pruefungstermin.index') }}" class="px-4 py-3 rounded-xl bg-amber-600 hover:bg-amber-700 text-white">
            Prüfungstermine
        </a>
    @endif

    {{-- Beratungen (Gruppen & Einzeln – Aufteilung erst auf der Beratungen-Seite) --}}
    @if (Route::has('beratung.index'))
        <a href="{{ route('beratung.index') }}" class="px-4 py-3 rounded-xl bg-teal-600 hover:bg-teal-700 text-white">
            Beratungen (Gruppen & Einzeln)
        </a>
    @endif

    {{-- Dokumente --}}
    @if (Route::has('dokumente.index'))
        <a href="{{ route('dokumente.index') }}" class="px-4 py-3 rounded-xl bg-slate-700 hover:bg-slate-800 text-white">
            Neue Bestätigung / Dokument erstellen
        </a>
    @endif

    {{-- Mitarbeiter (Kontaktdaten & Rollen) – wenn es eine eigene Mitarbeiter-Übersicht gibt, nimm mitarbeiter.index;
       sonst fallback auf Admin-User&Rollen --}}
    @if (Route::has('mitarbeiter.index'))
        <a href="{{ route('mitarbeiter.index') }}" class="px-4 py-3 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white">
            Mitarbeiter (Kontaktdaten & Rollen)
        </a>
    @elseif (Route::has('admin.users.index'))
        <a href="{{ route('admin.users.index') }}" class="px-4 py-3 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white">
            Mitarbeiter (Kontaktdaten & Rollen)
        </a>
    @endif

    {{-- Projekte --}}
    @if (Route::has('projekte.index'))
        <a href="{{ route('projekte.index') }}" class="px-4 py-3 rounded-xl bg-violet-600 hover:bg-violet-700 text-white">
            Projekte
        </a>
    @else
        <a href="#" class="px-4 py-3 rounded-xl bg-violet-600/60 text-white cursor-not-allowed" title="Bald verfügbar">
            Projekte (in Planung)
        </a>
    @endif

    {{-- Endbericht Statistik (derzeit leerer Link/Platzhalter) --}}
    @if (Route::has('endbericht.stats'))
        <a href="{{ route('endbericht.stats') }}" class="px-4 py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white">
            Endbericht Statistik
        </a>
    @else
        <a href="#" class="px-4 py-3 rounded-xl bg-rose-600/60 text-white cursor-not-allowed" title="Bald verfügbar">
            Endbericht Statistik (in Planung)
        </a>
    @endif

    {{-- Schnellzugriff: Bestätigung / Dokument erstellen --}}
    @can('dokumente.upload')
        @if (Route::has('dokumente.create'))
            <a href="{{ route('dokumente.create') }}" class="px-4 py-3 rounded-xl bg-fuchsia-600 hover:bg-fuchsia-700 text-white">
                Projekte (in Planung)
            </a>
        @endif
    @endcan
</div>




        {{-- Anwesenheit (Woche) --}}
        <div class="bg-white rounded-2xl shadow p-4 border">
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
                        <div class="h-2 bg-gray-200 rounded">
                            <div class="h-2 rounded bg-emerald-600" style="width: {{ $rate }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Kalender + rollenspezifische To-Dos --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        {{-- Einfacher Monatskalender --}}
        <div class="bg-white rounded-2xl shadow p-4 border lg:col-span-2">
            <h2 class="font-semibold mb-3">Kalender</h2>
            @php
                $today = \Carbon\Carbon::today();
                $current = \Carbon\Carbon::parse(request('month', $today->format('Y-m-01')));
                $start = $current->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::MONDAY);
                $end   = $current->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
                $periodCal = new \Carbon\CarbonPeriod($start, $end);
                $weekdays = ['Mo','Di','Mi','Do','Fr','Sa','So'];
            @endphp

            <div class="flex items-center justify-between mb-2">
                <a class="px-2 py-1 text-sm rounded bg-gray-100 hover:bg-gray-200"
                   href="{{ request()->fullUrlWithQuery(['month' => $current->copy()->subMonth()->format('Y-m-01')]) }}">«</a>
                <div class="font-medium">{{ $current->locale('de')->translatedFormat('F Y') }}</div>
                <a class="px-2 py-1 text-sm rounded bg-gray-100 hover:bg-gray-200"
                   href="{{ request()->fullUrlWithQuery(['month' => $current->copy()->addMonth()->format('Y-m-01')]) }}">»</a>
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
                    <div class="h-16 rounded border text-sm p-1
                        {{ $isCurrentMonth ? 'bg-white' : 'bg-gray-50 text-gray-400' }}
                        {{ $isToday ? 'ring-2 ring-blue-500' : '' }}">
                        <div class="text-right">{{ $day->day }}</div>
                        {{-- Platzhalter für Termine/ToDos --}}
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Rollenspezifische To-Dos --}}
        <div class="bg-white rounded-2xl shadow p-4 border">
            <h2 class="font-semibold mb-3">Wichtige To-Dos</h2>

            @role('Admin')
                <div class="mb-2 text-xs uppercase text-gray-500">Für Admin</div>
                <ul class="space-y-2 list-disc list-inside">
                    <li>Neue Mitarbeiter anlegen & Rollen zuweisen</li>
                    <li>Permissions prüfen/anpassen</li>
                    <li>Backups & Wartung (Monatsende)</li>
                </ul>
            @endrole

            @can('anwesenheit.manage')
                <div class="mt-4 mb-2 text-xs uppercase text-gray-500">Für Mitarbeiter</div>
                <ul class="space-y-2 list-disc list-inside">
                    <li>Heutige Anwesenheit eintragen</li>
                    <li>Praktikumsdaten aktualisieren</li>
                    <li>Beratungsprotokolle nachtragen</li>
                </ul>
            @endcan

            @can('beratung.manage')
                <div class="mt-4 mb-2 text-xs uppercase text-gray-500">Für Coach</div>
                <ul class="space-y-2 list-disc list-inside">
                    <li>Beratungstermine dieser Woche bestätigen</li>
                    <li>Dokumente/Unterlagen hochladen</li>
                </ul>
            @endcan
        </div>
    </div>
@endsection
