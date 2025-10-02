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

    {{-- =========================
         Niveaus – EINTRITT
       ========================= --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-base font-semibold mb-4">Niveau bei <span class="font-bold">Eintritt</span></h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Deutsch --}}
            <div>
                <label for="de_lesen_in" class="block text-sm font-medium mb-1">Deutsch: Leseverstehen</label>
                <select id="de_lesen_in" name="de_lesen_in" class="border rounded-lg w-full px-3 py-2">
                    <option value="">— bitte wählen —</option>
                    @foreach(($levelsDe ?? []) as $opt)
                        <option value="{{ $opt }}" @selected(old('de_lesen_in') === $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
                @error('de_lesen_in')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="de_hoeren_in" class="block text-sm font-medium mb-1">Deutsch: Hörverstehen</label>
                <select id="de_hoeren_in" name="de_hoeren_in" class="border rounded-lg w-full px-3 py-2">
                    <option value="">— bitte wählen —</option>
                    @foreach(($levelsDe ?? []) as $opt)
                        <option value="{{ $opt }}" @selected(old('de_hoeren_in') === $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
                @error('de_hoeren_in')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="de_schreiben_in" class="block text-sm font-medium mb-1">Deutsch: Schreiben</label>
                <select id="de_schreiben_in" name="de_schreiben_in" class="border rounded-lg w-full px-3 py-2">
                    <option value="">— bitte wählen —</option>
                    @foreach(($levelsDe ?? []) as $opt)
                        <option value="{{ $opt }}" @selected(old('de_schreiben_in') === $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
                @error('de_schreiben_in')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="de_sprechen_in" class="block text-sm font-medium mb-1">Deutsch: Sprechen</label>
                <select id="de_sprechen_in" name="de_sprechen_in" class="border rounded-lg w-full px-3 py-2">
                    <option value="">— bitte wählen —</option>
                    @foreach(($levelsDe ?? []) as $opt)
                        <option value="{{ $opt }}" @selected(old('de_sprechen_in') === $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
                @error('de_sprechen_in')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Englisch --}}
            <div>
                <label for="en_in" class="block text-sm font-medium mb-1">Englisch</label>
                <select id="en_in" name="en_in" class="border rounded-lg w-full px-3 py-2">
                    <option value="">— bitte wählen —</option>
                    @foreach(($levelsEn ?? []) as $opt)
                        <option value="{{ $opt }}" @selected(old('en_in') === $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
                @error('en_in')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Mathematik --}}
            <div>
                <label for="ma_in" class="block text-sm font-medium mb-1">Mathematik</label>
                <select id="ma_in" name="ma_in" class="border rounded-lg w-full px-3 py-2">
                    <option value="">— bitte wählen —</option>
                    @foreach(($levelsMa ?? []) as $opt)
                        <option value="{{ $opt }}" @selected(old('ma_in') === $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
                @error('ma_in')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- =========================
         Niveaus – AUSSTIEG (beim Anlegen leer & disabled)
       ========================= --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-semibold">Niveau bei <span class="font-bold">Ausstieg</span></h3>
            <span class="text-xs text-gray-500">Beim Neuanlegen nicht ausfüllen</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 opacity-60">
            {{-- Deutsch --}}
            <div>
                <label class="block text-sm font-medium mb-1">Deutsch: Leseverstehen</label>
                <select name="de_lesen_out" class="border rounded-lg w-full px-3 py-2 bg-gray-100 cursor-not-allowed" disabled>
                    <option value="">— leer —</option>
                    @foreach(($levelsDe ?? []) as $opt)
                        <option value="{{ $opt }}">{{ $opt }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Deutsch: Hörverstehen</label>
                <select name="de_hoeren_out" class="border rounded-lg w-full px-3 py-2 bg-gray-100 cursor-not-allowed" disabled>
                    <option value="">— leer —</option>
                    @foreach(($levelsDe ?? []) as $opt)
                        <option value="{{ $opt }}">{{ $opt }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Deutsch: Schreiben</label>
                <select name="de_schreiben_out" class="border rounded-lg w-full px-3 py-2 bg-gray-100 cursor-not-allowed" disabled>
                    <option value="">— leer —</option>
                    @foreach(($levelsDe ?? []) as $opt)
                        <option value="{{ $opt }}">{{ $opt }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Deutsch: Sprechen</label>
                <select name="de_sprechen_out" class="border rounded-lg w-full px-3 py-2 bg-gray-100 cursor-not-allowed" disabled>
                    <option value="">— leer —</option>
                    @foreach(($levelsDe ?? []) as $opt)
                        <option value="{{ $opt }}">{{ $opt }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Englisch --}}
            <div>
                <label class="block text-sm font-medium mb-1">Englisch</label>
                <select name="en_out" class="border rounded-lg w-full px-3 py-2 bg-gray-100 cursor-not-allowed" disabled>
                    <option value="">— leer —</option>
                    @foreach(($levelsEn ?? []) as $opt)
                        <option value="{{ $opt }}">{{ $opt }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Mathematik --}}
            <div>
                <label class="block text-sm font-medium mb-1">Mathematik</label>
                <select name="ma_out" class="border rounded-lg w-full px-3 py-2 bg-gray-100 cursor-not-allowed" disabled>
                    <option value="">— leer —</option>
                    @foreach(($levelsMa ?? []) as $opt)
                        <option value="{{ $opt }}">{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <p class="text-xs text-gray-500 mt-2">
            Die Ausstiegswerte werden erst im <strong>Editieren</strong> gesetzt.
        </p>
    </div>

    {{-- Kompetenz-Matrix --}}
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

@include('partials.audit', ['model' => $teilnehmer])
@endsection

