{{-- Anwesenheit hinzufügen + Monatsliste --}}

{{-- Formular hinzufügen --}}
<div class="mb-6">
  <h3 class="font-semibold mb-3">Anwesenheit hinzufügen</h3>
  <form action="{{ route('anwesenheit.store') }}" method="POST" class="grid grid-cols-4 gap-3">
    @csrf
    <input type="hidden" name="teilnehmer_id" value="{{ $teilnehmer->Teilnehmer_id }}">
    <div>
      <label class="block text-sm mb-1">Datum *</label>
      <input type="date" name="datum" value="{{ now()->toDateString() }}" class="border rounded w-full px-3 py-2" required>
    </div>
    <div>
      <label class="block text-sm mb-1">Status *</label>
      <select name="status" class="border rounded w-full px-3 py-2" required>
        @foreach(\App\Models\TeilnehmerAnwesenheit::STATI as $s)
          <option value="{{ $s }}">{{ $s }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="block text-sm mb-1">Fehlminuten</label>
      <input type="number" min="0" step="1" name="fehlminuten" value="0" class="border rounded w-full px-3 py-2">
    </div>
    <div class="flex items-end">
      <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Speichern</button>
    </div>
  </form>
</div>

{{-- Monatsliste --}}
<div>
  <h3 class="text-lg font-semibold mb-2">Anwesenheit im Monat {{ $monat }}</h3>

  <form method="GET" class="mb-4">
    <label for="monat">Monat wählen:</label>
    <input type="month" name="monat" id="monat" value="{{ $monat }}" class="border p-1">
    <button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded">Anzeigen</button>
  </form>

  <table class="w-full border text-sm">
    <thead class="bg-gray-100">
      <tr>
        <th class="border px-2 py-1">Datum</th>
        <th class="border px-2 py-1">Status</th>
        <th class="border px-2 py-1">Fehlminuten</th>
      </tr>
    </thead>
    <tbody>
      @forelse($anwesenheiten as $a)
        <tr>
          <td class="border px-2 py-1">{{ $a->datum->format('d.m.Y') }}</td>
          <td class="border px-2 py-1">{{ ucfirst($a->status) }}</td>
          <td class="border px-2 py-1 text-right">{{ $a->fehlminuten }}</td>
        </tr>
      @empty
        <tr>
          <td colspan="3" class="text-center text-gray-500 py-2">Keine Daten für diesen Monat.</td>
        </tr>
      @endforelse
    </tbody>
    <tfoot>
      <tr class="bg-gray-50 font-semibold">
        <td colspan="2" class="border px-2 py-1 text-right">Summe Fehlminuten:</td>
        <td class="border px-2 py-1 text-right">{{ $fehlminutenSumme }}</td>
      </tr>
    </tfoot>
  </table>
</div>
