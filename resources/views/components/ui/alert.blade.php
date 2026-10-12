@props([
    'variant' => 'info',    // info | success | warning | error
    'title' => null,
    'message' => null,
    'icon' => null,
    'dismissible' => false,
    'action' => null,
    'actionUrl' => null,
])

@php
    $variants = [
        'info' => [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-200',
            'icon' => 'info',
            'iconColor' => 'text-brand-primary',
            'titleColor' => 'text-brand-primary',
        ],
        'success' => [
            'bg' => 'bg-green-50',
            'border' => 'border-green-200',
            'icon' => 'check-circle',
            'iconColor' => 'text-green-700',
            'titleColor' => 'text-green-800',
        ],
        'warning' => [
            'bg' => 'bg-amber-50',
            'border' => 'border-amber-200',
            'icon' => 'alert-triangle',
            'iconColor' => 'text-amber-700',
            'titleColor' => 'text-amber-800',
        ],
        'error' => [
            'bg' => 'bg-red-50',
            'border' => 'border-red-200',
            'icon' => 'x-circle',
            'iconColor' => 'text-red-700',
            'titleColor' => 'text-red-800',
        ],
    ];
    $v = $variants[$variant] ?? $variants['info'];
    $icon = $icon ?? $v['icon'];
@endphp

<div class="flex items-start gap-3 p-4 rounded-md {{ $v['bg'] }} {{ $v['border'] }} border">
    <div class="w-6 h-6 flex items-center justify-center flex-shrink-0 mt-0.5">
        <i data-lucide="{{ $icon }}" class="w-5 h-5 {{ $v['iconColor'] }}"></i>
    </div>

    <div class="flex-1 min-w-0">
        @if ($title)
            <p class="font-bold {{ $v['titleColor'] }} text-sm">{{ $title }}</p>
        @endif
        @if ($message)
            <p class="text-xs text-gray-700 mt-0.5 leading-relaxed">{{ $message }}</p>
        @endif
        {{ $slot }}

        @if ($action && $actionUrl)
            <a href="{{ $actionUrl }}" class="inline-flex items-center gap-1 mt-2 text-xs font-semibold {{ $v['titleColor'] }} hover:underline">
                {{ $action }} <i data-lucide="arrow-right" class="w-3 h-3"></i>
            </a>
        @endif
    </div>

    @if ($dismissible)
        <button type="button" onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    @endif
</div>
