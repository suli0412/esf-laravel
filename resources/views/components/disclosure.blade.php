@props(['title','open'=>false])

<details {{ $open ? 'open' : '' }} class="group rounded-xl border bg-white shadow-sm">
  <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3">
    <span class="font-medium">{{ $title }}</span>
    <svg class="h-4 w-4 transition-transform duration-200 group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
      <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
    </svg>
  </summary>
  <div class="px-4 pb-4">
    {{ $slot }}
  </div>
</details>
