@props([
  'teilnehmer' => null,           // App\Models\Teilnehmer или null
  'mitarbeiter' => null,          // App\Models\Mitarbeiter или null
  'arten' => collect([]),
  'themen' => collect([]),
  'teilnehmerList' => collect([]) // ← НОВОЕ: список участников для выбора
])
<form action="{{ route('beratungen.store') }}" method="POST" class="bg-white rounded-xl shadow-sm p-4 space-y-3">
  @csrf

  @if($teilnehmer)
    <input type="hidden" name="teilnehmer_id" value="{{ $teilnehmer->Teilnehmer_id }}">
  @else
    <div>
      <label class="block text-sm mb-1">Teilnehmer *</label>
      <select name="teilnehmer_id" class="border rounded w-full px-2 py-2" required>
        <option value=""></option>
        @foreach($teilnehmerList as $tn)
          <option value="{{ $tn->Teilnehmer_id }}">
            {{ $tn->Nachname }}, {{ $tn->Vorname }}
          </option>
        @endforeach
      </select>
    </div>
  @endif

  @if($mitarbeiter)
    <input type="hidden" name="mitarbeiter_id" value="{{ $mitarbeiter->Mitarbeiter_id }}">
  @endif

  <div class="grid grid-cols-12 gap-3">
    <div class="col-span-3">
      <label class="block text-sm mb-1">Art *</label>
      <select name="art_id" class="border rounded w-full px-2 py-2" required>
        <option value=""></option>
        @foreach($arten as $a)
          <option value="{{ $a->Art_id }}">{{ $a->Code }} — {{ $a->Bezeichnung }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-span-5">
      <label class="block text-sm mb-1">Thema *</label>
      <select name="thema_id" class="border rounded w-full px-2 py-2" required>
        <option value=""></option>
        @foreach($themen as $t)
          <option value="{{ $t->Thema_id }}">{{ $t->Bezeichnung }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-span-2">
      <label class="block text-sm mb-1">Datum *</label>
      <input type="date" name="datum" value="{{ now()->toDateString() }}" class="border rounded w-full px-2 py-2" required>
    </div>
    <div class="col-span-2">
      <label class="block text-sm mb-1">Dauer (h)</label>
      <input type="number" step="0.25" min="0" max="24" name="dauer_h" class="border rounded w-full px-2 py-2">
    </div>
    <div class="col-span-12">
      <label class="block text-sm mb-1">Notizen</label>
      <textarea name="notizen" rows="2" class="border rounded w-full px-2 py-2"></textarea>
    </div>
  </div>

  <div class="text-right">
    <button class="px-4 py-2 bg-green-600 text-white rounded">Speichern</button>
  </div>
</form>
