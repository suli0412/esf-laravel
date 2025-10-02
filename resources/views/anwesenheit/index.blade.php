@extends('layouts.app')
@section('title','Anwesenheit')

@section('content')
<div class="flex flex-col gap-4 mb-6">

  <div class="flex flex-wrap items-end gap-3">
    <h2 class="text-2xl font-bold mr-auto">Anwesenheit</h2>

    <a href="{{ route('anwesenheit.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">
      + Neuer Eintrag
    </a>
  </div>

  <form method="GET" action="{{ route('anwesenheit.index') }}"
        class="flex flex-wrap items-end gap-3 bg-white p-3 rounded-xl shadow-sm">

    <div>
      <label class="text-sm text-gray-600">Suche (TN)</label>
      <input name="q" value="{{ $q }}" class="border rounded px-3 py-2" placeholder="Name/Email">
    </div>

    <div>
      <label class="text-sm text-gray-600">Gruppe</label>
      <select name="gruppe_id" class="border rounded px-3 py-2">
        <option value="">— alle —</option>
        @foreach($gruppen as $g)
          <option value="{{ $g->gruppe_id }}" @selected((string)$gruppeId===(string)$g->gruppe_id)>
            {{ $g->name }}
          </option>
        @endforeach
      </select>
    </div>

    <div>
      <label class="text-sm text-gray-600">Von</label>
      <input type="date" name="von" value="{{ $von }}" class="border rounded px-3 py-2">
    </div>
    <div>
      <label class="text-sm text-gray-600">Bis</label>
      <input type="date" name="bis" value="{{ $bis }}" class="border rounded px-3 py-2">
    </div>

    <button class="px-4 py-2 bg-gray-800 text-white rounded">Filtern</button>
    @if($q || $von || $bis || $gruppeId)
      <a href="{{ route('anwesenheit.index') }}" class="px-3 py-2 border rounded">Reset</a>
    @endif
  </form>

  {{-- Kontextzeile --}}
  <div class="bg-white rounded-xl shadow-sm px-3 py-2 text-sm text-gray-700 flex flex-wrap gap-4">
    <div><span class="text-gray-500">Zeitraum:</span> {{ \Illuminate\Support\Carbon::parse($von)->format('d.m.Y') }} – {{ \Illuminate\Support\Carbon::parse($bis)->format('d.m.Y') }}</div>
    <div><span class="text-gray-500">Gruppe:</span> {{ $gruppe?->name ?? 'alle' }}</div>
    <div><span class="text-gray-500">Treffer:</span> {{ $rows->total() }}</div>
  </div>

  {{-- Summenpanel: Verspätungsminuten je TN (für Filter) --}}
  <div class="bg-white rounded-xl shadow-sm p-3">
    <h3 class="font-semibold mb-2">Verspätungsminuten ({{ $von }} – {{ $bis }})</h3>

    @if($sumVerspaetet->isEmpty())
      <p class="text-gray-500">Keine Verspätungen im gewählten Zeitraum.</p>
    @else
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-3 py-2">Teilnehmer</th>
              <th class="px-3 py-2 text-right">Summe (Min.)</th>
            </tr>
          </thead>
          <tbody>
            @foreach($sumVerspaetet as $row)
              <tr class="border-t">
                <td class="px-3 py-2">
                  @if($row->teilnehmer)
                    <a class="text-blue-600 hover:underline" href="{{ route('teilnehmer.show', $row->teilnehmer) }}">
                      {{ $row->teilnehmer->Nachname }}, {{ $row->teilnehmer->Vorname }}
                    </a>
                  @else
                    —
                  @endif
                </td>
                <td class="px-3 py-2 text-right font-semibold">
                  {{ (int) $row->sum_min }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>

  @if(session('success'))
    <div class="rounded border bg-green-50 text-green-800 px-3 py-2">{{ session('success') }}</div>
  @endif

  {{-- Einzelliste --}}
  <div class="bg-white rounded-xl shadow-sm overflow-hidden">
    @php
      $badge = function ($status) {
        $map = [
          'anwesend'               => 'bg-green-100 text-green-800',
          'anwesend_verspaetet'    => 'bg-yellow-100 text-yellow-800',
          'abwesend'               => 'bg-red-100 text-red-800',
          'entschuldigt'           => 'bg-blue-100 text-blue-800',
          'religiöser_feiertag'    => 'bg-purple-100 text-purple-800',
        ];
        $cls = $map[$status] ?? 'bg-gray-100 text-gray-800';
        $label = str_replace('_', ' ', $status);
        return "<span class=\"px-2 py-1 rounded text-xs {$cls}\">{$label}</span>";
      };
    @endphp

    <table class="w-full text-left">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-2">Datum</th>
          <th class="px-3 py-2">Teilnehmer</th>
          <th class="px-3 py-2">Status</th>
          <th class="px-3 py-2 text-right">Fehlmin.</th>
          <th class="px-3 py-2">Aktionen</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $r)
          <tr class="border-t">
            <td class="px-3 py-2 whitespace-nowrap">{{ optional($r->datum)->format('d.m.Y') ?? \Illuminate\Support\Carbon::parse($r->datum)->format('d.m.Y') }}</td>
            <td class="px-3 py-2">
              @if($r->teilnehmer)
                <a href="{{ route('teilnehmer.show', $r->teilnehmer) }}" class="text-blue-600 hover:underline">
                  {{ $r->teilnehmer->Nachname }}, {{ $r->teilnehmer->Vorname }}
                </a>
              @else — @endif
            </td>
            <td class="px-3 py-2">{!! $badge($r->status) !!}</td>
            <td class="px-3 py-2 text-right">
              @if($r->status === 'anwesend_verspaetet')
                {{ (int) $r->fehlminuten }}
              @else
                —
              @endif
            </td>
            <td class="px-3 py-2">
              <a href="{{ route('anwesenheit.edit', $r) }}" class="text-yellow-700 hover:underline">Bearbeiten</a>
              <span class="mx-1 text-gray-300">|</span>
              <form action="{{ route('anwesenheit.destroy', $r) }}" method="POST" class="inline" onsubmit="return confirm('Löschen?')">
                @csrf @method('DELETE')
                <button class="text-red-700 hover:underline">Löschen</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td class="px-3 py-6 text-gray-500" colspan="5">Keine Einträge.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div>{{ $rows->links() }}</div>
</div>
<div>

</div>

@endsection




