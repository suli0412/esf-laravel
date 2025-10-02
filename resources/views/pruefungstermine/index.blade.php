@extends('layouts.app')
@section('title', 'Prüfungstermine')

@section('content')
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-2xl font-bold">Prüfungstermine</h2>
    <a href="{{ route('pruefungstermine.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700">
      + Neuer Termin
    </a>
  </div>

  @if(session('success'))
    <div class="mb-3 rounded border bg-green-50 text-green-800 px-3 py-2">
      {{ session('success') }}
    </div>
  @endif

  {{-- Filterleiste --}}
  @php
    // Kompatibilität: Controller liefert 'rows' (neu) und evtl. 'termine' (alt)
    $list = $rows ?? $termine ?? collect();
    $scope = $scope ?? request('ab','upcoming');
    $q     = $q ?? request('q','');
    $niv   = $niv ?? request('niveau_id');
  @endphp

  <form method="GET" action="{{ route('pruefungstermine.index') }}" class="bg-white rounded-xl shadow-sm p-3 mb-4">
    <div class="flex flex-wrap items-center gap-3">
      {{-- Scope-Tabs --}}
      <div class="inline-flex rounded-lg border overflow-hidden">
        @php
          $scopes = [
            'upcoming' => 'Bevorstehend',
            'past'     => 'Vergangen',
            'all'      => 'Alle',
          ];
        @endphp
        @foreach($scopes as $key => $label)
          @php $active = $scope === $key; @endphp
          <a href="{{ route('pruefungstermine.index', array_filter(['ab' => $key, 'q' => $q, 'niveau_id' => $niv])) }}"
             class="px-3 py-1 text-sm {{ $active ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-50' }}">
            {{ $label }}
          </a>
        @endforeach
      </div>

      {{-- Suche --}}
      <div class="flex items-center gap-2">
        <input type="text" name="q" value="{{ $q }}" placeholder="Suche (Institut, Bezeichnung, Titel)"
               class="w-64 border rounded px-3 py-2" />
      </div>

      {{-- Niveau-Dropdown --}}
      <div>
        <select name="niveau_id" class="border rounded px-3 py-2">
          <option value="">Alle Niveaus</option>
          @foreach(($niveaus ?? []) as $n)
            @php
              $code = $n->code ?? $n->label ?? ('ID '.$n->niveau_id);
            @endphp
            <option value="{{ $n->niveau_id }}" @selected((string)$niv === (string)$n->niveau_id)>
              {{ $code }}
            </option>
          @endforeach
        </select>
      </div>

      <button class="px-3 py-2 border rounded hover:bg-gray-50">Filtern</button>

      @if($q || $niv || ($scope && $scope !== 'upcoming'))
        <a href="{{ route('pruefungstermine.index') }}" class="text-sm text-gray-600 hover:underline">Filter zurücksetzen</a>
      @endif
    </div>
  </form>

  <div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-left">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-2">Datum</th>
          <th class="px-3 py-2">Niveau</th>
          <th class="px-3 py-2">Titel / Bezeichnung</th>
          <th class="px-3 py-2">Institut</th>
          <th class="px-3 py-2">Buchungen</th>
          <th class="px-3 py-2 w-56">Aktionen</th>
        </tr>
      </thead>
      <tbody>
        @forelse($list as $t)
        @php
        $s = $t->start_at instanceof \Illuminate\Support\Carbon ? $t->start_at : (filled($t->start_at) ? \Illuminate\Support\Carbon::parse($t->start_at) : null);
        $e = $t->end_at   instanceof \Illuminate\Support\Carbon ? $t->end_at   : (filled($t->end_at)   ? \Illuminate\Support\Carbon::parse($t->end_at)   : null);
        $past = $e ? $e->isPast() : ($s ? $s->isPast() : false);
        @endphp
        <td class="px-3 py-2 whitespace-nowrap">
        @if($s)
            <div class="flex flex-col">
            <span class="inline-block px-2 py-0.5 text-xs rounded {{ $past ? 'bg-gray-100 text-gray-700' : 'bg-green-50 text-green-700' }}">
                {{ $s->format('d.m.Y') }}
            </span>
            <span class="text-sm text-gray-700">
                {{ $s->format('H:i') }}{{ $e ? '–'.$e->format('H:i') : '' }} Uhr
            </span>
            </div>
        @else
            —
        @endif
        </td>

            <td class="px-3 py-2">
              {{ $t->niveau->code ?? $t->niveau->label ?? '—' }}
            </td>
            <td class="px-3 py-2">
              <div class="font-medium">{{ $t->titel ?? '—' }}</div>
              <div class="text-sm text-gray-500">{{ $t->bezeichnung ?? '' }}</div>
            </td>
            <td class="px-3 py-2">{{ $t->institut ?? '—' }}</td>
                {{-- Zeige Count --}}
                <td class="px-3 py-2">
                    {{ isset($t->teilnehmer_count) ? $t->teilnehmer_count : '—' }}
                </td>
            <td class="px-3 py-2">
              <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('pruefungstermine.show', $t) }}" class="px-2 py-1 border rounded hover:bg-gray-50">
                  Anzeigen
                </a>
                <a href="{{ route('pruefungstermine.edit', $t) }}" class="px-2 py-1 border rounded hover:bg-gray-50">
                  Bearbeiten
                </a>
                <form action="{{ route('pruefungstermine.destroy', $t) }}" method="POST"
                      onsubmit="return confirm('Termin wirklich löschen?')">
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
            <td class="px-3 py-6 text-gray-500" colspan="6">Keine Termine gefunden.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    {{ $list->links() }}
  </div>
@endsection
