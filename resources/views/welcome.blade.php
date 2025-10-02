{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-900">Dashboard</h2>
            <div class="hidden sm:flex gap-2">
                @if (Route::has('teilnehmer.create'))
                    <a href="{{ route('teilnehmer.create') }}" class="inline-flex items-center px-3 py-2 rounded-md bg-gray-900 text-white text-sm hover:bg-gray-800">+ Teilnehmer</a>
                @endif
                @if (Route::has('gruppen.create'))
                    <a href="{{ route('gruppen.create') }}" class="inline-flex items-center px-3 py-2 rounded-md border text-sm hover:bg-gray-50">+ Gruppe</a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- Stat Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="rounded-2xl border bg-white p-5">
                    <div class="text-sm text-gray-500">Teilnehmer</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $counts['teilnehmer'] ?? 0 }}</div>
                </div>
                <div class="rounded-2xl border bg-white p-5">
                    <div class="text-sm text-gray-500">Gruppen</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $counts['gruppen'] ?? 0 }}</div>
                </div>
                <div class="rounded-2xl border bg-white p-5">
                    <div class="text-sm text-gray-500">Projekte</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $counts['projekte'] ?? 0 }}</div>
                </div>
            </div>

        </div>
    </div>
            {{-- Nächste Prüfungstermine --}}
            <div class="rounded-2xl border bg-white p-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Nächste Prüfungstermine</h3>
                    @if (Route::has('pruefungstermine.index'))
                        <a href="{{ route('pruefungstermine.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Alle anzeigen →</a>
                    @endif
                </div>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                    @forelse ($nextPruef as $t)
                        <div class="rounded-xl border p-4">
                            <div class="text-sm text-gray-500">{{ \Illuminate\Support\Carbon::parse($t->datum)->translatedFormat('d.m.Y') }}</div>
                            <div class="mt-1 font-medium text-gray-900">{{ $t->bezeichnung ?? 'Prüfung' }}</div>
                            <div class="text-sm text-gray-600">{{ $t->institut ?? '' }}</div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-600">Keine anstehenden Termine.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
