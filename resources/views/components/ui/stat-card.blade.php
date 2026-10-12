@props([
    'label' => '',
    'value' => '',
    'icon' => 'activity',
    'color' => 'primary',   // primary | success | warning | error | accent
    'trend' => null,        // string: "+5%" atau "-3%"
    'trendUp' => true,
    'href' => null,
    'subtitle' => null,
])

@php
    $colors = [
        'primary' => ['bg-blue-50', 'text-brand-primary'],
        'success' => ['bg-green-50', 'text-green-700'],
        'warning' => ['bg-amber-50', 'text-amber-700'],
        'error' => ['bg-red-50', 'text-red-700'],
        'accent' => ['bg-yellow-50', 'text-yellow-700'],
    ];
    $c = $colors[$color] ?? $colors['primary'];
    
    $tag = $href ? 'a' : 'div';
    $hrefAttr = $href ? "href=\"{$href}\"" : '';
@endphp

<{{ $tag }} {!! $hrefAttr !!} class="block bg-white rounded-lg border border-gray-100 shadow-soft p-6 transition-blueprint hover:shadow-medium group {{ $href ? 'cursor-pointer' : '' }}">
    <div class="flex items-start justify-between gap-3 mb-3">
        <p class="text-xs text-gray-500 uppercase font-semibold tracking-wider">{{ $label }}</p>
        <div class="w-10 h-10 rounded-lg {{ $c[0] }} {{ $c[1] }} flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition">
            <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
        </div>
    </div>

    <p class="text-2xl font-bold text-gray-900">{{ $value }}</p>

    @if ($trend)
        <div class="flex items-center gap-1 mt-2 text-xs font-semibold {{ $trendUp ? 'text-green-700' : 'text-red-700' }}">
            <i data-lucide="{{ $trendUp ? 'trending-up' : 'trending-down' }}" class="w-3 h-3"></i>
            {{ $trend }}
        </div>
    @endif

    @if ($subtitle)
        <p class="text-xs text-gray-500 mt-1">{{ $subtitle }}</p>
    @endif

    {{ $slot }}
</{{ $tag }}>
