@props(['items'])

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
  @foreach($items as $t)
    <div class="bg-white rounded-2xl shadow border p-4">
      {{-- Header --}}
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
        <div>
          <h2 class="text-lg font-semibold">{{ $t->Vorname }} {{ $t->Nachname }}</h2>
          <p class="text-xs text-gray-500">
            ESF-Nummer: <span class="font-medium">#{{ $t->getKey() }}</span>
            @if(!empty($t->Gruppe) || !empty($t->gruppe))
              · Gruppe: {{ $t->Gruppe ?? optional($t->gruppe)->name ?? '—' }}
            @endif
          </p>
        </div>
        <div class="flex items-center gap-2">
          <a href="{{ route('teilnehmer.show', $t) }}" class="text-sm px-3 py-1.5 rounded-lg border hover:bg-gray-50">Details</a>
          <a href="{{ route('teilnehmer.edit', $t) }}" class="text-sm px-3 py-1.5 rounded-lg border hover:bg-gray-50">Bearbeiten</a>
        </div>
      </div>

      {{-- 2 Spalten: LINKS Dropdown-Tabs, RECHTS Blöcke --}}
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- LINKS: Dropdown-Tabs (native details/summary) --}}
        <div class="space-y-2">
          <details class="border rounded-xl overflow-hidden group">
            <summary class="cursor-pointer list-none px-3 py-2 flex items-center justify-between">
              <span class="text-sm font-medium">Name & Nachname</span>
              <span class="text-gray-400 group-open:rotate-180 transition transform">▾</span>
            </summary>
            <div class="px-3 pb-3 text-sm text-gray-700">
              <div class="grid grid-cols-2 gap-2">
                <div><span class="text-gray-500">Nachname</span><div class="font-medium">{{ $t->Nachname ?: '—' }}</div></div>
                <div><span class="text-gray-500">Vorname</span><div class="font-medium">{{ $t->Vorname ?: '—' }}</div></div>
              </div>
            </div>
          </details>

          <details class="border rounded-xl overflow-hidden group">
            <summary class="cursor-pointer list-none px-3 py-2 flex items-center justify-between">
              <span class="text-sm font-medium">ESF-Nummer & AMS-Bericht & Checkliste Nermin</span>
              <span class="text-gray-400 group-open:rotate-180 transition transform">▾</span>
            </summary>
            <div class="px-3 pb-3 text-sm text-gray-700 space-y-2">
              <div class="grid grid-cols-3 gap-2">
                <div>
                  <span class="text-gray-500">ESF-Nummer</span>
                  <div class="font-medium">#{{ $t->getKey() }}</div>
                </div>
                <div>
                  <span class="text-gray-500">AMS-Bericht</span>
                  <div class="font-medium">
                    @if(!empty($t->ams_bericht_url))
                      <a href="{{ $t->ams_bericht_url }}" class="underline">Öffnen</a>
                    @else
                      —
                    @endif
                  </div>
                </div>
                <div class="flex items-end">
                  <label class="inline-flex items-center gap-2">
                    <input type="checkbox" disabled
                           @checked(optional($t->checkliste)->nermin_check ?? false)
                           class="rounded border-gray-300">
                    <span>Checkliste Nermin</span>
                  </label>
                </div>
              </div>
            </div>
          </details>

          <details class="border rounded-xl overflow-hidden group">
            <summary class="cursor-pointer list-none px-3 py-2 flex items-center justify-between">
              <span class="text-sm font-medium">Karten</span>
              <span class="text-gray-400 group-open:rotate-180 transition transform">▾</span>
            </summary>
            <div class="px-3 pb-3 text-sm text-gray-700">
              <div class="text-gray-500">Keine Karten hinterlegt.</div>
            </div>
          </details>

          <details class="border rounded-xl overflow-hidden group">
            <summary class="cursor-pointer list-none px-3 py-2 flex items-center justify-between">
              <span class="text-sm font-medium">Stammdaten</span>
              <span class="text-gray-400 group-open:rotate-180 transition transform">▾</span>
            </summary>
            <div class="px-3 pb-3 text-sm text-gray-700">
              <div class="grid grid-cols-2 gap-2">
                <div><span class="text-gray-500">SVN</span><div class="font-medium">{{ $t->SVN ?? '—' }}</div></div>
                <div><span class="text-gray-500">Geburtsdatum</span><div class="font-medium">{{ optional($t->Geburtsdatum)->format('d.m.Y') ?? '—' }}</div></div>
                <div class="col-span-2"><span class="text-gray-500">Adresse</span><div class="font-medium">{{ $t->Adresse ?? '—' }}</div></div>
                <div><span class="text-gray-500">E-Mail</span><div class="font-medium">{{ $t->Email ?? '—' }}</div></div>
                <div><span class="text-gray-500">Telefon</span><div class="font-medium">{{ $t->Telefon ?? $t->Telefonnummer ?? '—' }}</div></div>
              </div>
            </div>
          </details>

          <details class="border rounded-xl overflow-hidden group">
            <summary class="cursor-pointer list-none px-3 py-2 flex items-center justify-between">
              <span class="text-sm font-medium">Soziale Merkmale</span>
              <span class="text-gray-400 group-open:rotate-180 transition transform">▾</span>
            </summary>
            <div class="px-3 pb-3 text-sm text-gray-700">
              <div class="grid grid-cols-2 gap-2">
                <div>Minderheit: <span class="font-medium">{{ $t->Minderheit ? 'Ja' : 'Nein' }}</span></div>
                <div>Behinderung: <span class="font-medium">{{ $t->Behinderung ? 'Ja' : 'Nein' }}</span></div>
                <div>Obdachlos: <span class="font-medium">{{ $t->Obdachlos ? 'Ja' : 'Nein' }}</span></div>
                <div>Ländliche Gebiete: <span class="font-medium">{{ $t->Laendliche_Gebiete ? 'Ja' : 'Nein' }}</span></div>
                <div>Eltern im Ausland geboren: <span class="font-medium">{{ $t->Eltern_im_Ausland ? 'Ja' : 'Nein' }}</span></div>
                <div>Armutsbetroffen: <span class="font-medium">{{ $t->Armutsbetroffen ? 'Ja' : 'Nein' }}</span></div>
                <div>Armutsgefährdet: <span class="font-medium">{{ $t->Armutsgefährdet ? 'Ja' : 'Nein' }}</span></div>
                <div>Bildungshintergrund: <span class="font-medium">{{ $t->ISCED ?? '—' }}</span></div>
              </div>
            </div>
          </details>

          <details class="border rounded-xl overflow-hidden group">
            <summary class="cursor-pointer list-none px-3 py-2 flex items-center justify-between">
              <span class="text-sm font-medium">Sonstiges</span>
              <span class="text-gray-400 group-open:rotate-180 transition transform">▾</span>
            </summary>
            <div class="px-3 pb-3 text-sm text-gray-700 space-y-2">
              <div>Berufswunsch: <span class="font-medium">{{ $t->Berufswunsch ?? '—' }}</span></div>
              <div>Branche: <span class="font-medium">{{ $t->Branche ?? '—' }}</span></div>
              <div>Clearing-Gruppe: <span class="font-medium">{{ $t->Clearing_gruppe ? 'Ja' : 'Nein' }}</span></div>
              <div>Anmerkung: <span class="font-medium">{{ $t->Anmerkung ?? '—' }}</span></div>
            </div>
          </details>

          <details class="border rounded-xl overflow-hidden group">
            <summary class="cursor-pointer list-none px-3 py-2 flex items-center justify-between">
              <span class="text-sm font-medium">Kompetenzen</span>
              <span class="text-gray-400 group-open:rotate-180 transition transform">▾</span>
            </summary>
            <div class="px-3 pb-3 text-sm text-gray-700">
              <div class="text-gray-500">Kurzüberblick (Eintritt/Austritt) – Details in der Teilnehmer-Detailseite.</div>
            </div>
          </details>

          <details class="border rounded-xl overflow-hidden group">
            <summary class="cursor-pointer list-none px-3 py-2 flex items-center justify-between">
              <span class="text-sm font-medium">Unterlagen & Ziele</span>
              <span class="text-gray-400 group-open:rotate-180 transition transform">▾</span>
            </summary>
            <div class="px-3 pb-3 text-sm text-gray-700 space-y-1">
              <div>IDEA Stammdatenblatt: <span class="font-medium">{{ $t->IDEA_Stammdatenblatt ? 'Ja' : 'Nein' }}</span></div>
              <div>IDEA Dokumente: <span class="font-medium">{{ $t->IDEA_Dokumente ? 'Ja' : 'Nein' }}</span></div>
              <div>PAZ Lehrstelle: <span class="font-medium">{{ $t->PAZ_Lehrstelle ? 'Ja' : 'Nein' }}</span></div>
            </div>
          </details>
        </div>

        {{-- RECHTS: Fixe Blöcke --}}
        <div class="space-y-3">
          {{-- Beratungen --}}
          <div class="border rounded-xl p-3">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-semibold">Beratungen</h3>
              <a href="{{ route('teilnehmer.show', $t) }}#beratungen" class="text-xs underline">alle</a>
            </div>
            <div class="text-xs text-gray-500 mb-2">{{ $t->beratungen_count ?? 0 }} Einträge</div>
            <div class="divide-y">
              @forelse($t->beratungen ?? [] as $b)
                <div class="py-2 text-sm flex items-center justify-between">
                  <span>{{ \Illuminate\Support\Carbon::parse($b->datum)->format('d.m.Y') }} · {{ $b->art ?? '—' }}</span>
                  <span class="text-gray-500">{{ $b->dauer_stunden ?? '—' }} h</span>
                </div>
              @empty
                <div class="text-sm text-gray-500 py-2">Keine Einträge</div>
              @endforelse
            </div>
          </div>

          {{-- Anwesenheiten --}}
          <div class="border rounded-xl p-3">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-semibold">Anwesenheiten</h3>
              <a href="{{ route('teilnehmer.show', $t) }}#anwesenheiten" class="text-xs underline">alle</a>
            </div>
            <div class="text-xs text-gray-500 mb-2">{{ $t->anwesenheiten_count ?? 0 }} Einträge</div>
            <div class="divide-y">
              @forelse($t->anwesenheiten ?? [] as $a)
                <div class="py-2 text-sm flex items-center justify-between">
                  <span>{{ \Illuminate\Support\Carbon::parse($a->datum)->format('d.m.Y') }}</span>
                  <span class="text-gray-500">{{ $a->status ?? '—' }}</span>
                </div>
              @empty
                <div class="text-sm text-gray-500 py-2">Keine Einträge</div>
              @endforelse
            </div>
          </div>

          {{-- Praktika --}}
          <div class="border rounded-xl p-3">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-semibold">Praktika</h3>
              <a href="{{ route('teilnehmer.show', $t) }}#praktika" class="text-xs underline">alle</a>
            </div>
            <div class="text-xs text-gray-500 mb-2">{{ $t->praktika_count ?? 0 }} Einträge</div>
            <div class="divide-y">
              @forelse($t->praktika ?? [] as $p)
                <div class="py-2 text-sm">
                  <div class="font-medium">{{ $p->firma ?? '—' }}</div>
                  <div class="text-gray-500 text-xs">
                    {{ \Illuminate\Support\Carbon::parse($p->von)->format('d.m.Y') }} – {{ \Illuminate\Support\Carbon::parse($p->bis)->format('d.m.Y') }}
                  </div>
                </div>
              @empty
                <div class="text-sm text-gray-500 py-2">Keine Einträge</div>
              @endforelse
            </div>
          </div>

          {{-- Dokumente --}}
          <div class="border rounded-xl p-3">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-semibold">Dokumente</h3>
              <a href="{{ route('teilnehmer.show', $t) }}#dokumente" class="text-xs underline">alle</a>
            </div>
            <div class="text-xs text-gray-500 mb-2">{{ $t->dokumente_count ?? 0 }} Einträge</div>
            <div class="divide-y">
              @forelse($t->dokumente ?? [] as $d)
                <div class="py-2 text-sm flex items-center justify-between">
                  <span class="truncate">{{ $d->titel ?? $d->dateiname ?? 'Dokument' }}</span>
                  <span class="text-gray-500 text-xs">{{ optional($d->created_at)->format('d.m.Y') }}</span>
                </div>
              @empty
                <div class="text-sm text-gray-500 py-2">Keine Einträge</div>
              @endforelse
            </div>
          </div>
        </div>

      </div>
    </div>
  @endforeach
</div>

{{-- Pagination --}}
<div class="mt-6">
  {{ $items->withQueryString()->links() }}
</div>
