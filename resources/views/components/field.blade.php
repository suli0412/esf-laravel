@props(['label' => '', 'class' => 'col-span-12'])
<div {{ $attributes->merge(['class' => $class]) }}>
    @if($label)
        <label class="block text-sm mb-1 text-gray-700">{{ $label }}</label>
    @endif
    {{ $slot }}
</div>
