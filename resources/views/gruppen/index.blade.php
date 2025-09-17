@extends('layouts.app')
@section('title','Gruppen')

@section('content')
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-2xl font-bold">Gruppen</h2>
    <a href="{{ route('gruppen.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">+ Neue Gruppe</a>
  </div>

  @if(session('success'))
    <div class="mb-3 rounded border bg-green-50 text-green-800 px-3 py-2">{{ session('success') }}</div>
  @endif

  <div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-left">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-2">Name</th>
          <th class="px-3 py-2">Code</th>
          <th class="px-3 py-2">Projekt</th>
          <th class="px-3 py-2">Standard-MA</th>
          <th class="px-3 py-2">Aktiv</th>
          <th class="px-3 py-2 w-56">Aktionen</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $g)
          <tr class="border-t">
            <td class="px-3 py-2">
              <a href="{{ route('gruppen.show', $g) }}" class="text-blue-600 hover:underline">
                {{ $g->name }}
              </a>
            </td>
            <td class="px-3 py-2">{{ $g->code }}</td>
            <td class="px-3 py-2">{{ $g->projekt?->bezeichnung }}</td>
            <td class="px-3 py-2">
              @if($g->standardMitarbeiter)
                {{ $g->standardMitarbeiter->Nachname }}, {{ $g->standardMitarbeiter->Vorname }}
              @endif
            </td>
            <td class="px-3 py-2">{{ $g->aktiv ? 'Ja' : 'Nein' }}</td>

            <td class="px-3 py-2">
              <div class="flex flex-wrap items-center gap-2">
                {{-- Mitglieder ansehen --}}
                <a href="{{ route('gruppen.show', $g) }}" class="px-2 py-1 border rounded hover:bg-gray-50">
                  Mitglieder
                </a>

                {{-- Bearbeiten --}}
                <a href="{{ route('gruppen.edit', $g) }}" class="px-2 py-1 border rounded hover:bg-gray-50">
                  Bearbeiten
                </a>

                {{-- Löschen --}}
                <form action="{{ route('gruppen.destroy', $g) }}" method="POST"
                      onsubmit="return confirm('Wirklich löschen?')">
                  @csrf
                  @method('DELETE')
                  <button class="px-2 py-1 border rounded text-red-600 hover:bg-red-50">
                    Löschen
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td class="px-3 py-6 text-gray-500" colspan="6">Keine Gruppen.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    {{ $rows->links() }}
  </div>
@endsection
