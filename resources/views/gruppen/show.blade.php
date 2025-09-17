


@extends('layouts.app')
@section('title', 'Gruppe: '.$gruppe->name)

@section('content')

<h2 class="text-2xl font-bold mb-6">Gruppe • {{ $gruppe->name }}</h2>

<div class="bg-white rounded-xl shadow-sm p-4 mb-6">
  <div><b>Code:</b> {{ $gruppe->code }}</div>
  <div><b>Projekt:</b> {{ $gruppe->projekt?->bezeichnung }}</div>
  <div><b>Standard-Mitarbeiter:</b> {{ $gruppe->standardMitarbeiter?->Nachname }}, {{ $gruppe->standardMitarbeiter?->Vorname }}</div>
  <div><b>Aktiv:</b> {{ $gruppe->aktiv ? 'Ja' : 'Nein' }}</div>
</div>

  <div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Gruppe • {{ $gruppe->name }}</h2>
    <a href="{{ route('gruppen.index') }}" class="px-3 py-2 border rounded">Zurück</a>
  </div>

  @if(session('success'))
    <div class="mb-4 rounded border bg-green-50 text-green-800 px-3 py-2">
      {{ session('success') }}
    </div>
  @endif

  <div class="bg-white rounded-xl shadow-sm overflow-hidden">






    {{-- Mitglied hinzufügen --}}
<x-section-card id="mitglied-hinzufuegen" title="Mitglied zur Gruppe hinzufügen">
    <form method="get" action="{{ route('gruppen.show', $gruppe) }}" class="mb-3 flex gap-2 items-end">
        <input type="hidden" name="datum" value="{{ $datum }}">
        <label class="block">
            <span class="text-sm font-medium">Suche (Name/Email)</span>
            <input type="text" name="add_q" value="{{ $add_q }}" class="form-input" placeholder="z.B. Müller">
        </label>
        <button class="btn btn-secondary">Suchen</button>
    </form>

    <form method="post" action="{{ route('gruppen.mitglieder.attach', $gruppe) }}" class="flex flex-wrap gap-3 items-end">
        @csrf
        <label class="block">
            <span class="text-sm font-medium">Teilnehmer:in</span>
            <select name="teilnehmer_id" class="form-select min-w-64" required>
                <option value="">— wählen —</option>
                @foreach($availableTeilnehmer as $opt)
                    <option value="{{ $opt->getKey() }}">
                        {{ $opt->Nachname }}, {{ $opt->Vorname }} @if(!empty($opt->Email)) ({{ $opt->Email }}) @endif
                    </option>
                @endforeach
            </select>
        </label>

        <label class="block">
            <span class="text-sm font-medium">Beitritt ab</span>
            <input type="date" name="beitritt_von" class="form-input" value="{{ now()->toDateString() }}">
        </label>

        <button class="btn btn-primary">Zur Gruppe hinzufügen</button>
    </form>

    @if($availableTeilnehmer->isEmpty())
        <p class="mt-3 text-sm text-gray-500">Keine passenden Personen gefunden oder alle sind bereits Mitglied.</p>
    @endif
</x-section-card>













    <table class="w-full text-sm">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-2 text-left">Teilnehmer</th>
          <th class="px-3 py-2 text-left">Kontakt</th>
          <th class="px-3 py-2 text-left">Anwesenheit (heute schnell erfassen)</th>
        </tr>
      </thead>
      <tbody>
        @forelse($gruppe->teilnehmer as $tn)
          <tr class="border-t">
            <td class="px-3 py-2">
              <a href="{{ route('teilnehmer.show', $tn) }}" class="text-blue-600 hover:underline">
                {{ $tn->Nachname }}, {{ $tn->Vorname }}
              </a>
            </td>
            <td class="px-3 py-2">
              @if($tn->Email) <div>{{ $tn->Email }}</div> @endif
              @if($tn->Telefonnummer) <div>{{ $tn->Telefonnummer }}</div> @endif
            </td>
            <td class="px-3 py-2">
              <form method="POST" action="{{ route('anwesenheit.store') }}" class="flex flex-wrap items-end gap-2">
                @csrf
                {{-- Pflicht: teilnehmer_id für deine bestehende Store-Logik --}}
                <input type="hidden" name="teilnehmer_id" value="{{ $tn->Teilnehmer_id }}">

                <div>
                  <label class="block text-xs mb-1">Datum</label>
                  <input type="date" name="datum" value="{{ now()->toDateString() }}" class="border rounded px-2 py-1">
                </div>

                <div>
                  <label class="block text-xs mb-1">Status</label>
                  <select name="status" class="border rounded px-2 py-1">
                    @foreach(\App\Models\TeilnehmerAnwesenheit::STATI as $s)
                      <option value="{{ $s }}">{{ $s }}</option>
                    @endforeach
                  </select>
                </div>

                <div>
                  <label class="block text-xs mb-1">Fehlminuten</label>
                  <input type="number" name="fehlminuten" min="0" step="1" value="0" class="border rounded px-2 py-1 w-24">
                </div>

                {{-- OPTIONAL: nach Speichern wieder zurück zur Gruppe --}}
                <input type="hidden" name="return_to" value="{{ url()->current() }}">

                <button class="px-3 py-1 bg-blue-600 text-white rounded">
                  Speichern
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="3" class="px-3 py-6 text-gray-500">Keine Teilnehmer in dieser Gruppe.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
