@props(['label' => '', 'value' => 0, 'hint' => null])

<div class="rounded-2xl border bg-white p-5">
    <div class="text-sm text-gray-500">{{ $label }}</div>
    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $value }}</div>
    @if ($hint)
        <div class="mt-1 text-xs text-gray-500">{{ $hint }}</div>
    @endif
</div>
