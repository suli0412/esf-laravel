@props(['title' => null])
<div class="rounded-xl border bg-white shadow-sm overflow-hidden">
  @if($title)
    <div class="px-4 py-3 border-b">
      <h3 class="font-medium">{{ $title }}</h3>
    </div>
  @endif
  <div class="p-4">
    {{ $slot }}
  </div>
</div>
