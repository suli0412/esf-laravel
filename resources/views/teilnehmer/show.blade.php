@extends('layouts.app')

@section('title', $teilnehmer->Vorname.' '.$teilnehmer->Nachname)

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Teilnehmer • {{ $teilnehmer->Vorname }} {{ $teilnehmer->Nachname }}</h2>
    <div class="flex gap-2">
        <a href="{{ route('teilnehmer.edit',$teilnehmer) }}" class="px-4 py-2 rounded-lg bg-yellow-500 text-white hover:bg-yellow-600">Bearbeiten</a>
        <a href="{{ route('teilnehmer.index') }}" class="px-4 py-2 rounded-lg border">Zurück</a>
        <button onclick="window.print()" class="px-4 py-2 rounded-lg border">Drucken</button>
    </div>
</div>

{{-- Stammdaten --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h3 class="text-lg font-semibold mb-4">Stammdaten</h3>
    <dl class="grid grid-cols-4 gap-4 text-sm">
        <dt class="text-gray-500">Nachname</dt><dd>{{ $teilnehmer->Nachname }}</dd>
        <dt class="text-gray-500">Vorname</dt><dd>{{ $teilnehmer->Vorname }}</dd>
        <dt class="text-gray-500">Geschlecht</dt><dd>{{ $teilnehmer->Geschlecht }}</dd>
        <dt class="text-gray-500">SVN</dt><dd>{{ $teilnehmer->SVN }}</dd>

        <dt class="text-gray-500">Straße</dt><dd>{{ $teilnehmer->Strasse }}</dd>
        <dt class="text-gray-500">Hausnummer</dt><dd>{{ $teilnehmer->Hausnummer }}</dd>
        <dt class="text-gray-500">PLZ</dt><dd>{{ $teilnehmer->PLZ }}</dd>
        <dt class="text-gray-500">Wohnort</dt><dd>{{ $teilnehmer->Wohnort }}</dd>

        <dt class="text-gray-500">Land</dt><dd>{{ $teilnehmer->Land }}</dd>
        <dt class="text-gray-500">Email</dt><dd>{{ $teilnehmer->Email }}</dd>
        <dt class="text-gray-500">Telefon</dt><dd>{{ $teilnehmer->Telefonnummer }}</dd>
        <dt class="text-gray-500">Geburtsdatum</dt>
        <dd>
            {{ $teilnehmer->Geburtsdatum ? \Illuminate\Support\Carbon::parse($teilnehmer->Geburtsdatum)->format('d.m.Y') : '' }}
        </dd>

        <dt class="text-gray-500">Geburtsland</dt><dd>{{ $teilnehmer->Geburtsland }}</dd>
        <dt class="text-gray-500">Staatszugehörigkeit</dt><dd>{{ $teilnehmer->getAttribute('Staatszugehörigkeit') }}</dd>
        <dt class="text-gray-500">Staatszugehörigkeit Kategorie</dt><dd>{{ $teilnehmer->getAttribute('Staatszugehörigkeit_Kategorie') }}</dd>
        <dt class="text-gray-500">Aufenthaltsstatus</dt><dd>{{ $teilnehmer->Aufenthaltsstatus }}</dd>
    </dl>
</div>

{{-- Soziale Merkmale --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h3 class="text-lg font-semibold mb-4">Soziale Merkmale</h3>
    <dl class="grid grid-cols-4 gap-4 text-sm">
        <dt class="text-gray-500">Minderheit</dt><dd>{{ $teilnehmer->Minderheit }}</dd>
        <dt class="text-gray-500">Behinderung</dt><dd>{{ $teilnehmer->Behinderung }}</dd>
        <dt class="text-gray-500">Obdachlos</dt><dd>{{ $teilnehmer->Obdachlos }}</dd>
        <dt class="text-gray-500">Ländliche Gebiete</dt><dd>{{ $teilnehmer->getAttribute('LändlicheGebiete') }}</dd>

        <dt class="text-gray-500">Eltern im Ausland geboren</dt><dd>{{ $teilnehmer->getAttribute('ElternImAuslandGeboren') }}</dd>
        <dt class="text-gray-500">Armutsbetroffen</dt><dd>{{ $teilnehmer->Armutsbetroffen }}</dd>
        <dt class="text-gray-500">Armutsgefährdet</dt><dd>{{ $teilnehmer->Armutsgefährdet }}</dd>
        <dt class="text-gray-500">Bildungshintergrund</dt><dd>{{ $teilnehmer->Bildungshintergrund }}</dd>
    </dl>
</div>

{{-- Unterlagen & Ziele --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h3 class="text-lg font-semibold mb-4">Unterlagen & Ziele</h3>
    <dl class="grid grid-cols-4 gap-4 text-sm">
        <dt class="text-gray-500">IDEA Stammdatenblatt</dt><dd>{{ $teilnehmer->IDEA_Stammdatenblatt ? 'Ja' : 'Nein' }}</dd>
        <dt class="text-gray-500">IDEA Dokumente</dt><dd>{{ $teilnehmer->IDEA_Dokumente ? 'Ja' : 'Nein' }}</dd>
        <dt class="text-gray-500">PAZ</dt><dd>{{ $teilnehmer->PAZ }}</dd>
    </dl>
</div>

{{-- Beruf --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <h3 class="text-lg font-semibold mb-4">Beruf</h3>
    <dl class="grid grid-cols-4 gap-4 text-sm">
        <dt class="text-gray-500">Berufserfahrung als</dt><dd>{{ $teilnehmer->Berufserfahrung_als }}</dd>
        <dt class="text-gray-500">Bereich Berufserfahrung</dt><dd>{{ $teilnehmer->Bereich_berufserfahrung }}</dd>
        <dt class="text-gray-500">Land Berufserfahrung</dt><dd>{{ $teilnehmer->Land_berufserfahrung }}</dd>
        <dt class="text-gray-500">Firma Berufserfahrung</dt><dd>{{ $teilnehmer->Firma_berufserfahrung }}</dd>

        <dt class="text-gray-500">Zeit Berufserfahrung</dt><dd>{{ $teilnehmer->Zeit_berufserfahrung }}</dd>
        <dt class="text-gray-500">Stundenumfang</dt><dd>{{ $teilnehmer->Stundenumfang_berufserfahrung }}</dd>
        <dt class="text-gray-500">Zertifikate</dt><dd>{{ $teilnehmer->Zertifikate }}</dd>
        <dt class="text-gray-500">Clearing Gruppe</dt><dd>{{ $teilnehmer->Clearing_gruppe ? 'Ja' : 'Nein' }}</dd>

        <dt class="text-gray-500">Berufswunsch</dt><dd>{{ $teilnehmer->Berufswunsch }}</dd>
        <dt class="text-gray-500">Branche</dt><dd>{{ $teilnehmer->Berufswunsch_branche }}</dd>
        <dt class="text-gray-500">Branche 2</dt><dd>{{ $teilnehmer->Berufswunsch_branche2 }}</dd>
        <dt class="text-gray-500">Unterrichtseinheiten</dt><dd>{{ $teilnehmer->Unterrichtseinheiten }}</dd>
    </dl>
</div>

{{-- Anmerkung --}}
<div class="bg-white rounded-xl shadow-sm p-6">
    <h3 class="text-lg font-semibold mb-4">Anmerkung</h3>
    <p class="whitespace-pre-line">{{ $teilnehmer->Anmerkung }}</p>
</div>
@endsection
