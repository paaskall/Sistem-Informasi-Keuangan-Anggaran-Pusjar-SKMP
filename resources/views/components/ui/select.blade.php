@props([
    'name' => '',
    'id' => null,
    'label' => null,
    'options' => [],       // ['value' => 'label'] atau [['id'=>.., 'nama'=>..]] dengan :optionValue / :optionLabel
    'optionValue' => null,
    'optionLabel' => null,
    'value' => '',
    'placeholder' => '- Pilih -',
    'required' => false,
    'disabled' => false,
    'error' => null,
    'helper' => null,
])

@php
    $id = $id ?? $name;
    $hasError = !empty($error);

    $baseSelect = 'w-full h-11 px-3 pr-10 rounded-md text-sm transition-blueprint focus:outline-none appearance-none bg-white cursor-pointer';
    $selectColor = $hasError
        ? 'border-2 border-red-400 focus:border-red-500 focus:ring-2 focus:ring-red-200'
        : 'border border-gray-200 focus:border-brand-secondary focus:ring-2 focus:ring-blue-100';
    $selectDisabled = $disabled ? 'bg-gray-100 cursor-not-allowed text-gray-500' : '';

    $selectClass = trim("{$baseSelect} {$selectColor} {$selectDisabled}");
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1.5">
            {{ $label }}
            @if ($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif

    <div class="relative">
        <select
            name="{{ $name }}"
            id="{{ $id }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            {{ $attributes->merge(['class' => $selectClass]) }}
        >
            @if ($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif

            @foreach ($options as $key => $opt)
                @php
                    if (is_array($opt)) {
                        $optVal = $optionValue ? $opt[$optionValue] : ($opt['id'] ?? $key);
                        $optLabel = $optionLabel ? $opt[$optionLabel] : ($opt['nama'] ?? $opt['name'] ?? $key);
                    } else {
                        $optVal = $key;
                        $optLabel = $opt;
                    }
                @endphp
                <option value="{{ $optVal }}" {{ $value == $optVal ? 'selected' : '' }}>
                    {{ $optLabel }}
                </option>
            @endforeach

            {{ $slot }}
        </select>

        <i data-lucide="chevron-down" class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
    </div>

    @if ($hasError)
        <p class="text-xs text-red-600 mt-1.5 flex items-center gap-1">
            <i data-lucide="alert-circle" class="w-3 h-3"></i>
            {{ $error }}
        </p>
    @elseif ($helper)
        <p class="text-xs text-gray-500 mt-1.5">{{ $helper }}</p>
    @endif
</div>
