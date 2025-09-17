@if($errors->any())
  <div class="rounded border border-red-200 bg-red-50 text-red-800 px-3 py-2 mb-2">
    <ul class="list-disc list-inside text-sm">
      @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
  </div>
@endif

<div class="grid grid-cols-2 gap-4">
  <div>
    <label class="block text-sm mb-1">Code *</label>
    <input name="code" value="{{ old('code', $projekt->code ?? '') }}" class="border rounded w-full px-3 py-2" required>
  </div>
  <div>
    <label class="block text-sm mb-1">Bezeichnung *</label>
    <input name="bezeichnung" value="{{ old('bezeichnung', $projekt->bezeichnung ?? '') }}" class="border rounded w-full px-3 py-2" required>
  </div>
  <div>
    <label class="block text-sm mb-1">Start</label>
    <input type="date" name="start" value="{{ old('start', optional($projekt->start ?? null)->toDateString()) }}" class="border rounded w-full px-3 py-2">
  </div>
  <div>
    <label class="block text-sm mb-1">Ende</label>
    <input type="date" name="ende" value="{{ old('ende', optional($projekt->ende ?? null)->toDateString()) }}" class="border rounded w-full px-3 py-2">
  </div>
  <div class="col-span-2">
    <label class="inline-flex items-center gap-2">
      <input type="checkbox" name="aktiv" value="1" @checked(old('aktiv', ($projekt->aktiv ?? true)) )>
      <span>Aktiv</span>
    </label>
  </div>
</div>
