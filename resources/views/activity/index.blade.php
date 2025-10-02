@extends('layouts.app')
@section('title','Activity Logs')

@section('content')
<div class="flex items-center justify-between mb-6 gap-3">
  <h2 class="text-2xl font-bold">Activity Logs</h2>

  <form method="GET" class="flex flex-wrap items-end gap-2">
    <div>
      <label class="text-sm text-gray-600">Suche</label>
      <input name="q" value="{{ $q }}" class="border rounded px-3 py-2" placeholder="Text, Event, Feld…">
    </div>
    <div>
      <label class="text-sm text-gray-600">Log</label>
      <select name="log" class="border rounded px-3 py-2">
        <option value="">Alle</option>
        @foreach($logNames as $ln)
          <option value="{{ $ln }}" @selected($ln===$log)>{{ $ln }}</option>
        @endforeach
      </select>
    </div>
    <div>
      <label class="text-sm text-gray-600">Von</label>
      <input type="date" name="from" value="{{ $dateFrom }}" class="border rounded px-3 py-2">
    </div>
    <div>
      <label class="text-sm text-gray-600">Bis</label>
      <input type="date" name="to" value="{{ $dateTo }}" class="border rounded px-3 py-2">
    </div>
    <button class="px-4 py-2 bg-gray-800 text-white rounded">Filtern</button>
    @if($q || $log || $dateFrom || $dateTo)
      <a href="{{ route('activity.index') }}" class="px-3 py-2 border rounded">Reset</a>
    @endif
  </form>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
  <table class="w-full text-left">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-3 py-2">Zeit</th>
        <th class="px-3 py-2">User</th>
        <th class="px-3 py-2">Log</th>
        <th class="px-3 py-2">Event</th>
        <th class="px-3 py-2">Beschreibung</th>
        <th class="px-3 py-2">Subject</th>
      </tr>
    </thead>
    <tbody>
    @forelse($rows as $a)
      <tr class="border-t align-top">
        <td class="px-3 py-2 whitespace-nowrap">{{ $a->created_at?->format('d.m.Y H:i') }}</td>
        <td class="px-3 py-2">{{ $a->causer?->name ?? '—' }}</td>
        <td class="px-3 py-2">{{ $a->log_name }}</td>
        <td class="px-3 py-2">{{ $a->event }}</td>
        <td class="px-3 py-2">{{ $a->description }}</td>
        <td class="px-3 py-2">
          {{ optional($a->subject)->getTable() ?? '—' }}
          @if($a->subject_id) #{{ $a->subject_id }} @endif
        </td>
      </tr>
    @empty
      <tr><td class="px-3 py-6 text-gray-500" colspan="6">Keine Einträge.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $rows->links() }}</div>
@endsection
