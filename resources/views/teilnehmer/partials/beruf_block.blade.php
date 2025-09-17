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
