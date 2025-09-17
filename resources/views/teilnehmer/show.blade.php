{{-- resources/views/teilnehmer/show.blade.php --}}
@extends('layouts.app')

@section('title', $teilnehmer->Vorname.' '.$teilnehmer->Nachname)

@section('content')
@php
  $request = request();
  $arten  = $arten  ?? \App\Models\Beratungsart::orderBy('Code')->get();
  $themen = $themen ?? \App\Models\Beratungsthema::orderBy('Bezeichnung')->get();
@endphp

<div class="flex items-center justify-between mb-6">
  <h2 class="text-2xl font-bold">
    Teilnehmer • {{ $teilnehmer->Vorname }} {{ $teilnehmer->Nachname }}
  </h2>
  <div class="flex gap-2">
    <a href="{{ route('teilnehmer.edit',$teilnehmer) }}" class="px-4 py-2 rounded-lg bg-yellow-500 text-white hover:bg-yellow-600">Bearbeiten</a>
    <a href="{{ route('checkliste.edit',$teilnehmer) }}" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Checkliste bearbeiten</a>
    <a href="{{ route('teilnehmer.index') }}" class="px-4 py-2 rounded-lg border">Zurück</a>
    <button onclick="window.print()" class="px-4 py-2 rounded-lg border">Drucken</button>
  </div>
</div>

{{-- BLOCK: Stammdaten (mit allen Untersektionen) --}}
<x-section-card id="stammdaten" title="Stammdaten">
  @include('teilnehmer.partials.stammdaten_group', [
    'teilnehmer' => $teilnehmer
  ])
</x-section-card>

{{-- BLOCK: Beratung --}}
<x-section-card id="beratung" title="Beratung">
  @include('teilnehmer.partials.beratung_block', [
    'teilnehmer' => $teilnehmer,
    'arten'      => $arten,
    'themen'     => $themen
  ])
</x-section-card>

{{-- BLOCK: Anwesenheit --}}
<x-section-card id="anwesenheit" title="Anwesenheit">
  @include('teilnehmer.partials.anwesenheit_block', [
    'teilnehmer'        => $teilnehmer,
    'anwesenheiten'     => $anwesenheiten,
    'monat'             => $monat,
    'fehlminutenSumme'  => $fehlminutenSumme,
  ])
</x-section-card>

{{-- BLOCK: Praktikum (Liste + Formular – einziges Vorkommen) --}}
<x-section-card id="praktikum" title="Praktikum">
  @include('teilnehmer.partials.praktika_block', ['teilnehmer' => $teilnehmer])
</x-section-card>

{{-- BLOCK: Dokumente (Liste + Upload) --}}
<x-section-card id="dokumente" title="Dokumente">
  @include('teilnehmer._dokumente_block', [
    'teilnehmer' => $teilnehmer,
    'request'    => $request,
  ])
</x-section-card>

{{-- BLOCK: Kompetenzen (Stammdaten-Set anzeigen) --}}
<x-section-card id="kompetenzen" title="Kompetenzen">
  @include('teilnehmer.partials.kompetenzen_block')
</x-section-card>

{{-- BLOCK: Prüfungsteilnahme (alle Prüfungen + Ergebnisse dieses Teilnehmers) --}}
<x-section-card id="pruefungsteilnahme" title="Prüfungsteilnahme">
  @include('teilnehmer.partials.pruefungsteilnahme_block', [
    'teilnehmer' => $teilnehmer
  ])
</x-section-card>

@endsection
