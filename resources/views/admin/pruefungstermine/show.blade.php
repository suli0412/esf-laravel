@extends('layouts.app')
@section('title','Prüfungstermin')
@section('content')
<div class="flex items-center justify-between mb-4">
  <h1 class="text-2xl font-bold">
    Prüfungstermin · {{ \Illuminate\Support\Carbon::parse($pruefungstermin->datum)->format('d.m.Y') }}
  </h1>
  <a href="{{ route('admin.pruefungstermine.index') }}" class="px-4 py-2 rounded border">Zurück</a>
</div>

@if(session('success'))
  <div class="mb-3 text-green-700">{{ session('success') }}</div>
@endif
@if($errors->any())
  <div class="mb-3 text-red-700">
    <ul class="ml-5 list-disc">
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
@endif

<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
  <dl class="grid grid-cols-4 gap-4 text-sm">
    <dt class="text-gray-500">Datum</dt>
    <dd>{{ \Illuminate\Support\Carbon::parse($pruefungstermin->datum)->format('d.m.Y') }}</dd>
    <dt class="text-gray-500">Niveau</dt>
    <dd>{{ $pruefungstermin->niveau?->code }} {{ $pruefungstermin->niveau?->label ? '– '.$pruefungstermin->niveau->label : '' }}</dd>
    <dt class="text-gray-500">Bezeichnung</dt>
    <dd>{{ $pruefungstermin->bezeichnung ?? '—' }}</dd>
    <dt class="text-gray-500">Institut</dt>
    <dd>{{ $pruefungstermin->institut ?? '—' }}</dd>
  </dl>
</div>

<div class="grid md:grid-cols-2 gap-6">
  {{-- Buchungen --}}
  <div class="bg-white rounded-xl shadow-sm p-6">
    <h2 class="text-lg font-semibold mb-3">Gebuchte Teilnehmer</h2>
    @if($buchungen->isEmpty())
      <p class="text-gray-500 text-sm">Keine Buchungen.</p>
    @else
      <table class="w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-2 text-left">Teilnehmer</th>
            <th class="px-3 py-2 text-left">Email</th>
            <th class="px-3 py-2 text-left">Selbstzahler</th>
            <th class="px-3 py-2 w-24">Aktion</th>
          </tr>
        </thead>
        <tbody>
        @foreach($buchungen as $tn)
          <tr class="border-t">
            <td class="px-3 py-2">{{ $tn->Nachname }}, {{ $tn->Vorname }}</td>
            <td class="px-3 py-2">{{ $tn->Email }}</td>
            <td class="px-3 py-2">{{ $tn->pivot->selbstzahler ? 'Ja' : 'Nein' }}</td>
            <td class="px-3 py-2">
              <form method="POST" action="{{ route('admin.pruefungstermine.detach', [$pruefungstermin, $tn]) }}"
                    onsubmit="return confirm('Buchung entfernen?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Entfernen</button>
              </form>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    @endif
  </div>

  {{-- Suche & Buchen --}}
  <div class="bg-white rounded-xl shadow-sm p-6">
    <h2 class="text-lg font-semibold mb-3">Teilnehmer buchen</h2>

    <form method="GET" class="mb-3">
      <input type="text" name="q" value="{{ $q }}" placeholder="Name/Email suchen" class="border rounded px-3 py-2 w-2/3">
      <button class="px-3 py-2 bg-gray-200 rounded">Suchen</button>
    </form>

    @if($q !== '' && $treffer->isEmpty())
      <p class="text-gray-500">Keine Treffer.</p>
    @endif

    @foreach($treffer as $t)
      <div class="flex items-center justify-between border-b py-2">
        <div>
          <div class="font-medium">{{ $t->Nachname }}, {{ $t->Vorname }}</div>
          <div class="text-sm text-gray-500">{{ $t->Email }}</div>
        </div>
        <form method="POST" action="{{ route('admin.pruefungstermine.attach',$pruefungstermin) }}">
          @csrf
          <input type="hidden" name="teilnehmer_id" value="{{ $t->Teilnehmer_id }}">
          <label class="text-sm mr-2">
            <input type="checkbox" name="selbstzahler" value="1"> Selbstzahler
          </label>
          <button class="px-3 py-1 bg-blue-600 text-white rounded">Buchen</button>
        </form>
      </div>
    @endforeach
  </div>
</div>
@endsection
