@extends('layouts.app')
@section('title', 'Dokument erzeugen: '.$dokument->name)

@section('content')
<h2 class="text-2xl font-bold mb-4">
  Dokument erzeugen – {{ $dokument->name }}<br>
  <span class="text-base font-normal text-gray-600">Teilnehmer: {{ $teilnehmer->Vorname }} {{ $teilnehmer->Nachname }}</span>
</h2>

<form action="{{ route('dokumente.generate', [$teilnehmer, $dokument]) }}" method="POST" class="bg-white rounded-xl shadow-sm p-4">
  @csrf
  <div class="grid grid-cols-2 gap-4">
    <div>
      <label class="block text-sm mb-1">Projekt (Mitgliedschaften)</label>
      <select name="projekt_id" class="border rounded w-full px-3 py-2" required>
        @forelse($tp as $row)
          <option value="{{ $row->projekt_id }}" {{ (int)$vorauswahlProjektId === (int)$row->projekt_id ? 'selected' : '' }}>
            {{ $row->projekt?->bezeichnung ?? 'Projekt #'.$row->projekt_id }}
            @if($row->beginn || $row->ende)
              — {{ $row->beginn ? \Carbon\Carbon::parse($row->beginn)->format('d.m.Y') : '—' }}
              bis {{ $row->ende ? \Carbon\Carbon::parse($row->ende)->format('d.m.Y') : '—' }}
            @endif
          </option>
        @empty
          {{-- Fallback: alle Projekte, falls TN noch keiner zugeordnet ist --}}
          @foreach(\App\Models\Projekt::orderBy('bezeichnung')->get() as $p)
            <option value="{{ $p->projekt_id }}">{{ $p->bezeichnung }}</option>
          @endforeach
        @endforelse
      </select>
      <p class="text-xs text-gray-500 mt-1">Diese Auswahl bestimmt Kursbeginn/Ende und Kursdauer.</p>
    </div>

    <div>
      <label class="block text-sm mb-1">Mitarbeiter (zuständig)</label>
      <select name="mitarbeiter_id" class="border rounded w-full px-3 py-2">
        <option value="">— keiner —</option>
        @foreach($mitarbeiter as $m)
          <option value="{{ $m->Mitarbeiter_id }}" {{ (int)$vorauswahlMitarbeiterId === (int)$m->Mitarbeiter_id ? 'selected' : '' }}>
            {{ $m->Nachname }}, {{ $m->Vorname }} — {{ $m->Taetigkeit }}
          </option>
        @endforeach
      </select>
      <p class="text-xs text-gray-500 mt-1">Vorbelegung kommt aus dem Projekt (Standard-Mitarbeiter), kann aber überschrieben werden.</p>
    </div>
  </div>

  <div class="mt-4">
    <button class="px-4 py-2 bg-blue-600 text-white rounded">PDF erzeugen</button>
    <a href="{{ route('teilnehmer.show', $teilnehmer) }}" class="px-4 py-2 border rounded ml-2">Abbrechen</a>
  </div>
</form>

@if($dokument->body)
  <div class="mt-6 bg-white rounded-xl shadow-sm p-4">
    <h3 class="font-semibold mb-2">Vorlagen-Inhalt (Vorschau mit Platzhaltern)</h3>
    <pre class="text-sm whitespace-pre-wrap">{{ $dokument->body }}</pre>
    <p class="text-xs text-gray-500 mt-2">Verfügbare Platzhalter: {Anrede}, {Vorname}, {Nachname}, {Geburtsdatum}, {Heute}, {Ort}, {Projekt}, {ProjektCode}, {ProjektBeginn}, {ProjektEnde}, {KursdauerTage}, {KursdauerWochen}, {KursdauerMonate}, {MitarbeiterVorname}, {MitarbeiterNachname}, {MitarbeiterTaetigkeit}, {MitarbeiterEmail}, {MitarbeiterTelefon}</p>
  </div>
@endif
@endsection
