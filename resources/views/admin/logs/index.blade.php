@extends('layouts.app')

@section('content')

@php
    use Carbon\Carbon;

    // Datumsformatierer
    $fmtDate = function ($v) {
        if (!$v) return '—';
        try { return Carbon::parse($v)->format('d.m.Y'); }
        catch (\Throwable $e) { return (string)$v; }
    };

    // Sicherer Link-Helfer für teilnehmer.show
    $safeTeilnehmerLink = function ($tid, $label = null) {
        $tid = is_numeric($tid) ? (int)$tid : null;
        if (!$tid) return '—';
        $label = $label ?? $tid;
        return '<a class="underline" href="'.e(route('teilnehmer.show', $tid)).'">'.e($label).'</a>';
    };
@endphp

@php
    // Fallbacks, falls Controller-Variablen fehlen
    $channel     = $channel     ?? 'db';              // 'db' | 'file'
    $date        = $date        ?? now()->toDateString();
    $perPage     = isset($perPage) ? (int)$perPage : 50;
    $q           = $q           ?? '';
    $filename    = $filename    ?? null;              // nur bei channel=file relevant
    $filePreview = $filePreview ?? null;              // Textauszug aus Log-Datei
    $count       = $count       ?? 0;
@endphp

<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Logs-Dashboard</h1>
            <p class="text-sm text-gray-500">
                Channel:
                <code>{{ $channel }}</code>
                — Quelle:
                <span class="font-mono">
                    {{ $channel === 'file'
                        ? ($filename ? basename($filename) : '—')
                        : 'Datenbank: activity_log' }}
                </span>
                — Gesamt: <span class="font-mono">{{ number_format($count, 0, ',', '.') }}</span>
            </p>
        </div>
    </div>

    {{-- Filter/Form --}}
    <form method="GET" action="{{ route('admin.logs.index') }}" class="bg-white border rounded-2xl p-4 shadow mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm mb-1">Channel</label>
                <select name="channel" class="w-full border rounded px-3 py-2">
                    <option value="db"   {{ $channel==='db'?'selected':'' }}>Datenbank (activity_log)</option>
                    <option value="file" {{ $channel==='file'?'selected':'' }}>Datei (storage/logs)</option>
                </select>
            </div>

            <div>
                <label class="block text-sm mb-1">Datum (nur Datei)</label>
                <input
                    type="date"
                    name="date"
                    value="{{ $date }}"
                    class="w-full border rounded px-3 py-2"
                />
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm mb-1">Suche</label>
                <input
                    type="text"
                    name="q"
                    value="{{ $q }}"
                    placeholder="z. B. user, event, description, ID…"
                    class="w-full border rounded px-3 py-2"
                />
            </div>

            <div>
                <label class="block text-sm mb-1">Pro Seite (DB)</label>
                <select name="perPage" class="w-full border rounded px-3 py-2">
                    @foreach([25,50,100,200,500] as $n)
                        <option value="{{ $n }}" {{ (int)$perPage===$n?'selected':'' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-4 flex items-center gap-3">
            <button class="inline-flex items-center px-4 py-2 rounded-lg border bg-gray-900 text-white hover:bg-black">
                Filtern
            </button>
            <a href="{{ route('admin.logs.index') }}" class="text-sm text-gray-600 hover:underline">Zurücksetzen</a>
        </div>
    </form>

    {{-- Ansicht: Datei-Channel --}}
    @if($channel === 'file')
        @if(!$filename || !is_file($filename))
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl p-4">
                Für das gewählte Datum wurde keine Log-Datei gefunden
                (<span class="font-mono">{{ $filename ?? '—' }}</span>).
                Wähle ein anderes Datum oder prüfe die Log-Konfiguration.
            </div>
        @else
            <div class="bg-white border rounded-2xl shadow overflow-hidden">
                <div class="p-3 border-b flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Datei: <span class="font-mono">{{ $filename }}</span>
                    </div>
                    <div class="text-sm text-gray-600">
                        Zeilen gesamt: <span class="font-mono">{{ number_format($count, 0, ',', '.') }}</span>
                    </div>
                </div>
                <div class="p-0">
                    @if($filePreview !== null && $filePreview !== '')
                        <pre class="text-xs bg-gray-50 leading-5 p-4 overflow-auto whitespace-pre-wrap">{{ $filePreview }}</pre>
                    @else
                        <p class="p-4 text-sm text-gray-500">Keine passenden Zeilen (Filter?)</p>
                    @endif
                </div>
            </div>
        @endif

    {{-- Ansicht: DB-Channel --}}
    @else
        <div class="bg-white border rounded-2xl shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-2 w-44">Zeit</th>
                        <th class="text-left px-4 py-2 w-28">Log</th>
                        <th class="text-left px-4 py-2 w-32">Event</th>
                        <th class="text-left px-4 py-2 w-72">User</th>
                        <th class="text-left px-4 py-2">Description / Properties</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($items as $row)
                        <tr class="hover:bg-gray-50 align-top">
                            <td class="px-4 py-2 font-mono text-xs">
                                {{ optional($row->created_at)->format('Y-m-d H:i:s') ?? '—' }}
                            </td>
                            <td class="px-4 py-2">
                                <code>{{ $row->log_name ?? 'activity' }}</code>
                            </td>
                            <td class="px-4 py-2">
                                <span class="inline-flex items-center px-2 py-0.5 text-xs rounded border">
                                    {{ $row->event ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                {{ optional($row->causer)->name ?? '—' }}
                                @if(!empty($row->causer_id))
                                    <div class="text-xs text-gray-500">ID: {{ $row->causer_id }}</div>
                                @endif
                                @if(!empty($row->subject_type) || !empty($row->subject_id))
                                    <div class="text-xs text-gray-500">
                                        Subj: {{ class_basename($row->subject_type) ?? '—' }} #{{ $row->subject_id ?? '—' }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                <div class="font-medium">{{ $row->description ?? '—' }}</div>

                                @php
                                    $isAnwesenheit = str_contains((string)$row->subject_type, 'TeilnehmerAnwesenheit');
                                    $attr = data_get($row->properties, 'attributes', []);
                                @endphp

                                @if($isAnwesenheit && !empty($attr))
                                    <div class="text-xs space-y-1">
                                        <div>
                                            <span class="text-gray-500">Datum:</span>
                                            <span class="font-mono">{{ $fmtDate(data_get($attr,'datum')) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Status:</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded border text-[11px]">
                                                {{ ucfirst((string) data_get($attr,'status','—')) }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Fehlminuten:</span>
                                            <span class="font-mono">{{ (int) data_get($attr,'fehlminuten',0) }}</span>
                                        </div>

                                        @php
                                            $tid = data_get($attr, 'teilnehmer_id');
                                            $sid = $row->subject_id ?? null;
                                        @endphp

                                        <div class="text-gray-500">
                                            Teilnehmer&nbsp;ID: {!! $safeTeilnehmerLink($tid) !!}
                                        </div>

                                        @if($sid)
                                            <div class="text-gray-500">
                                                Anwesenheit&nbsp;ID:
                                                @if(is_numeric($tid))
                                                    {!! $safeTeilnehmerLink($tid, $sid) !!}
                                                @else
                                                    <span class="text-gray-400">{{ $sid }}</span>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="text-gray-500">
                                            Erstellt:
                                            <span class="font-mono">{{ $fmtDate(data_get($attr,'created_at')) }}</span>
                                            — von User&nbsp;ID: <span class="font-mono">{{ data_get($attr,'created_by','—') }}</span>
                                        </div>

                                        {{-- Debug-Link: Rohdaten ein-/ausblendbar --}}
                                        <details class="mt-1">
                                            <summary class="cursor-pointer text-gray-500">Rohdaten</summary>
                                            <pre class="text-[11px] bg-gray-50 border rounded p-2 overflow-x-auto">
{{ json_encode($row->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                                            </pre>
                                        </details>
                                    </div>
                                @else
                                    {{-- Fallback: andere Events weiterhin als JSON --}}
                                    @if(!empty($row->properties))
                                        <pre class="text-xs bg-gray-50 border rounded p-2 mt-1 overflow-x-auto">
{{ json_encode($row->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                                        </pre>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                @endif

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                Keine Einträge für diese Filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($items instanceof \Illuminate\Contracts\Pagination\Paginator)
                <div class="p-3 border-t">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
