{{-- resources/views/teilnehmer/partials/kompetenz_form.blade.php --}}
@php
  $eintrittMap = $eintrittMap ?? [];
  $austrittMap = $austrittMap ?? [];
@endphp
@php
  // bereits gesetzte Werte mappen: [zeitpunkt][kompetenz_id] => niveau_id
  $set = ['Eintritt'=>[], 'Austritt'=>[]];
  if(isset($teilnehmer)){
    foreach ($teilnehmer->kompetenzstaende as $ks) {
      $set[$ks->zeitpunkt][$ks->kompetenz_id] = $ks->niveau_id;
    }
  }
@endphp

<div class="bg-white rounded-xl shadow-sm p-6">
  <h3 class="text-lg font-semibold mb-4">Kompetenzstand</h3>

  <div class="grid grid-cols-3 gap-3 items-end mb-3">
    <div></div>
    <div>
      <label class="block text-sm text-gray-600">Datum (Eintritt)</label>
      <input type="date" name="kompetenz[datum][Eintritt]" class="border rounded w-full px-3 py-2"
             value="{{ old('kompetenz.datum.Eintritt') }}">
    </div>
    <div>
      <label class="block text-sm text-gray-600">Datum (Austritt)</label>
      <input type="date" name="kompetenz[datum][Austritt]" class="border rounded w-full px-3 py-2"
             value="{{ old('kompetenz.datum.Austritt') }}">
    </div>
  </div>

  <table class="w-full text-sm">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-3 py-2 text-left">Kompetenz</th>
        <th class="px-3 py-2 text-left">Eintritt</th>
        <th class="px-3 py-2 text-left">Austritt</th>
      </tr>
    </thead>
    <tbody>
      @foreach($kompetenzen as $k)
        <tr class="border-t">
          <td class="px-3 py-2">{{ $k->code }} — {{ $k->bezeichnung }}</td>
          <td class="px-3 py-2">
            <select name="kompetenz[Eintritt][{{ $k->kompetenz_id }}]" class="border rounded px-2 py-1 w-full">
              <option value="">—</option>
              @foreach($niveaus as $n)
                <option value="{{ $n->niveau_id }}"
                  @selected( (old('kompetenz.Eintritt.'.$k->kompetenz_id) ?? ($set['Eintritt'][$k->kompetenz_id] ?? null)) == $n->niveau_id )>
                  {{ $n->code }} {{ $n->label ? '– '.$n->label : '' }}
                </option>
              @endforeach
            </select>
          </td>
          <td class="px-3 py-2">
            <select name="kompetenz[Austritt][{{ $k->kompetenz_id }}]" class="border rounded px-2 py-1 w-full">
              <option value="">—</option>
              @foreach($niveaus as $n)
                <option value="{{ $n->niveau_id }}"
                  @selected( (old('kompetenz.Austritt.'.$k->kompetenz_id) ?? ($set['Austritt'][$k->kompetenz_id] ?? null)) == $n->niveau_id )>
                  {{ $n->code }} {{ $n->label ? '– '.$n->label : '' }}
                </option>
              @endforeach
            </select>
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div class="grid grid-cols-2 gap-3 mt-3">
    <div>
      <label class="block text-sm text-gray-600">Bemerkung (Eintritt)</label>
      <input type="text" name="kompetenz[bemerkung][Eintritt]" class="border rounded w-full px-3 py-2"
             value="{{ old('kompetenz.bemerkung.Eintritt') }}">
    </div>
    <div>
      <label class="block text-sm text-gray-600">Bemerkung (Austritt)</label>
      <input type="text" name="kompetenz[bemerkung][Austritt]" class="border rounded w-full px-3 py-2"
             value="{{ old('kompetenz.bemerkung.Austritt') }}">
    </div>
  </div>
</div>
