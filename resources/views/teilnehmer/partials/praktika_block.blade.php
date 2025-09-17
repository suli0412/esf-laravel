<section id="praktika" class="space-y-4">
  <h3 class="text-lg font-semibold">Praktikum</h3>

  {{-- Fehler aus dem praktikum-Errorbag --}}
  @if ($errors->praktikum->any())
    <div class="text-red-600 text-sm">
      <ul class="list-disc ml-5">
        @foreach ($errors->praktikum->all() as $msg)
          <li>{{ $msg }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  {{-- Liste --}}
  <div class="overflow-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50">
        <tr class="text-left">
          <th class="py-2 px-3">Bereich</th>
          <th class="py-2 px-3">Firma</th>
          <th class="py-2 px-3">Land</th>
          <th class="py-2 px-3">Zeitraum</th>
          <th class="py-2 px-3 text-right">Stunden</th>
          <th class="py-2 px-3">Anmerkung</th>
          <th class="py-2 px-3">Aktion</th>
        </tr>
      </thead>
      <tbody>
        @forelse($teilnehmer->praktika as $p)
          <tr class="border-t">
            <td class="py-2 px-3">{{ $p->bereich ?? '—' }}</td>
            <td class="py-2 px-3">{{ $p->firma ?? '—' }}</td>
            <td class="py-2 px-3">{{ $p->land ?? '—' }}</td>
            <td class="py-2 px-3">
              {{ optional($p->beginn)->format('d.m.Y') }} – {{ optional($p->ende)->format('d.m.Y') }}
            </td>
            <td class="py-2 px-3 text-right">
              {{ $p->stunden_ausmass !== null ? number_format((float)$p->stunden_ausmass, 2, ',', '.') : '—' }}
            </td>
            <td class="py-2 px-3">{{ $p->anmerkung ?? '—' }}</td>
            <td class="py-2 px-3">
              <form method="POST" action="{{ route('praktika.destroy', [$teilnehmer, $p]) }}"
                    onsubmit="return confirm('Dieses Praktikum löschen?')">
                @csrf @method('DELETE')
                <button class="text-red-600 hover:underline">Löschen</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="7" class="py-4 text-gray-500 px-3">Noch keine Einträge.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Formular "Praktikum hinzufügen" --}}
  <form id="praktikumCreateForm" method="POST" action="{{ route('praktika.store', $teilnehmer) }}"
        class="grid grid-cols-12 gap-3">
    @csrf
    <div class="col-span-3">
      <label class="block text-sm mb-1">Bereich</label>
      <input name="bereich" value="{{ old('bereich') }}" class="border rounded w-full px-3 py-2">
    </div>
    <div class="col-span-3">
      <label class="block text-sm mb-1">Firma</label>
      <input name="firma" value="{{ old('firma') }}" class="border rounded w-full px-3 py-2">
    </div>
    <div class="col-span-2">
      <label class="block text-sm mb-1">Land</label>
      <input name="land" value="{{ old('land') }}" class="border rounded w-full px-3 py-2">
    </div>
    <div class="col-span-2">
      <label class="block text-sm mb-1">Von</label>
      <input type="date" name="von" value="{{ old('von') }}" class="border rounded w-full px-3 py-2">
    </div>
    <div class="col-span-2">
      <label class="block text-sm mb-1">Bis</label>
      <input type="date" name="bis" value="{{ old('bis') }}" class="border rounded w-full px-3 py-2">
    </div>
    <div class="col-span-2">
      <label class="block text-sm mb-1">Stunden</label>
      <input type="number" step="0.01" min="0" name="stunden" value="{{ old('stunden') }}"
             class="border rounded w-full px-3 py-2">
    </div>
    <div class="col-span-10">
      <label class="block text-sm mb-1">Anmerkung</label>
      <input name="anmerkung" value="{{ old('anmerkung') }}" class="border rounded w-full px-3 py-2">
    </div>
    <div class="col-span-12">
      <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Praktikum hinzufügen</button>
    </div>
  </form>
</section>
