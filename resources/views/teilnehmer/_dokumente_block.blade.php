<div id="docs" class="bg-white rounded-xl shadow-sm p-6 mb-6">
  <h3 class="text-lg font-semibold mb-4">Dokumente</h3>

  {{-- Загрузка нового документа --}}
<form
  id="tn-upload-docs"
  action="{{ route('teilnehmer_dokumente.store', ['teilnehmer' => $teilnehmer->Teilnehmer_id]) }}"
  method="POST"
  enctype="multipart/form-data"
  class="mb-4 grid grid-cols-12 gap-3"
  onsubmit="console.log('docs submit fired')"
>
  @csrf

  <input type="hidden" name="teilnehmer_id" value="{{ $teilnehmer->Teilnehmer_id }}">

  <div class="col-span-4">
    <label class="block text-sm mb-1">Titel</label>
    <input type="text" name="titel" class="border rounded w-full px-3 py-2" placeholder="z.B. Ausweis Scan">
  </div>

  <div class="col-span-3">
    <label class="block text-sm mb-1">Typ</label>
    <select name="typ" class="border rounded w-full px-3 py-2" required>
      @foreach(\App\Models\TeilnehmerDokument::TYPEN as $t)
        <option value="{{ $t }}">{{ $t }}</option>
      @endforeach
    </select>
  </div>

  <div class="col-span-5">
    <label class="block text-sm mb-1">Datei *</label>
    <input type="file" name="file" required class="border rounded w-full px-3 py-2">
  </div>

  <div class="col-span-12">
    {{-- WICHTIG: type="submit" + form-Attribut zielt explizit auf DIESES Form --}}
    <button type="submit" form="tn-upload-docs" class="px-4 py-2 bg-blue-600 text-white rounded">
      Hochladen
    </button>
  </div>
</form>


  {{-- Список файлов участника --}}
  <table class="w-full text-left">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-3 py-2">Name</th>
        <th class="px-3 py-2">Typ</th>
        <th class="px-3 py-2">Hochgeladen</th>
        <th class="px-3 py-2 w-44">Aktionen</th>
      </tr>

    </thead>
    <tbody>
      @forelse($teilnehmer->dokumente as $d)
        <tr class="border-t">
          <td class="px-3 py-2">
            {{-- ВОТ ЭТА СТРОКА С НАЗВАНИЕМ --}}
            {{ $d->titel ?? $d->original_name ?? basename($d->dokument_pfad) }}
          </td>
          <td class="px-3 py-2">{{ $d->typ }}</td>
          <td class="px-3 py-2">
            {{ \Illuminate\Support\Carbon::parse($d->hochgeladen_am ?? $d->created_at)->format('d.m.Y H:i') }}
          </td>
          <td class="px-4 py-3">
            <a href="{{ route('teilnehmer_dokumente.download', $d) }}" class="text-blue-600 hover:underline">Download</a>
            <span class="mx-1 text-gray-300">|</span>
            <a href="{{ Storage::disk('public')->url($d->dokument_pfad) }}" target="_blank" class="text-blue-600 hover:underline">Öffnen</a>
            <span class="mx-1 text-gray-300">|</span>
            <form action="{{ route('teilnehmer_dokumente.destroy', $d) }}" method="POST" class="inline" onsubmit="return confirm('Wirklich löschen?');">
              @csrf @method('DELETE')
              <button class="text-red-600 hover:underline">Löschen</button>
            </form>
          </td>
        </tr>
      @empty
        <tr><td class="px-3 py-6 text-gray-500" colspan="4">Noch keine Dokumente.</td></tr>
      @endforelse
    </tbody>
  </table>
</div>
