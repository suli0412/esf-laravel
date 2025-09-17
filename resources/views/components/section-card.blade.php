@props(['title' => ''])

<section class="rounded-2xl border bg-white">
    <div class="px-5 py-4 border-b flex items-center justify-between">
        <h3 class="font-semibold text-gray-900">{{ $title }}</h3>
        <div class="text-sm">
            {{ $actions ?? '' }}
        </div>
    </div>
    <div class="p-5">
        {{ $slot }}
    </div>
</section>
