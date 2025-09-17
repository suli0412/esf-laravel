@extends('layouts.app')

@section('title', 'Checkliste • '.$teilnehmer->Vorname.' '.$teilnehmer->Nachname)

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold">Checkliste – {{ $teilnehmer->Vorname }} {{ $teilnehmer->Nachname }}</h2>
</div>

<form action="{{ route('checkliste.save', $teilnehmer) }}" method="POST" class="bg-white rounded-xl shadow-sm p-6 max-w-3xl">
    @csrf

    @if ($errors->any())
        <div class="mb-4 rounded border border-red-200 bg-red-50 text-red-800 px-3 py-2">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm text-gray-600 mb-1">AMS Bericht</label>
            <select name="AMS_Bericht" class="w-full border rounded px-3 py-2">
                <option value=""></option>
                @foreach (['Gesendet','Nicht gesendet'] as $v)
                    <option value="{{ $v }}" @selected(old('AMS_Bericht', $checkliste->AMS_Bericht)===$v)>{{ $v }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">AMS Lebenslauf</label>
            <select name="AMS_Lebenslauf" class="w-full border rounded px-3 py-2">
                <option value=""></option>
                @foreach (['Gesendet','Nicht gesendet'] as $v)
                    <option value="{{ $v }}" @selected(old('AMS_Lebenslauf', $checkliste->AMS_Lebenslauf)===$v)>{{ $v }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-span-2">
            <label class="block text-sm text-gray-600 mb-1">Erwerbsstatus</label>
            <input name="Erwerbsstatus" value="{{ old('Erwerbsstatus', $checkliste->Erwerbsstatus) }}" class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">Vorzeitiger Austritt</label>
            <select name="VorzeitigerAustritt" class="w-full border rounded px-3 py-2">
                <option value=""></option>
                @foreach (['Ja','Nein'] as $v)
                    <option value="{{ $v }}" @selected(old('VorzeitigerAustritt', $checkliste->VorzeitigerAustritt)===$v)>{{ $v }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">IDEA</label>
            <select name="IDEA" class="w-full border rounded px-3 py-2">
                <option value=""></option>
                @foreach (['Gesendet','Nicht gesendet','k. VD/g. AW','offen'] as $v)
                    <option value="{{ $v }}" @selected(old('IDEA', $checkliste->IDEA)===$v)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="mt-6 flex gap-3">
        <a href="{{ route('teilnehmer.show', $teilnehmer) }}" class="px-4 py-2 rounded border">Abbrechen</a>
        <button class="px-5 py-2 rounded bg-green-600 text-white">Speichern</button>
    </div>
</form>
@endsection
