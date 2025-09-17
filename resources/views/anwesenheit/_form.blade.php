@if($errors->any())
  <div class="rounded border border-red-200 bg-red-50 text-red-800 px-3 py-2 mb-2">
    <ul class="list-disc list-inside text-sm">
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
@endif

@php
  $stati = $stati ?? \App\Models\TeilnehmerAnwesenheit::STATI;
@endphp

<div class="grid grid-cols-2 gap-4">
  <div class="col-span-2">
    <label class="block text-sm mb-1">Teilnehmer *</label>
    <select name="teilnehmer_id" class="border rounded w-full px-3 py-2" required>
      <option value="">— wählen —</option>
      @foreach($teilnehmer as $t)
        <option value="{{ $t->Teilnehmer_id }}"
          @selected(old('teilnehmer_id', $anwesenheit->teilnehmer_id ?? request('teilnehmer_id')) == $t->Teilnehmer_id)>
          {{ $t->Nachname }}, {{ $t->Vorname }} ({{ $t->Email }})
        </option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm mb-1">Datum *</label>
    <input type="date" name="datum" value="{{ old('datum', optional($anwesenheit->datum ?? null)->toDateString()) }}" class="border rounded w-full px-3 py-2" required>
  </div>

  <div>
    <label class="block text-sm mb-1">Status *</label>
    <select name="status" class="border rounded w-full px-3 py-2" required>
      @foreach($stati as $s)
        <option value="{{ $s }}" @selected(old('status', $anwesenheit->status ?? '') === $s)>{{ $s }}</option>
      @endforeach
    </select>
  </div>

  <div>
    <label class="block text-sm mb-1">Fehlminuten</label>
    <input type="number" min="0" step="1" name="fehlminuten"
           value="{{ old('fehlminuten', $anwesenheit->fehlminuten ?? 0) }}"
           class="border rounded w-full px-3 py-2">
  </div>
</div>
