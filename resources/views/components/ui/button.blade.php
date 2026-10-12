@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'icon' => null,
    'iconPosition' => 'left',
    'href' => null,
    'disabled' => false,
])

@php
    // Base classes (Blueprint typography)
    $base = 'inline-flex items-center justify-center gap-2 text-label font-semibold rounded-md transition-blueprint focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 whitespace-nowrap';

    // Size (Blueprint spec: 44px height)
    $sizes = [
        'sm' => 'h-9 px-3',
        'md' => 'h-11 px-5',
    ];
    $sizeClass = $sizes[$size] ?? $sizes['md'];

    // Variant (Blueprint spec)
    $variants = [
        'primary' => 'bg-brand-primary hover:bg-primary-dark active:bg-primary-darker text-white focus:ring-brand-secondary shadow-sm',
        'secondary' => 'bg-brand-secondary hover:bg-secondary-dark active:bg-secondary-darker text-slate focus:ring-brand-secondary shadow-sm',
        'tertiary' => 'bg-brand-accent hover:bg-accent-dark active:bg-accent-darker text-slate focus:ring-brand-secondary shadow-sm',
        'outline' => 'bg-white hover:bg-info-bg active:bg-blue-100 text-brand-primary border-2 border-brand-primary focus:ring-brand-secondary',
        'ghost' => 'bg-white hover:bg-info-bg active:bg-blue-100 text-brand-primary focus:ring-brand-secondary',
        'success' => 'bg-green-600 hover:bg-green-700 active:bg-green-800 text-white focus:ring-green-400 shadow-sm',
        'warning' => 'bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white focus:ring-amber-300 shadow-sm',
        'danger' => 'bg-red-600 hover:bg-red-700 active:bg-red-800 text-white focus:ring-red-400 shadow-sm',
    ];
    $variantClass = $variants[$variant] ?? $variants['primary'];

    $classes = trim("{$base} {$sizeClass} {$variantClass}");

    // Icon size
    $iconSize = $size === 'sm' ? 'w-3.5 h-3.5' : 'w-4 h-4';
@endphp

@if ($href && !$disabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon && $iconPosition === 'left')
            <i data-lucide="{{ $icon }}" class="{{ $iconSize }}"></i>
        @endif
        {{ $slot }}
        @if ($icon && $iconPosition === 'right')
            <i data-lucide="{{ $icon }}" class="{{ $iconSize }}"></i>
        @endif
    </a>
@else
    <button type="{{ $type }}" @if($disabled) disabled @endif {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon && $iconPosition === 'left')
            <i data-lucide="{{ $icon }}" class="{{ $iconSize }}"></i>
        @endif
        {{ $slot }}
        @if ($icon && $iconPosition === 'right')
            <i data-lucide="{{ $icon }}" class="{{ $iconSize }}"></i>
        @endif
    </button>
@endif
