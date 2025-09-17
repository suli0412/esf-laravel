@extends('layouts.app')
@section('title', 'Mitarbeiter · '.$mitarbeiter->Vorname.' '.$mitarbeiter->Nachname)

@section('content')
@if(session('success'))
  <div class="mb-4 rounded border bg-green-50 text-green-800 px-3 py-2">
    {{ session('success') }}
  </div>
@endif

{{-- Карточка сотрудника --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-6">
  <h2 class="text-xl font-semibold mb-2">Mitarbeiter</h2>
  <div class="grid grid-cols-2 gap-4 text-sm">
    <div><span class="text-gray-500">Name:</span> {{ $mitarbeiter->Vorname }} {{ $mitarbeiter->Nachname }}</div>
    <div><span class="text-gray-500">Tätigkeit:</span> {{ $mitarbeiter->Taetigkeit }}</div>
    <div><span class="text-gray-500">E-Mail:</span> {{ $mitarbeiter->Email }}</div>
    <div><span class="text-gray-500">Telefon:</span> {{ $mitarbeiter->Telefonnummer ?? '—' }}</div>
  </div>
  <div class="mt-4 flex gap-2">
    <a href="{{ route('mitarbeiter.index') }}" class="px-3 py-2 border rounded">Zurück</a>
    <a href="{{ route('mitarbeiter.edit', $mitarbeiter) }}" class="px-3 py-2 border rounded">Bearbeiten</a>
  </div>
</div>

{{-- Быстрые формы --}}
<div class="grid grid-cols-12 gap-6">
  <div class="col-span-12 lg:col-span-6">
    <div class="bg-white rounded-xl shadow-sm p-4">
      <h3 class="font-semibold mb-3">Schnell: Individuelle Beratung</h3>
      @include('beratungen._quick_form_individual', [
        'teilnehmer' => null,
        'mitarbeiter' => $mitarbeiter,
        'arten' => $arten,
        'themen' => $themen
      ])
    </div>
  </div>

<div class="col-span-12 lg:col-span-6">
    <div class="bg-white rounded-xl shadow-sm p-4">
      <h3 class="font-semibold mb-3">Schnell: Gruppenberatung</h3>
      @include('beratungen._quick_form_group', [
        'mitarbeiter' => $mitarbeiter,
        'arten' => $arten,
        'themen' => $themen,
        'teilnehmerList' => $teilnehmerList
      ])
    </div>
  </div>
</div>


@include('beratungen._quick_form_individual', [
  'teilnehmer' => null,
  'mitarbeiter' => $mitarbeiter,
  'arten' => $arten,
  'themen' => $themen,
  'teilnehmerList' => $teilnehmerList  {{-- ← добавили --}}
])

    {{-- Schnell: Individuelle Beratung (vom Mitarbeiter aus) --}}
<div class="bg-white rounded-xl shadow-sm p-4 mt-6">
    <h3 class="font-semibold mb-3">Schnell: Individuelle Beratung</h3>
@include('beratungen._quick_form_individual', [
        'teilnehmer'  => null,           // участника выберем из селекта формы
        'mitarbeiter' => $mitarbeiter,   // фиксируем сотрудника
        'arten'       => $arten,
        'themen'      => $themen,
    ])
    </div>



@endsection
