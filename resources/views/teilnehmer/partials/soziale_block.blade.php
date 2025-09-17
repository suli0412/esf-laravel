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
