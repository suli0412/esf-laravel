@php
    $geschlecht = ['Mann','Frau','Nicht binär'];
    $jaNeinKA = ['Ja','Nein','Keine Angabe'];
    $bildung = ['ISCED0','ISCED1','ISCED2','ISCED3','ISCED4','ISCED5-8'];
    $paz = [
        'Arbeitsaufnahme','Lehrstelle','ePSA','Sprachprüfung A2/B1',
        'weitere Deutschkurse','Basisbildung','Sonstige berufsspezifische Weiterbildung','Sonstiges'
    ];
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
        <div class="font-semibold mb-1">Bitte Eingaben prüfen:</div>
        <ul class="list-disc list-inside text-sm">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-12 gap-6">
    {{-- Stammdaten --}}
    <section class="col-span-12 bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4">Stammdaten</h3>
        <div class="grid grid-cols-12 gap-4">
            <x-field class="col-span-4" label="Nachname">
                <input name="Nachname" value="{{ old('Nachname', $teilnehmer->Nachname ?? '') }}" class="w-full border rounded-lg px-3 py-2" required>
            </x-field>
            <x-field class="col-span-4" label="Vorname">
                <input name="Vorname" value="{{ old('Vorname', $teilnehmer->Vorname ?? '') }}" class="w-full border rounded-lg px-3 py-2" required>
            </x-field>
            <x-field class="col-span-4" label="Geschlecht">
                <select name="Geschlecht" class="w-full border rounded-lg px-3 py-2">
                    <option value=""></option>
                    @foreach($geschlecht as $g)
                        <option value="{{ $g }}" @selected(old('Geschlecht', $teilnehmer->Geschlecht ?? '')===$g)>{{ $g }}</option>
                    @endforeach
                </select>
            </x-field>

            <x-field class="col-span-3" label="SVN">
                <input name="SVN" maxlength="12" value="{{ old('SVN', $teilnehmer->SVN ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>
            <x-field class="col-span-5" label="Straße">
                <input name="Strasse" value="{{ old('Strasse', $teilnehmer->Strasse ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>
            <x-field class="col-span-2" label="Hausnr.">
                <input name="Hausnummer" value="{{ old('Hausnummer', $teilnehmer->Hausnummer ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>
            <x-field class="col-span-2" label="PLZ">
                <input name="PLZ" value="{{ old('PLZ', $teilnehmer->PLZ ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>
            <x-field class="col-span-4" label="Wohnort">
                <input name="Wohnort" value="{{ old('Wohnort', $teilnehmer->Wohnort ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>
            <x-field class="col-span-3" label="Land">
                <input name="Land" value="{{ old('Land', $teilnehmer->Land ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>
            <x-field class="col-span-4" label="Email">
                <input type="email" name="Email" value="{{ old('Email', $teilnehmer->Email ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>
            <x-field class="col-span-3" label="Telefon">
                <input name="Telefonnummer" value="{{ old('Telefonnummer', $teilnehmer->Telefonnummer ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>
            <x-field class="col-span-3" label="Geburtsdatum">
                <input type="date" name="Geburtsdatum" value="{{ old('Geburtsdatum', $teilnehmer->Geburtsdatum ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>
            <x-field class="col-span-3" label="Geburtsland">
                <input name="Geburtsland" value="{{ old('Geburtsland', $teilnehmer->Geburtsland ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>
            <x-field class="col-span-3" label="Staatszugehörigkeit">
                <input name="Staatszugehörigkeit" value="{{ old('Staatszugehörigkeit', $teilnehmer->Staatszugehörigkeit ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>
            <x-field class="col-span-3" label="Staatszugehörigkeit Kategorie">
                <input name="Staatszugehörigkeit_Kategorie" value="{{ old('Staatszugehörigkeit_Kategorie', $teilnehmer->Staatszugehörigkeit_Kategorie ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>
            <x-field class="col-span-3" label="Aufenthaltsstatus">
                <input name="Aufenthaltsstatus" value="{{ old('Aufenthaltsstatus', $teilnehmer->Aufenthaltsstatus ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>
        </div>
    </section>

    {{-- Soziale Merkmale --}}
    <section class="col-span-12 bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4">Soziale Merkmale</h3>
        <div class="grid grid-cols-12 gap-4">
            @foreach ([
                'Minderheit' => 'Minderheit',
                'Behinderung' => 'Behinderung',
                'Obdachlos' => 'Obdachlos',
                'LändlicheGebiete' => 'Ländliche Gebiete',
                'ElternImAuslandGeboren' => 'Eltern im Ausland geboren',
                'Armutsbetroffen' => 'Armutsbetroffen',
                'Armutsgefährdet' => 'Armutsgefährdet',
            ] as $name => $label)
                <x-field class="col-span-3" :label="$label">
                    <select name="{{ $name }}" class="w-full border rounded-lg px-3 py-2">
                        <option value=""></option>
                        @foreach ($jaNeinKA as $opt)
                            <option value="{{ $opt }}" @selected(old($name, $teilnehmer->$name ?? '')===$opt)>{{ $opt }}</option>
                        @endforeach
                    </select>
                </x-field>
            @endforeach

            <x-field class="col-span-3" label="Bildungshintergrund">
                <select name="Bildungshintergrund" class="w-full border rounded-lg px-3 py-2">
                    <option value=""></option>
                    @foreach ($bildung as $b)
                        <option value="{{ $b }}" @selected(old('Bildungshintergrund', $teilnehmer->Bildungshintergrund ?? '')===$b)>{{ $b }}</option>
                    @endforeach
                </select>
            </x-field>
        </div>
    </section>

    {{-- IDEA / Dokumente / PAZ --}}
    <section class="col-span-12 bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4">Unterlagen & Ziele</h3>
        <div class="grid grid-cols-12 gap-4 items-center">
            <div class="col-span-3">
                <label class="inline-flex items-center space-x-2">
                    <input type="hidden" name="IDEA_Stammdatenblatt" value="0">
                    <input type="checkbox" name="IDEA_Stammdatenblatt" value="1"
                           @checked(old('IDEA_Stammdatenblatt', $teilnehmer->IDEA_Stammdatenblatt ?? false))>
                    <span>IDEA Stammdatenblatt</span>
                </label>
            </div>
            <div class="col-span-3">
                <label class="inline-flex items-center space-x-2">
                    <input type="hidden" name="IDEA_Dokumente" value="0">
                    <input type="checkbox" name="IDEA_Dokumente" value="1"
                           @checked(old('IDEA_Dokumente', $teilnehmer->IDEA_Dokumente ?? false))>
                    <span>IDEA Dokumente</span>
                </label>
            </div>
            <x-field class="col-span-6" label="PAZ">
                <select name="PAZ" class="w-full border rounded-lg px-3 py-2">
                    <option value=""></option>
                    @foreach ($paz as $p)
                        <option value="{{ $p }}" @selected(old('PAZ', $teilnehmer->PAZ ?? '')===$p)>{{ $p }}</option>
                    @endforeach
                </select>
            </x-field>
        </div>
    </section>

    {{-- Berufserfahrung & Wünsche --}}
    <section class="col-span-12 bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4">Beruf</h3>
        <div class="grid grid-cols-12 gap-4">
            <x-field class="col-span-3" label="Berufserfahrung als">
                <input name="Berufserfahrung_als" value="{{ old('Berufserfahrung_als', $teilnehmer->Berufserfahrung_als ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>
            <x-field class="col-span-3" label="Bereich Berufserfahrung">
                <input name="Bereich_berufserfahrung" value="{{ old('Bereich_berufserfahrung', $teilnehmer->Bereich_berufserfahrung ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>
            <x-field class="col-span-2" label="Land">
                <input name="Land_berufserfahrung" value="{{ old('Land_berufserfahrung', $teilnehmer->Land_berufserfahrung ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>
            <x-field class="col-span-4" label="Firma">
                <input name="Firma_berufserfahrung" value="{{ old('Firma_berufserfahrung', $teilnehmer->Firma_berufserfahrung ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>
            <x-field class="col-span-3" label="Zeit">
                <input name="Zeit_berufserfahrung" value="{{ old('Zeit_berufserfahrung', $teilnehmer->Zeit_berufserfahrung ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>
            <x-field class="col-span-3" label="Stundenumfang">
                <input type="number" step="0.01" name="Stundenumfang_berufserfahrung" value="{{ old('Stundenumfang_berufserfahrung', $teilnehmer->Stundenumfang_berufserfahrung ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>
            <x-field class="col-span-6" label="Zertifikate">
                <input name="Zertifikate" value="{{ old('Zertifikate', $teilnehmer->Zertifikate ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>

            <x-field class="col-span-4" label="Berufswunsch">
                <input name="Berufswunsch" value="{{ old('Berufswunsch', $teilnehmer->Berufswunsch ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>
            <x-field class="col-span-4" label="Berufswunsch Branche">
                <input name="Berufswunsch_branche" value="{{ old('Berufswunsch_branche', $teilnehmer->Berufswunsch_branche ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>
            <x-field class="col-span-4" label="Berufswunsch Branche 2">
                <input name="Berufswunsch_branche2" value="{{ old('Berufswunsch_branche2', $teilnehmer->Berufswunsch_branche2 ?? '') }}" class="w-full border rounded-lg px-3 py-2">
            </x-field>

            <div class="col-span-12 flex items-center gap-6">
                <label class="inline-flex items-center space-x-2">
                    <input type="hidden" name="Clearing_gruppe" value="0">
                    <input type="checkbox" name="Clearing_gruppe" value="1" @checked(old('Clearing_gruppe', $teilnehmer->Clearing_gruppe ?? false))>
                    <span>Clearing Gruppe</span>
                </label>
                <x-field label="Unterrichtseinheiten">
                    <input type="number" name="Unterrichtseinheiten" value="{{ old('Unterrichtseinheiten', $teilnehmer->Unterrichtseinheiten ?? '') }}" class="w-full border rounded-lg px-3 py-2">
                </x-field>
            </div>
        </div>
    </section>

    {{-- Anmerkung --}}
    <section class="col-span-12 bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold mb-4">Anmerkung</h3>
        <textarea name="Anmerkung" rows="4" class="w-full border rounded-lg px-3 py-2">{{ old('Anmerkung', $teilnehmer->Anmerkung ?? '') }}</textarea>
    </section>
</div>

{{-- Липкая панель действий для desktop --}}
<div class="mt-8 sticky bottom-4">
    <div class="bg-white border rounded-xl shadow-sm px-4 py-3 flex items-center justify-end gap-3">
        <a href="{{ route('teilnehmer.index') }}" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Abbrechen</a>
        <button class="px-5 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700">Speichern</button>
    </div>
</div>
