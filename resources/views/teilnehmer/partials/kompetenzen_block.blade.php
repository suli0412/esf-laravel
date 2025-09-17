@php
  $kompetenzen = $kompetenzen ?? \App\Models\Kompetenz::orderBy('code')->get();
@endphp

@if($kompetenzen->isEmpty())
  <p class="text-sm text-gray-500">Noch keine Kompetenzen definiert.</p>
@else
  <table class="w-full text-sm">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-3 py-2 text-left">Code</th>
        <th class="px-3 py-2 text-left">Bezeichnung</th>
      </tr>
    </thead>
    <tbody>
      @foreach($kompetenzen as $k)
        <tr class="border-t">
          <td class="px-3 py-2">{{ $k->code }}</td>
          <td class="px-3 py-2">{{ $k->bezeichnung }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
@endif

<div class="mt-3 text-sm">
  <a class="text-blue-600 hover:underline mr-3" href="{{ route('kompetenzen.index') }}">Kompetenzen verwalten</a>
  <a class="text-blue-600 hover:underline" href="{{ route('niveaus.index') }}">Niveaus verwalten</a>
</div>
