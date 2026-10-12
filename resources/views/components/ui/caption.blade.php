@props([
    'variant' => 'default',  // default | muted | error | success | warning
    'icon' => null,
])

@php
    $variants = [
        'default' => 'text-gray-700',
        'muted' => 'text-muted',
        'error' => 'text-red-600',
        'success' => 'text-green-700',
        'warning' => 'text-amber-700',
    ];
    $v = $variants[$variant] ?? $variants['default'];
@endphp

<p {{ $attributes->merge(['class' => "text-caption font-medium flex items-center gap-1 {$v}"]) }}>
    @if ($icon)
        <i data-lucide="{{ $icon }}" class="w-3 h-3 flex-shrink-0"></i>
    @endif
    {{ $slot }}
</p>
