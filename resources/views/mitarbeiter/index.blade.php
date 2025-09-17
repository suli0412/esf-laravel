@extends('layouts.app')
@section('title','Mitarbeiter')

@section('content')
<div class="flex items-center justify-between mb-6 gap-3">
  <h2 class="text-2xl font-bold">Mitarbeiter</h2>

  {{-- Поиск --}}
  <form method="GET" action="{{ route('mitarbeiter.index') }}" class="flex items-center gap-2">
    <input type="text" name="q" value="{{ $q ?? request('q') }}" placeholder="Suche Name/E-Mail"
           class="border rounded px-3 py-2">
    @if(!empty($q ?? request('q')))
      <a href="{{ route('mitarbeiter.index') }}" class="px-3 py-2 border rounded">Zurücksetzen</a>
    @endif
    <button class="px-4 py-2 bg-gray-800 text-white rounded">Suchen</button>
  </form>

  <a href="{{ route('mitarbeiter.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded">
    + Neuer Mitarbeiter
  </a>

</div>

@if(session('success'))
  <div class="mb-4 rounded border bg-green-50 text-green-800 px-3 py-2">
    {{ session('success') }}
  </div>
@endif

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
  <table class="w-full text-left">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-3 py-2">ID</th>
        <th class="px-3 py-2">Nachname</th>
        <th class="px-3 py-2">Vorname</th>
        <th class="px-3 py-2">Tätigkeit</th>
        <th class="px-3 py-2">E-Mail</th>
        <th class="px-3 py-2">Aktionen</th>
      </tr>
    </thead>
    <tbody>
      @forelse($mitarbeiter as $m)
        <tr class="border-t">
          <td class="px-3 py-2">{{ $m->Mitarbeiter_id }}</td>
          <td class="px-3 py-2">{{ $m->Nachname }}</td>
          <td class="px-3 py-2">{{ $m->Vorname }}</td>
          <td class="px-3 py-2">{{ $m->Taetigkeit }}</td>
          <td class="px-3 py-2">{{ $m->Email }}</td>
          <td class="px-3 py-2">
            {{-- ВОТ ЗДЕСЬ ССЫЛКА ÖFFNEN --}}
            <a href="{{ route('mitarbeiter.show', $m) }}" class="text-blue-600 hover:underline">Öffnen</a>
            <span class="mx-1 text-gray-300">|</span>
            <a href="{{ route('mitarbeiter.edit', $m) }}" class="text-yellow-700 hover:underline">Bearbeiten</a>
            <span class="mx-1 text-gray-300">|</span>
            <form action="{{ route('mitarbeiter.destroy', $m) }}" method="POST" class="inline"
                  onsubmit="return confirm('Löschen?')">
              @csrf @method('DELETE')
              <button class="text-red-700 hover:underline">Löschen</button>
            </form>
          </td>
        </tr>
      @empty
        <tr class="border-t">
          <td class="px-3 py-6 text-gray-500" colspan="6">Keine Einträge.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">
  {{ $mitarbeiter->links() }}
</div>
@endsection
