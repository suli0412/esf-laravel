@extends('layouts.app')
@section('title','Gruppen')

@section('content')
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-2xl font-bold">Gruppen</h2>
    <a href="{{ route('gruppen.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">+ Neue Gruppe</a>
  </div>

  {{-- Status-/Fehlermeldungen --}}
  @if(session('success'))
    <div class="mb-3 rounded border bg-green-50 text-green-800 px-3 py-2">{{ session('success') }}</div>
  @endif
  @if(session('error'))
    <div class="mb-3 rounded border bg-red-50 text-red-800 px-3 py-2">{{ session('error') }}</div>
  @endif

  {{-- Filter: Aktiv / Inaktiv / Alle (spiegelt Controller-Logik ?show=...) --}}
  @php $show = request('show'); @endphp
  <div class="flex items-center gap-2 mb-3 text-sm">
    <a href="{{ route('gruppen.index') }}"
       class="px-3 py-1 rounded border {{ $show===null ? 'bg-gray-900 text-white' : 'bg-white' }}">
      Aktiv
    </a>
    <a href="{{ route('gruppen.index', ['show' => 'inactive']) }}"
       class="px-3 py-1 rounded border {{ $show==='inactive' ? 'bg-gray-900 text-white' : 'bg-white' }}">
      Inaktiv
    </a>
    <a href="{{ route('gruppen.index', ['show' => 'all']) }}"
       class="px-3 py-1 rounded border {{ $show==='all' ? 'bg-gray-900 text-white' : 'bg-white' }}">
      Alle
    </a>
  </div>

  @php
    // Spalte "Aktiv" nur zeigen, wenn es das Feld gibt
    $first = $rows->first();
    $hasAktiv = $first && isset($first->aktiv);
  @endphp

  <div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-left">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-2">Name</th>
          <th class="px-3 py-2">Code</th>
          <th class="px-3 py-2">Projekt</th>
          <th class="px-3 py-2">Standard-MA</th>
          @if($hasAktiv)
            <th class="px-3 py-2">Aktiv</th>
          @endif
          <th class="px-3 py-2 w-72">Aktionen</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $g)
          <tr class="border-t">
            <td class="px-3 py-2">
              <a href="{{ route('gruppen.show', $g) }}" class="text-blue-600 hover:underline">
                {{ $g->name }}
              </a>
              @if($hasAktiv && !$g->aktiv)
                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-200 text-gray-700">inaktiv</span>
              @endif
            </td>
            <td class="px-3 py-2">{{ $g->code }}</td>
            <td class="px-3 py-2">{{ $g->projekt?->bezeichnung }}</td>
            <td class="px-3 py-2">
              @if($g->standardMitarbeiter)
                {{ $g->standardMitarbeiter->Nachname }}, {{ $g->standardMitarbeiter->Vorname }}
              @endif
            </td>
            @if($hasAktiv)
              <td class="px-3 py-2">{{ $g->aktiv ? 'Ja' : 'Nein' }}</td>
            @endif

            <td class="px-3 py-2">
              <div class="flex flex-wrap items-center gap-2">
                {{-- Mitglieder / Wochenansicht --}}
                <a href="{{ route('gruppen.show', $g) }}" class="px-2 py-1 border rounded hover:bg-gray-50">
                  Mitglieder
                </a>

                {{-- Bearbeiten --}}
                <a href="{{ route('gruppen.edit', $g) }}" class="px-2 py-1 border rounded hover:bg-gray-50">
                  Bearbeiten
                </a>

                @if($hasAktiv && $g->aktiv)
                  {{-- Deaktivieren (nutzt destroy, löscht aber nicht – Controller setzt aktiv=0) --}}
                  <form action="{{ route('gruppen.destroy', $g) }}" method="POST"
                        onsubmit="return confirm('Gruppe wirklich deaktivieren?')">
                    @csrf
                    @method('DELETE')
                    <button class="px-2 py-1 border rounded text-red-600 hover:bg-red-50">
                      Deaktivieren
                    </button>
                  </form>
                @elseif($hasAktiv)
                  {{-- Aktivieren (nur anzeigen, wenn Route existiert) --}}
                  @if(\Illuminate\Support\Facades\Route::has('gruppen.activate'))
                    <form action="{{ route('gruppen.activate', $g) }}" method="POST">
                      @csrf
                      <button class="px-2 py-1 border rounded text-green-700 hover:bg-green-50">
                        Aktivieren
                      </button>
                    </form>
                  @endif
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td class="px-3 py-6 text-gray-500" colspan="{{ 5 + ($hasAktiv ? 1 : 0) }}">Keine Gruppen.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    {{ $rows->appends(['show' => request('show')])->links() }}
  </div>
@endsection
