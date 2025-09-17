@extends('layouts.app')
@section('title','Dokumente')

@section('content')
<div class="flex items-center justify-between mb-6">
  <h2 class="text-2xl font-bold">Dokumente</h2>

  <div class="flex items-center gap-2">
    <a href="{{ route('dokumente.create') }}" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">
      Neue Vorlage
    </a>
    <a href="{{ route('dokumente.index') }}" class="px-4 py-2 rounded border">Aktualisieren</a>
  </div>
</div>

{{-- Flash / Errors --}}
@if(session('success'))
  <div class="mb-4 rounded border bg-green-50 text-green-800 px-3 py-2">
    {{ session('success') }}
  </div>
@endif
@if ($errors->any())
  <div class="mb-4 rounded border bg-red-50 text-red-800 px-3 py-2">
    <ul class="list-disc ml-5">
      @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
    </ul>
  </div>
@endif

{{-- Генератор документа --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-8">
  <div class="flex items-center justify-between">
    <h3 class="font-semibold mb-3">Dokument generieren</h3>

  </div>

  <form action="{{ route('dokumente.go') }}" method="POST" target="_blank" class="grid grid-cols-12 gap-3">
    @csrf


{{-- Teilnehmer --}}
    <div class="col-span-4">
      <label class="block text-sm mb-1">Teilnehmer *</label>
      <input type="hidden" name="teilnehmer_id" id="teilnehmer_id" value="{{ $teilnehmerSelected?->Teilnehmer_id }}">
      <div class="flex items-center gap-2">
        <input type="text" readonly
                value="{{ $teilnehmerSelected ? ($teilnehmerSelected->Nachname.', '.$teilnehmerSelected->Vorname) : '' }}"
                placeholder="Wählen Sie unten aus der Liste"
                class="border rounded w-full px-3 py-2 bg-gray-50">
        @if($teilnehmerSelected)
          <a href="{{ route('dokumente.index') }}" class="px-3 py-2 border rounded">Zurücksetzen</a>
        @endif
      </div>
      <p class="text-xs text-gray-500 mt-1">Wählen Sie unten aus der Liste</p>
    </div>



    {{-- Mitarbeiter --}}
    <div class="col-span-2">
      <label class="block text-sm mb-1">Mitarbeiter (optional)</label>
      <select name="mitarbeiter_id" class="border rounded w-full px-3 py-2">
        <option value=""></option>
        @if($mitarbeiterSelected)
          <option value="{{ $mitarbeiterSelected->Mitarbeiter_id }}" selected>
            {{ $mitarbeiterSelected->Nachname }}, {{ $mitarbeiterSelected->Vorname }}
          </option>
        @endif
      </select>
      <p class="text-xs text-gray-500 mt-1">Unten wählen (optional)</p>
    </div>

    {{-- Projekt --}}
    <div class="col-span-3">
      <label class="block text-sm mb-1">Projekt (optional)</label>
      <select name="projekt_id" class="border rounded w-full px-3 py-2">
        <option value=""></option>
        @foreach($projekte as $p)
          <option value="{{ $p->projekt_id }}">{{ $p->bezeichnung }}</option>
        @endforeach
      </select>
      <p class="text-xs text-gray-500 mt-1">Beginn/Ende werden aus dem Projekt übernommen</p>
    </div>


    {{-- Vorlage --}}
    <div class="col-span-3">
      <label class="block text-sm mb-1">Vorlage *</label>
      <select name="dokument_slug" class="border rounded w-full px-3 py-2" required>
        <option value=""></option>
        @foreach($docs as $d)
          <option value="{{ $d->slug }}">{{ $d->name }}</option>
        @endforeach
      </select>
    </div>

















    <div class="col-span-12">
      <button class="px-4 py-2 bg-blue-600 text-white rounded">Öffnen (Vorschau/PDF)</button>
    </div>
  </form>
</div>

<div class="grid grid-cols-2 gap-6">
  {{-- Фильтр + список Teilnehmer --}}
  <div class="bg-white rounded-xl shadow-sm p-4">
    <div class="flex items-center justify-between mb-3">
      <h3 class="font-semibold">Teilnehmer wählen</h3>
      <form method="GET" action="{{ route('dokumente.index') }}" class="flex items-center gap-2">
        <input type="text" name="q_tn" value="{{ $q_tn }}" placeholder="Suche Nachname/Vorname/Email" class="border rounded px-3 py-2">
        @if(request()->has('mitarbeiter_id'))
          <input type="hidden" name="mitarbeiter_id" value="{{ request('mitarbeiter_id') }}">
        @endif
        <button class="px-3 py-2 bg-gray-800 text-white rounded">Suchen</button>
      </form>
    </div>

    <table class="w-full text-left">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-2">Name</th>
          <th class="px-3 py-2 w-28">Aktion</th>
        </tr>
      </thead>
      <tbody>
        @foreach($tnRows as $t)
          <tr class="border-t">
            <td class="px-3 py-2">{{ $t->Nachname }}, {{ $t->Vorname }}</td>
            <td class="px-3 py-2">
              <a class="text-blue-600 hover:underline"
                 href="{{ route('dokumente.index', array_merge(request()->query(), ['teilnehmer_id'=>$t->Teilnehmer_id])) }}">
                Wählen
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="mt-2">{{ $tnRows->links() }}</div>
  </div>

  {{-- Фильтр + список Mitarbeiter --}}
  <div class="bg-white rounded-xl shadow-sm p-4">
    <div class="flex items-center justify-between mb-3">
      <h3 class="font-semibold">Mitarbeiter wählen (optional)</h3>
      <form method="GET" action="{{ route('dokumente.index') }}" class="flex items-center gap-2">
        <input type="text" name="q_ma" value="{{ $q_ma }}" placeholder="Suche Nachname/Vorname/Email" class="border rounded px-3 py-2">
        @if(request()->has('teilnehmer_id'))
          <input type="hidden" name="teilnehmer_id" value="{{ request('teilnehmer_id') }}">
        @endif
        <button class="px-3 py-2 bg-gray-800 text-white rounded">Suchen</button>
      </form>
    </div>

    <table class="w-full text-left">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-3 py-2">Name</th>
          <th class="px-3 py-2 w-28">Aktion</th>
        </tr>
      </thead>
      <tbody>
        @foreach($maRows as $m)
          <tr class="border-t">
            <td class="px-3 py-2">{{ $m->Nachname }}, {{ $m->Vorname }} ({{ $m->Taetigkeit }})</td>
            <td class="px-3 py-2">
              <a class="text-blue-600 hover:underline"
                 href="{{ route('dokumente.index', array_merge(request()->query(), ['mitarbeiter_id'=>$m->Mitarbeiter_id])) }}">
                Wählen
              </a>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
    <div class="mt-2">{{ $maRows->links() }}</div>
  </div>
</div>

{{-- Управление шаблонами (кратко) --}}
<div class="bg-white rounded-xl shadow-sm p-4 mt-8">
  <div class="flex items-center justify-between mb-3">
    <h3 class="font-semibold">Vorlagen (Verwaltung)</h3>
    <a href="{{ route('dokumente.create') }}" class="px-3 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Neue Vorlage</a>
  </div>

  <table class="w-full text-left">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-3 py-2">Name</th>
        <th class="px-3 py-2">Slug</th>
        <th class="px-3 py-2">Aktiv</th>
        <th class="px-3 py-2">Aktionen</th>
      </tr>
    </thead>
    <tbody>
      @forelse($docs as $d)
        <tr class="border-t">
          <td class="px-3 py-2">{{ $d->name }}</td>
          <td class="px-3 py-2">{{ $d->slug }}</td>
          <td class="px-3 py-2">{{ $d->is_active ? 'Ja' : 'Nein' }}</td>
          <td class="px-3 py-2">
            <a href="{{ route('dokumente.edit', $d) }}" class="text-blue-600 hover:underline">Bearbeiten</a>
            <span class="mx-1 text-gray-300">|</span>
            <a href="{{ route('dokumente.show', $d) }}" class="text-gray-700 hover:underline" target="_blank">Vorschau</a>
          </td>
        </tr>
      @empty
        <tr><td class="px-3 py-6 text-gray-500" colspan="4">Keine Vorlagen vorhanden.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
