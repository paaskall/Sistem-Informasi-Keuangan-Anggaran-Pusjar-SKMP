@props([
    'padding' => 'p-6',        // p-4 | p-6 (default 24px)
    'radius' => 'rounded-lg',  // 16px
    'shadow' => 'shadow-soft',
    'border' => true,
    'header' => null,
    'footer' => null,
])

@php
    $borderClass = $border ? 'border border-gray-100' : '';
    $classes = "bg-white {$radius} {$borderClass} {$shadow} overflow-hidden";
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if ($header)
        <div class="border-b border-gray-100 px-6 py-4">
            {{ $header }}
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>

    @if ($footer)
        <div class="border-t border-gray-100 px-6 py-4 bg-gray-50">
            {{ $footer }}
        </div>
    @endif
</div>
