@extends('layouts.app')
@section('title', 'Prüfungstermin')

@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-2xl font-bold">
    Prüfungstermin • {{ optional($termin->datum)->format('d.m.Y') }} • {{ $termin->niveau?->code }}
  </h1>
  <div class="space-x-2">
    <a href="{{ route('pruefungstermine.edit',$termin) }}" class="px-3 py-2 bg-yellow-500 text-white rounded">Bearbeiten</a>
    <form action="{{ route('pruefungstermine.destroy',$termin) }}" method="POST" class="inline" onsubmit="return confirm('Termin löschen?')">
      @csrf @method('DELETE')
      <button class="px-3 py-2 border rounded">Löschen</button>
    </form>
    <a href="{{ route('pruefungstermine.index') }}" class="px-3 py-2 border rounded">Zurück</a>
  </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
  <dl class="grid grid-cols-4 gap-4 text-sm">
    <dt class="text-gray-500">Niveau</dt><dd>{{ $termin->niveau?->code }} — {{ $termin->niveau?->label }}</dd>
    <dt class="text-gray-500">Bezeichnung</dt><dd>{{ $termin->bezeichnung }}</dd>
    <dt class="text-gray-500">Datum</dt><dd>{{ optional($termin->datum)->format('d.m.Y') }}</dd>
    <dt class="text-gray-500">Institut</dt><dd>{{ $termin->institut }}</dd>
  </dl>
</div>

<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
  <h2 class="text-lg font-semibold mb-3">Teilnehmer buchen</h2>
  <form method="POST" action="{{ route('pruefungstermine.buchen',$termin) }}" class="grid grid-cols-3 gap-3">
    @csrf
    <div>
      <label class="block text-sm mb-1">Teilnehmer</label>
      <select name="teilnehmer_id" class="border rounded w-full px-3 py-2" required>
        @foreach($alleTn as $tn)
          <option value="{{ $tn->Teilnehmer_id }}">{{ $tn->Nachname }}, {{ $tn->Vorname }}</option>
        @endforeach
      </select>
      @error('teilnehmer_id')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
    </div>
    <div class="flex items-center gap-2">
      <input type="checkbox" name="selbstzahler" id="sz" class="h-4 w-4">
      <label for="sz">Selbstzahler</label>
    </div>
    <div class="flex items-end">
      <button class="px-4 py-2 bg-blue-600 text-white rounded">Buchen</button>
    </div>
  </form>
</div>

<div class="bg-white rounded-xl shadow-sm p-6">
  <h2 class="text-lg font-semibold mb-3">Gebuchte Teilnehmer</h2>
  <table class="w-full text-sm">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-3 py-2 text-left">Name</th>
        <th class="px-3 py-2 text-left">Selbstzahler</th>
        <th class="px-3 py-2 text-left">Bestanden</th>
        <th class="px-3 py-2 w-40"></th>
      </tr>
    </thead>
    <tbody>
      @forelse($termin->teilnehmer as $t)
        <tr class="border-t">
          <td class="px-3 py-2">{{ $t->Nachname }}, {{ $t->Vorname }}</td>
          <td class="px-3 py-2">{{ $t->pivot->selbstzahler ? 'Ja' : 'Nein' }}</td>
          <td class="px-3 py-2">
            <form method="POST" action="{{ route('pruefungstermine.status', [$termin, $t]) }}">
              @csrf @method('PATCH')
              <select name="bestanden" class="border rounded px-2 py-1" onchange="this.form.submit()">
                <option value=""  @selected($t->pivot->bestanden===null)>(offen)</option>
                <option value="1" @selected($t->pivot->bestanden===1)>Ja</option>
                <option value="0" @selected($t->pivot->bestanden===0)>Nein</option>
              </select>
            </form>
          </td>
          <td class="px-3 py-2 text-right">
            <form method="POST" action="{{ route('pruefungstermine.storno', [$termin, $t]) }}" onsubmit="return confirm('Buchung stornieren?')">
              @csrf @method('DELETE')
              <button class="text-red-600 hover:underline">Stornieren</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td colspan="4" class="px-3 py-4 text-gray-500">Keine Buchungen.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
@endsection
