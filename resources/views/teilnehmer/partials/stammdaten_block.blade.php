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
  <dd>{{ $teilnehmer->Geburtsdatum ? \Illuminate\Support\Carbon::parse($teilnehmer->Geburtsdatum)->format('d.m.Y') : '' }}</dd>

  <dt class="text-gray-500">Geburtsland</dt><dd>{{ $teilnehmer->Geburtsland }}</dd>
  <dt class="text-gray-500">Staatszugehörigkeit</dt><dd>{{ $teilnehmer->getAttribute('Staatszugehörigkeit') }}</dd>
  <dt class="text-gray-500">Staatszugehörigkeit Kategorie</dt><dd>{{ $teilnehmer->getAttribute('Staatszugehörigkeit_Kategorie') }}</dd>
  <dt class="text-gray-500">Aufenthaltsstatus</dt><dd>{{ $teilnehmer->Aufenthaltsstatus }}</dd>
</dl>
