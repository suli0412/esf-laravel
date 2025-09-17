{{-- Reuse: nur das Formular anzeigen, Liste verbergen --}}
@include('teilnehmer.partials.praktika_block', [
  'teilnehmer' => $teilnehmer,
  'showList'   => false,
])
