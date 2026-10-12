@props([
    'variant' => 'default',
    'icon' => null,
    'size' => 'md',
])

@php
    $variants = [
        'default' => 'bg-gray-100 text-gray-700',
        'primary' => 'bg-blue-100 text-brand-primary',
        'secondary' => 'bg-cyan-100 text-cyan-700',
        'accent' => 'bg-amber-100 text-amber-800',
        'success' => 'bg-green-100 text-green-700',
        'warning' => 'bg-amber-100 text-amber-700',
        'error' => 'bg-red-100 text-red-700',
        'info' => 'bg-blue-100 text-blue-700',
        'purple' => 'bg-purple-100 text-purple-700',
    ];
    $sizes = [
        'sm' => 'px-2 py-0.5 text-caption',
        'md' => 'px-2.5 py-1 text-caption',
    ];
    $v = $variants[$variant] ?? $variants['default'];
    $s = $sizes[$size] ?? $sizes['md'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 font-semibold rounded-full {$v} {$s}"]) }}>
    @if ($icon)
        <i data-lucide="{{ $icon }}" class="w-3 h-3"></i>
    @endif
    {{ $slot }}
</span>
