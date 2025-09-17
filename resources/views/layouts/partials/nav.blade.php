<nav class="space-y-1">
  {{-- bestehende Links, Beispiel --}}
  <a href="{{ route('dashboard') }}"
     class="block px-3 py-2 rounded {{ request()->routeIs('dashboard') ? 'bg-gray-200 font-semibold' : 'hover:bg-gray-100' }}">
    Dashboard
  </a>
  <a href="{{ route('teilnehmer.index') }}"
     class="block px-3 py-2 rounded {{ request()->routeIs('teilnehmer.*') ? 'bg-gray-200 font-semibold' : 'hover:bg-gray-100' }}">
    Teilnehmer
  </a>
  <a href="{{ route('beratungen.index') }}"
     class="block px-3 py-2 rounded {{ request()->routeIs('beratungen.*') ? 'bg-gray-200 font-semibold' : 'hover:bg-gray-100' }}">
    Beratungen
  </a>

  {{-- Admin-Bereich --}}
  <div class="pt-3 mt-3 border-t text-xs uppercase tracking-wide text-gray-500">Admin</div>

  <a href="{{ route('admin.kompetenzen.index') }}"
     class="block px-3 py-2 rounded {{ request()->routeIs('admin.kompetenzen.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-100' }}">
    Kompetenzen
  </a>
  <a href="{{ route('admin.niveaus.index') }}"
     class="block px-3 py-2 rounded {{ request()->routeIs('admin.niveaus.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-100' }}">
    Niveaus
  </a>
  <a href="{{ route('admin.pruefungstermine.index') }}"
     class="block px-3 py-2 rounded {{ request()->routeIs('admin.pruefungstermine.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-100' }}">
    Prüfungstermine
  </a>
</nav>
