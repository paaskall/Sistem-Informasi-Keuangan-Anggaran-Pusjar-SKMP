@props([
    'title' => '',
    'subtitle' => null,
    'breadcrumb' => null,
    'accent' => 'primary',
])

@php
    $accents = [
        'primary' => 'from-brand-primary to-brand-secondary',
        'success' => 'from-green-600 to-green-400',
        'warning' => 'from-amber-500 to-amber-400',
        'error' => 'from-red-600 to-red-400',
    ];
    $accentClass = $accents[$accent] ?? $accents['primary'];
@endphp

<div class="mb-6">
    @if ($breadcrumb)
        <x-ui.breadcrumb :items="$breadcrumb" class="mb-3" />
    @endif

    <div class="flex items-start gap-3">
        {{-- Accent bar --}}
        <div class="w-1 h-10 bg-gradient-to-b {{ $accentClass }} rounded-full flex-shrink-0 mt-1"></div>

        <div class="min-w-0 flex-1">
            <h1 class="text-h5 text-slate font-semibold leading-tight">
                {{ $title }}
            </h1>
            @if ($subtitle)
                <p class="text-label text-muted font-normal mt-1">
                    {{ $subtitle }}
                </p>
            @endif
            {{ $slot }}
        </div>
    </div>
</div>
