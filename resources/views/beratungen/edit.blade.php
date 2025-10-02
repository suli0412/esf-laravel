@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">
  <div class="bg-white rounded-2xl shadow border p-6">
    <h1 class="text-xl font-semibold mb-4">Beratung bearbeiten</h1>

    @php
      // ID robust ermitteln (funktioniert auch, wenn primaryKey nicht "id" heißt)
      $bid = $beratung->beratung_id
          ?? $beratung->getKey()
          ?? request()->route('beratung')
          ?? request()->route('beratungen'); // falls Param-Name noch "beratungen" ist

      // Update-URL mit BENANNTEM Parameter aufbauen
      $action = route('beratungen.update', ['beratung' => $bid]);
    @endphp

    @include('beratungen._form', [
      'mode'        => 'edit',
      'beratung'    => $beratung,
      'teilnehmer'  => $teilnehmer ?? null,
      'arten'       => $arten,
      'themen'      => $themen,
      'mitarbeiter' => $mitarbeiter,
      'action'      => $action,
    ])
  </div>
</div>
@endsection
