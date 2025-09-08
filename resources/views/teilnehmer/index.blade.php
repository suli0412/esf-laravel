@extends('layouts.app')

@section('title','Teilnehmer')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-2xl font-bold">Teilnehmer</h2>
    <a href="{{ route('teilnehmer.create') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
        + Neuer Teilnehmer
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-left">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-sm font-medium text-gray-600">ID</th>
                <th class="px-4 py-3 text-sm font-medium text-gray-600">Nachname</th>
                <th class="px-4 py-3 text-sm font-medium text-gray-600">Vorname</th>
                <th class="px-4 py-3 text-sm font-medium text-gray-600">Email</th>
                <th class="px-4 py-3 text-sm font-medium text-gray-600">Aktionen</th>
            </tr>
        </thead>
        <tbody>
            @forelse($teilnehmer as $t)
                <tr class="border-t">
                    <td class="px-4 py-3">{{ $t->Teilnehmer_id }}</td>
                    <td class="px-4 py-3">{{ $t->Nachname }}</td>
                    <td class="px-4 py-3">{{ $t->Vorname }}</td>
                    <td class="px-4 py-3">{{ $t->Email }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('teilnehmer.show',$t) }}" class="text-blue-600 hover:underline">Ansehen</a>
                        <span class="mx-1 text-gray-300">|</span>
                        <a href="{{ route('teilnehmer.edit',$t) }}" class="text-yellow-700 hover:underline">Bearbeiten</a>
                        <span class="mx-1 text-gray-300">|</span>
                        <form action="{{ route('teilnehmer.destroy',$t) }}" method="POST" class="inline" onsubmit="return confirm('Löschen?')">
                            @csrf @method('DELETE')
                            <button class="text-red-700 hover:underline">Löschen</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr class="border-t">
                    <td class="px-4 py-6 text-gray-500" colspan="5">Keine Einträge.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

