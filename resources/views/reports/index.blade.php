@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Reports</h1>

        <form method="GET" action="{{ route('reports.index') }}" class="flex items-center gap-2">
            <input type="date" name="date_from" value="{{ $from }}" class="border rounded px-2 py-1">
            <span class="text-gray-500">bis</span>
            <input type="date" name="date_to" value="{{ $to }}" class="border rounded px-2 py-1">
            <button class="px-3 py-1.5 rounded bg-blue-600 hover:bg-blue-700 text-white">Filtern</button>

            @if (Route::has('reports.export'))
                <a href="{{ route('reports.export', ['date_from'=>$from,'date_to'=>$to]) }}"
                   class="px-3 py-1.5 rounded bg-gray-800 hover:bg-gray-900 text-white">
                    CSV Export
                </a>
            @endif
        </form>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="rounded-2xl border p-4 shadow">
            <div class="text-sm text-gray-500">Teilnehmer (gesamt)</div>
            <div class="text-2xl font-semibold">{{ number_format($kpis['teilnehmer_total']) }}</div>
        </div>
        <div class="rounded-2xl border p-4 shadow">
            <div class="text-sm text-gray-500">Gruppen (gesamt)</div>
            <div class="text-2xl font-semibold">{{ number_format($kpis['gruppen_total']) }}</div>
        </div>
        <div class="rounded-2xl border p-4 shadow">
            <div class="text-sm text-gray-500">Projekte (gesamt)</div>
            <div class="text-2xl font-semibold">{{ number_format($kpis['projekte_total']) }}</div>
        </div>
        <div class="rounded-2xl border p-4 shadow">
            <div class="text-sm text-gray-500">Anwesenheitsquote (ges.)</div>
            <div class="text-2xl font-semibold">
                {{ $kpis['anwesenheit_rate'] !== null ? $kpis['anwesenheit_rate'].' %' : '—' }}
            </div>
        </div>
    </div>

    {{-- Tagesübersicht --}}
    <div class="rounded-2xl border overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
            <tr>
                <th class="text-left px-4 py-2">Datum</th>
                <th class="text-right px-4 py-2">Neue TN</th>
                <th class="text-right px-4 py-2">Beratungen</th>
                <th class="text-right px-4 py-2">Beratungsstunden</th>
                <th class="text-right px-4 py-2">Dok.-Uploads</th>
                <th class="text-right px-4 py-2">Anw. (anwesend)</th>
                <th class="text-right px-4 py-2">Anw. (gesamt)</th>
                <th class="text-right px-4 py-2">Quote</th>
            </tr>
            </thead>
            <tbody>
            @forelse($rows as $r)
                <tr class="border-t">
                    <td class="px-4 py-2">{{ $r['date'] }}</td>
                    <td class="px-4 py-2 text-right">{{ $r['new_participants'] }}</td>
                    <td class="px-4 py-2 text-right">{{ $r['consultations'] }}</td>
                    <td class="px-4 py-2 text-right">{{ number_format($r['consultation_hours'], 2, ',', '.') }}</td>
                    <td class="px-4 py-2 text-right">{{ $r['docs_uploaded'] }}</td>
                    <td class="px-4 py-2 text-right">{{ $r['present'] }}</td>
                    <td class="px-4 py-2 text-right">{{ $r['attendance_total'] }}</td>
                    <td class="px-4 py-2 text-right">
                        {{ $r['attendance_rate'] !== null ? $r['attendance_rate'].' %' : '—' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                        Keine Daten im gewählten Zeitraum.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
