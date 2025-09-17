@extends('layouts.app')

@section('title','Neuer Teilnehmer')

@section('content')
<form action="{{ route('teilnehmer.store') }}" method="POST" class="space-y-6">
    @csrf

    {{-- Validierungsfehler --}}
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3">
            <div class="font-semibold mb-1">Bitte prüfen:</div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Gruppe (optional) – falls im _form bereits vorhanden, diesen Block hier entfernen --}}
    @isset($gruppen)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <label for="gruppe_id" class="block text-sm font-medium mb-1">Gruppe (optional)</label>
            <select id="gruppe_id" name="gruppe_id" class="border rounded-lg w-full px-3 py-2">
                <option value="">— keine —</option>
                @foreach($gruppen as $g)
                    <option value="{{ $g->gruppe_id }}" {{ old('gruppe_id') == $g->gruppe_id ? 'selected' : '' }}>
                        {{ $g->code ? $g->code.' — ' : '' }}{{ $g->name }}
                    </option>
                @endforeach
            </select>
            @error('gruppe_id')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    @endisset

    {{-- Teilnehmer-Felder --}}
    @include('teilnehmer._form')

    @include('teilnehmer.partials.kompetenz_form', ['kompetenzen'=>$kompetenzen, 'niveaus'=>$niveaus, 'teilnehmer'=>$teilnehmer ?? null])

    <div class="flex items-center gap-2">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Speichern
        </button>
        <a href="{{ route('teilnehmer.index') }}" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
            Abbrechen
        </a>
    </div>
</form>
@endsection
