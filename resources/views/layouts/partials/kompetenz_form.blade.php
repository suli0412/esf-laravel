<section id="kompetenzstand" class="bg-white rounded-xl shadow-sm p-6 mt-8">
  <h3 class="text-lg font-semibold mb-4">Kompetenzstand</h3>

  <form method="POST" action="{{ route('teilnehmer.kompetenzstand.save', $teilnehmer) }}" class="space-y-4">
    @csrf

    <div class="overflow-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-2 text-left">Kompetenz</th>
            <th class="px-3 py-2 text-left">Code</th>
            <th class="px-3 py-2 text-left">Niveau (Eintritt)</th>
            <th class="px-3 py-2 text-left">Niveau (Austritt)</th>
          </tr>
        </thead>
        <tbody>
          @forelse($kompetenzen as $k)
            @php
              $eintrittSel = ($eintrittMap[$k->kompetenz_id] ?? null) ?? old("eintritt.{$k->kompetenz_id}");
              $austrittSel = ($austrittMap[$k->kompetenz_id] ?? null) ?? old("austritt.{$k->kompetenz_id}");
            @endphp
            <tr class="border-t">
              <td class="px-3 py-2">{{ $k->bezeichnung }}</td>
              <td class="px-3 py-2 text-gray-500">{{ $k->code }}</td>
              <td class="px-3 py-2">
                <select name="eintritt[{{ $k->kompetenz_id }}]" class="border rounded px-2 py-1 w-full">
                  <option value="">—</option>
                  @foreach($niveaus as $n)
                    <option value="{{ $n->niveau_id }}" {{ (string)$eintrittSel === (string)$n->niveau_id ? 'selected' : '' }}>
                      {{ $n->label }}
                    </option>
                  @endforeach
                </select>
              </td>
              <td class="px-3 py-2">
                <select name="austritt[{{ $k->kompetenz_id }}]" class="border rounded px-2 py-1 w-full">
                  <option value="">—</option>
                  @foreach($niveaus as $n)
                    <option value="{{ $n->niveau_id }}" {{ (string)$austrittSel === (string)$n->niveau_id ? 'selected' : '' }}>
                      {{ $n->label }}
                    </option>
                  @endforeach
                </select>
              </td>
            </tr>
          @empty
            <tr><td colspan="4" class="px-3 py-4 text-gray-500">Noch keine Kompetenzen definiert.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="flex items-center gap-2">
      <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">
        Kompetenzstand speichern
      </button>
      <a href="{{ route('admin.kompetenzen.index') }}" class="px-4 py-2 border rounded">Kompetenzen verwalten</a>
      <a href="{{ route('admin.niveaus.index') }}" class="px-4 py-2 border rounded">Niveaus verwalten</a>
    </div>
  </form>
</section>
