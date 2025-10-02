@php($scope = $scope ?? 'einzel')
<form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
  <div>
    <label class="text-sm text-gray-600">Von</label>
    <input type="date" name="von" value="{{ request('von') }}" class="border rounded px-3 py-2">
  </div>
  <div>
    <label class="text-sm text-gray-600">Bis</label>
    <input type="date" name="bis" value="{{ request('bis') }}" class="border rounded px-3 py-2">
  </div>
  <div>
    <label class="text-sm text-gray-600">Mitarbeiter</label>
    <select name="mitarbeiter_id" class="border rounded px-3 py-2">
      <option value="">— alle —</option>
      @foreach(($mitarbeiter ?? []) as $m)
        <option value="{{ $m->Mitarbeiter_id }}" @selected(request('mitarbeiter_id')==$m->Mitarbeiter_id)>
          {{ $m->Nachname }}, {{ $m->Vorname }}
        </option>
      @endforeach
    </select>
  </div>

  @if($scope==='einzel')
    <div>
      <label class="text-sm text-gray-600">Teilnehmer</label>
      <input type="number" name="teilnehmer_id" value="{{ request('teilnehmer_id') }}" class="border rounded px-3 py-2" placeholder="ID">
    </div>
  @else
    <div>
      <label class="text-sm text-gray-600">Gruppe</label>
      <select name="gruppe_id" class="border rounded px-3 py-2">
        <option value="">— alle —</option>
        @foreach(($gruppen ?? \App\Models\Gruppe::orderBy('name')->get()) as $g)
          <option value="{{ $g->gruppe_id }}" @selected(request('gruppe_id')==$g->gruppe_id)>{{ $g->name }}</option>
        @endforeach
      </select>
    </div>
  @endif

  <div>
    <label class="text-sm text-gray-600">Art</label>
    <select name="beratungsart_id" class="border rounded px-3 py-2">
      <option value="">— alle —</option>
      @foreach(($arten ?? []) as $a)
        <option value="{{ $a->id }}" @selected(request('beratungsart_id')==$a->id)>{{ $a->name }}</option>
      @endforeach
    </select>
  </div>
  <div>
    <label class="text-sm text-gray-600">Thema</label>
    <select name="beratungsthema_id" class="border rounded px-3 py-2">
      <option value="">— alle —</option>
      @foreach(($themen ?? []) as $t)
        <option value="{{ $t->id }}" @selected(request('beratungsthema_id')==$t->id)>{{ $t->name }}</option>
      @endforeach
    </select>
  </div>
  <div>
    <label class="text-sm text-gray-600">Suche</label>
    <input type="text" name="q" value="{{ request('q') }}" class="border rounded px-3 py-2" placeholder="Notiz...">
  </div>

  <button class="px-4 py-2 bg-gray-800 text-white rounded">Filtern</button>
  @if(request()->query())
    <a href="{{ url()->current() }}" class="px-3 py-2 border rounded">Reset</a>
  @endif
</form>
