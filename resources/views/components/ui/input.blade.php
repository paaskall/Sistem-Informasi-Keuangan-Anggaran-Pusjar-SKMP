@props([
    'name' => '',
    'id' => null,
    'type' => 'text',
    'label' => null,
    'value' => '',
    'placeholder' => '',
    'icon' => null,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'helper' => null,
    'mono' => false,
])

@php
    $id = $id ?? $name;
    $hasError = !empty($error);

    $baseInput = 'w-full h-11 px-3 rounded-md text-body text-slate transition-blueprint focus:outline-none placeholder:text-muted';
    $inputColor = $hasError
        ? 'border-2 border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-200'
        : 'border border-gray-200 focus:border-brand-secondary focus:ring-2 focus:ring-blue-100';
    $inputDisabled = $disabled ? 'bg-gray-100 cursor-not-allowed text-gray-500' : 'bg-white';
    $inputPadding = $icon ? 'pl-10' : 'pl-3';
    $inputFont = $mono ? 'font-mono' : '';

    $inputClass = trim("{$baseInput} {$inputColor} {$inputDisabled} {$inputPadding} {$inputFont}");
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="block text-label text-gray-700 font-semibold mb-1.5">
            {{ $label }}
            @if ($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif

    <div class="relative">
        @if ($icon)
            <i data-lucide="{{ $icon }}" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
        @endif

        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $id }}"
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            {{ $attributes->merge(['class' => $inputClass]) }}
        />

        @if ($hasError)
            <i data-lucide="alert-circle" class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-red-500 pointer-events-none"></i>
        @endif
    </div>

    @if ($hasError)
        <p class="text-caption text-red-600 font-medium mt-1.5 flex items-center gap-1">
            <i data-lucide="alert-circle" class="w-3 h-3"></i>
            {{ $error }}
        </p>
    @elseif ($helper)
        <p class="text-caption text-muted font-medium mt-1.5">{{ $helper }}</p>
    @endif
</div>
