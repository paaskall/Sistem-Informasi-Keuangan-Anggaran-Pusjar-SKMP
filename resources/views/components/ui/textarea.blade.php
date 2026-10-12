@props([
    'name' => '',
    'id' => null,
    'label' => null,
    'value' => '',
    'placeholder' => '',
    'rows' => 4,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'helper' => null,
])

@php
    $id = $id ?? $name;
    $hasError = !empty($error);

    $base = 'w-full px-3 py-2.5 rounded-md text-sm transition-blueprint focus:outline-none resize-y';
    $color = $hasError
        ? 'border-2 border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-200'
        : 'border border-gray-200 focus:border-brand-secondary focus:ring-2 focus:ring-blue-100';
    $disabledClass = $disabled ? 'bg-gray-100 cursor-not-allowed text-gray-500' : 'bg-white';

    $class = trim("{$base} {$color} {$disabledClass}");
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">
            {{ $label }}
            @if ($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $id }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->merge(['class' => $class]) }}
    >{{ $value }}</textarea>

    @if ($hasError)
        <p class="text-xs text-red-600 mt-1.5">{{ $error }}</p>
    @elseif ($helper)
        <p class="text-xs text-gray-500 mt-1.5">{{ $helper }}</p>
    @endif
</div>
