{{-- Erwartet: $teilnehmer->checkliste --}}
<dl class="grid grid-cols-4 gap-4 text-sm">
  <dt class="text-gray-500">AMS Bericht</dt><dd>{{ $teilnehmer->checkliste->AMS_Bericht }}</dd>
  <dt class="text-gray-500">AMS Lebenslauf</dt><dd>{{ $teilnehmer->checkliste->AMS_Lebenslauf }}</dd>
  <dt class="text-gray-500">Erwerbsstatus</dt><dd>{{ $teilnehmer->checkliste->Erwerbsstatus }}</dd>
  <dt class="text-gray-500">Vorzeitiger Austritt</dt><dd>{{ $teilnehmer->checkliste->VorzeitigerAustritt }}</dd>
  <dt class="text-gray-500">IDEA</dt><dd>{{ $teilnehmer->checkliste->IDEA }}</dd>
</dl>
