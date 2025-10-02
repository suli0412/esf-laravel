@extends('layouts.app')

@section('title','Beratungen')

@section('content')
  {{-- Kopfzeile + optionaler Filter --}}
  <div class="flex items-center justify-between mb-6 gap-3">
    <h2 class="text-2xl font-bold">Beratungen</h2>

    {{-- Beispiel: einfacher Suchfilter (falls du schon $q o.ä. verarbeitest) --}}
    <form method="GET" class="hidden md:block">
      <div class="flex items-center gap-2">
        <input type="text"
               name="q"
               value="{{ request('q') }}"
               placeholder="Suche…"
               class="border rounded-xl px-3 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <button class="px-3 py-2 rounded-xl border hover:bg-gray-50">Filtern</button>
      </div>
    </form>
  </div>

  {{-- Hier könnte deine Tabelle/Liste der Beratungen stehen --}}
  {{-- @includeIf('beratungen._table', [...]) --}}

  @can('beratung.manage')
    <div class="space-y-6">

      {{-- ============ Einzelberatung ============ --}}
      <div class="rounded-2xl border bg-white shadow">
        <div class="px-5 py-4 border-b flex items-center justify-between">
          <div>
            <h3 class="text-base font-semibold">Einzelberatung erfassen</h3>
            <p class="text-sm text-gray-500">Schnell neue 1:1-Beratung dokumentieren</p>
          </div>
          <div class="text-xl">💬</div>
        </div>

        <form action="{{ route('beratungen.store') }}" method="POST" class="p-5">
          @csrf

          @if ($errors->any() && session('form') === 'einzel')
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
              <strong>Bitte Eingaben prüfen:</strong>
              <ul class="list-disc ml-5 mt-1">
                @foreach ($errors->all() as $e)
                  <li>{{ $e }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm mb-1">Teilnehmer *</label>
              <select name="teilnehmer_id"
                      class="w-full border rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                      required>
                <option value="">— wählen —</option>
                @foreach($teilnehmer as $t)
                  <option value="{{ $t->Teilnehmer_id }}" @selected(old('teilnehmer_id') == $t->Teilnehmer_id)>
                    {{ $t->Nachname }}, {{ $t->Vorname }}{{ $t->Email ? ' — '.$t->Email : '' }}
                  </option>
                @endforeach
              </select>
            </div>

            <div>
              <label class="block text-sm mb-1">Datum *</label>
              <input type="date" name="datum" value="{{ old('datum') }}"
                     class="w-full border rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                     required>
            </div>

            <div>
              <label class="block text-sm mb-1">Art *</label>
              <select name="art_id"
                      class="w-full border rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                      required>
                <option value="">— wählen —</option>
                @foreach($arten as $a)
                  <option value="{{ $a->Art_id }}" @selected(old('art_id') == $a->Art_id)>{{ $a->label }}</option>
                @endforeach
              </select>
            </div>

            <div>
              <label class="block text-sm mb-1">Thema (Katalog)</label>
              <select name="thema_id"
                      class="w-full border rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">— optional —</option>
                @foreach($themen as $th)
                  <option value="{{ $th->Thema_id }}" @selected(old('thema_id') == $th->Thema_id)>{{ $th->label }}</option>
                @endforeach
              </select>
            </div>

            <div>
              <label class="block text-sm mb-1">Mitarbeiter</label>
              <select name="mitarbeiter_id"
                      class="w-full border rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">— optional —</option>
                @foreach($mitarbeiter as $m)
                  <option value="{{ $m->Mitarbeiter_id }}" @selected(old('mitarbeiter_id') == $m->Mitarbeiter_id)>
                    {{ $m->Nachname }}, {{ $m->Vorname }}
                  </option>
                @endforeach
              </select>
            </div>

            <div>
              <label class="block text-sm mb-1">Dauer (h)</label>
              <input type="number" step="0.5" min="0" max="24" name="dauer_h" value="{{ old('dauer_h') }}"
                     class="w-full border rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm mb-1">Thema (frei)</label>
              <input type="text" name="thema" value="{{ old('thema') }}"
                     class="w-full border rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm mb-1">Inhalt/Notizen</label>
              <textarea name="inhalt" rows="4"
                        class="w-full border rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('inhalt') }}</textarea>
            </div>

            <div class="flex items-center gap-2">
              <input id="TNUnterlagen1" type="checkbox" name="TNUnterlagen" value="1"
                     class="rounded border-gray-300" @checked(old('TNUnterlagen'))>
              <label for="TNUnterlagen1" class="text-sm">Unterlagen an TN ausgegeben</label>
            </div>

            <div class="md:col-span-2">
              <button class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white">Speichern</button>
            </div>
          </div>

          {{-- Herkunft des Fehlers markieren --}}
          <input type="hidden" name="form" value="einzel">
        </form>
      </div>

      {{-- ============ Gruppenberatung ============ --}}
      <div class="rounded-2xl border bg-white shadow">
        <div class="px-5 py-4 border-b flex items-center justify-between">
          <div>
            <h3 class="text-base font-semibold">Gruppenberatung erfassen</h3>
            <p class="text-sm text-gray-500">Mehrere Teilnehmer auswählen & dokumentieren</p>
          </div>
          <div class="text-xl">👥</div>
        </div>

        <form action="{{ route('gruppen_beratungen.store') }}" method="POST" class="p-5">
          @csrf

          @if ($errors->any() && session('form') === 'gruppe')
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
              <strong>Bitte Eingaben prüfen:</strong>
              <ul class="list-disc ml-5 mt-1">
                @foreach ($errors->all() as $e)
                  <li>{{ $e }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <div class="flex items-center justify-between gap-3 mb-1">
                <label class="block text-sm">Teilnehmer (Mehrfachauswahl)</label>
                <input type="text" placeholder="Suchen…" id="gb-tn-filter"
                       class="w-64 border rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>

              @php $selected = collect(old('teilnehmer_ids', []))->map(fn($v)=>(int)$v)->all(); @endphp
              <select name="teilnehmer_ids[]" id="gb-tn-select" multiple size="8"
                      class="w-full border rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                @foreach($teilnehmer as $t)
                  <option value="{{ $t->Teilnehmer_id }}" @selected(in_array($t->Teilnehmer_id, $selected))>
                    {{ $t->Nachname }}.................................... {{ $t->Vorname }}{{ $t->Email ? '.............................'.$t->Email : '' }}
                  </option>
                @endforeach
              </select>
              <p class="text-xs text-gray-500 mt-1">
                Tipp: Strg/Cmd für Mehrfachauswahl. Oben nach Namen/Email filtern.
              </p>
            </div>

            <div>
              <label class="block text-sm mb-1">Datum *</label>
              <input type="date" name="datum" value="{{ old('datum') }}"
                     class="w-full border rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>

            <div>
              <label class="block text-sm mb-1">Art *</label>
              <select name="art_id"
                      class="w-full border rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                      required>
                <option value="">— wählen —</option>
                @foreach($arten as $a)
                  <option value="{{ $a->Art_id }}" @selected(old('art_id') == $a->Art_id)>{{ $a->label }}</option>
                @endforeach
              </select>
            </div>

            <div>
              <label class="block text-sm mb-1">Thema (Katalog)</label>
              <select name="thema_id"
                      class="w-full border rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">— optional —</option>
                @foreach($themen as $th)
                  <option value="{{ $th->Thema_id }}" @selected(old('thema_id') == $th->Thema_id)>{{ $th->label }}</option>
                @endforeach
              </select>
            </div>

            <div>
              <label class="block text-sm mb-1">Mitarbeiter</label>
              <select name="mitarbeiter_id"
                      class="w-full border rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">— optional —</option>
                @foreach($mitarbeiter as $m)
                  <option value="{{ $m->Mitarbeiter_id }}" @selected(old('mitarbeiter_id') == $m->Mitarbeiter_id)>
                    {{ $m->Nachname }}, {{ $m->Vorname }}
                  </option>
                @endforeach
              </select>
            </div>

            <div>
              <label class="block text-sm mb-1">Dauer (h)</label>
              <input type="number" step="0.5" min="0" max="24" name="dauer_h" value="{{ old('dauer_h') }}"
                     class="w-full border rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm mb-1">Thema (frei)</label>
              <input type="text" name="thema" value="{{ old('thema') }}"
                     class="w-full border rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm mb-1">Inhalt/Notizen</label>
              <textarea name="inhalt" rows="4"
                        class="w-full border rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('inhalt') }}</textarea>
            </div>

            <div class="flex items-center gap-2">
              <input id="TNUnterlagen2" type="checkbox" name="TNUnterlagen" value="1"
                     class="rounded border-gray-300" @checked(old('TNUnterlagen'))>
              <label for="TNUnterlagen2" class="text-sm">Unterlagen an TN ausgegeben</label>
            </div>

            <div class="md:col-span-2">
              <button class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white">Speichern</button>
            </div>
          </div>

          <input type="hidden" name="form" value="gruppe">
        </form>
      </div>
    </div>
  @endcan
@endsection

{{-- Mini-Filter für die Mehrfachauswahl (Tailwind-kompatibel) --}}
@push('scripts')
<script>
  (function () {
    const input = document.getElementById('gb-tn-filter');
    const select = document.getElementById('gb-tn-select');
    if (!input || !select) return;
    input.addEventListener('input', function () {
      const q = this.value.toLowerCase();
      Array.from(select.options).forEach(o => {
        o.hidden = q && !o.textContent.toLowerCase().includes(q);
      });
    });
  })();
</script>
@endpush
