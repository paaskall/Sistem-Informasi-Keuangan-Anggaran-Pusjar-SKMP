@props([
    'title' => '',
    'icon' => null,
    'subtitle' => null,
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => 'text-body-lg',
        'md' => 'text-h6',
        'lg' => 'text-h5',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

<div class="flex items-start gap-2.5">
    @if ($icon)
        <div class="w-9 h-9 rounded-lg bg-blue-50 text-brand-primary flex items-center justify-center flex-shrink-0">
            <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
        </div>
    @endif

    <div class="min-w-0 flex-1">
        <h2 class="{{ $sizeClass }} text-slate font-semibold leading-tight">
            {{ $title }}
        </h2>
        @if ($subtitle)
            <p class="text-caption text-muted mt-0.5">{{ $subtitle }}</p>
        @endif
        {{ $slot }}
    </div>
</div>
