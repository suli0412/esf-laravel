@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Logs-Dashboard</h1>
            <p class="text-sm text-gray-500">Channel: <code>activity</code> — Datei: <span class="font-mono">{{ $filename }}</span></p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.logs.index') }}" class="bg-white border rounded-2xl p-4 shadow mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm mb-1">Datum</label>
                <input
                    type="date"
                    name="date"
                    value="{{ $date }}"
                    class="w-full border rounded px-3 py-2"
                />
            </div>
            <div>
                <label class="block text-sm mb-1">Level</label>
                <select name="level" class="w-full border rounded px-3 py-2">
                    <option value="">alle</option>
                    @foreach($levels as $lvl)
                        <option value="{{ $lvl }}" @selected($level === $lvl)>{{ strtoupper($lvl) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm mb-1">Suche (Message & Kontext)</label>
                <input
                    type="text"
                    name="q"
                    value="{{ $q }}"
                    placeholder="z.B. gruppe_id, user, message..."
                    class="w-full border rounded px-3 py-2"
                />
            </div>
        </div>

        <div class="mt-4 flex items-center gap-3">
            <button class="inline-flex items-center px-4 py-2 rounded-lg border bg-gray-900 text-white hover:bg-black">
                Filtern
            </button>
            <a href="{{ route('admin.logs.index') }}" class="text-sm text-gray-600 hover:underline">Zurücksetzen</a>
        </div>
    </form>

    @if(!$fileExists)
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-xl p-4">
            Für das gewählte Datum wurde keine Log-Datei gefunden (<span class="font-mono">{{ $filename }}</span>).
            Wähle ein anderes Datum oder prüfe, ob der Channel <code>activity</code> schreibt.
        </div>
    @else
        <div class="bg-white border rounded-2xl shadow overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-2 w-40">Zeit</th>
                        <th class="text-left px-4 py-2 w-20">Level</th>
                        <th class="text-left px-4 py-2">Message</th>
                        <th class="text-left px-4 py-2 w-72">Kontext</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($entries as $e)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 align-top">
                                <div class="font-mono">{{ $e['timestamp'] ?: '—' }}</div>
                            </td>
                            <td class="px-4 py-2 align-top">
                                @php $lvl = strtoupper($e['level'] ?? ''); @endphp
                                <span class="inline-flex items-center px-2 py-0.5 text-xs rounded border">
                                    {{ $lvl ?: '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-2 align-top">
                                <div class="font-medium">{{ $e['message'] }}</div>
                                <div class="text-xs text-gray-500">Env: {{ $e['env'] ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-2 align-top">
                                @if(!empty($e['context']))
                                    <pre class="text-xs bg-gray-50 border rounded p-2 overflow-x-auto">{{ json_encode($e['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                Keine Einträge für diese Filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="p-3 border-t">
                {{ $entries->links() }}
            </div>
        </div>
    @endif
</div>
@endsection
