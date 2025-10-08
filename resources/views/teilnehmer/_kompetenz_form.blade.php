{{-- resources/views/teilnehmer/_kompetenz_form.blade.php --}}
@php
  // erwartete Daten vom Controller:
  // $kompetenzen (Liste), $niveaus (Liste), $eintrittMap (id=>niveau_id), $austrittMap (id=>niveau_id)
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
  {{-- EINTRITT --}}
  <div>
    <h3 class="font-semibold mb-2">Kompetenzstand – Eintritt</h3>
    <table class="min-w-full border">
      <thead>
        <tr>
          <th class="p-2 border">Kompetenz</th>
          <th class="p-2 border">Niveau</th>
        </tr>
      </thead>
      <tbody>
      @foreach($kompetenzen as $k)
        @php $sel = old("kompetenz.Eintritt.{$k->kompetenz_id}", $eintrittMap[$k->kompetenz_id] ?? ''); @endphp
        <tr>
          <td class="p-2 border">{{ $k->code }} — {{ $k->bezeichnung }}</td>
          <td class="p-2 border">
            <select name="kompetenz[Eintritt][{{ $k->kompetenz_id }}]" class="form-select w-full">
              <option value="">— bitte wählen —</option>
              @foreach($niveaus as $n)
                <option value="{{ $n->niveau_id }}" @selected((string)$sel === (string)$n->niveau_id)>
                  {{ $n->code }} — {{ $n->label }}
                </option>
              @endforeach
            </select>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>

  {{-- AUSTRITT --}}
  <div>
    <h3 class="font-semibold mb-2">Kompetenzstand – Austritt</h3>
    <table class="min-w-full border">
      <thead>
        <tr>
          <th class="p-2 border">Kompetenz</th>
          <th class="p-2 border">Niveau</th>
        </tr>
      </thead>
      <tbody>
      @foreach($kompetenzen as $k)
        @php $sel = old("kompetenz.Austritt.{$k->kompetenz_id}", $austrittMap[$k->kompetenz_id] ?? ''); @endphp
        <tr>
          <td class="p-2 border">{{ $k->code }} — {{ $k->bezeichnung }}</td>
          <td class="p-2 border">
            <select name="kompetenz[Austritt][{{ $k->kompetenz_id }}]" class="form-select w-full">
              <option value="">— bitte wählen —</option>
              @foreach($niveaus as $n)
                <option value="{{ $n->niveau_id }}" @selected((string)$sel === (string)$n->niveau_id)>
                  {{ $n->code }} — {{ $n->label }}
                </option>
              @endforeach
            </select>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
</div>
