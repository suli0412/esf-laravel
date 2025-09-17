{{-- resources/views/teilnehmer/_checkliste_block.blade.php --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-6">
  <h3 class="font-semibold mb-3">Checkliste Nermin</h3>

  <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm">
    <div>AMS Bericht</div><div class="font-medium">{{ $teilnehmer->checkliste->AMS_Bericht ?? '—' }}</div>
    <div>AMS Lebenslauf</div><div class="font-medium">{{ $teilnehmer->checkliste->AMS_Lebenslauf ?? '—' }}</div>
    <div>Erwerbsstatus</div><div class="font-medium">{{ $teilnehmer->checkliste->Erwerbsstatus ?? '—' }}</div>
    <div>Vorzeitiger Austritt</div><div class="font-medium">{{ $teilnehmer->checkliste->VorzeitigerAustritt ?? '—' }}</div>
    <div>IDEA</div><div class="font-medium">{{ $teilnehmer->checkliste->IDEA ?? '—' }}</div>
  </div>

  <div class="mt-4">
    <a href="{{ route('checkliste.edit', $teilnehmer) }}" class="text-blue-600 hover:underline">Vollständig bearbeiten</a>
  </div>
</div>

<div class="bg-white rounded-xl shadow-sm p-4">
  <h3 class="font-semibold mb-3">Neue Beratung (für diesen Teilnehmer)</h3>

  @include('beratungen._quick_form_individual', [
    'teilnehmer'  => $teilnehmer,
    'mitarbeiter' => null,
    'arten'       => $arten,
    'themen'      => $themen,
  ])
</div>
