@props([
    'for' => null,
    'required' => false,
    'uppercase' => false,
])

<label
    @if($for) for="{{ $for }}" @endif
    {{ $attributes->merge([
        'class' => 'block text-label text-gray-700 font-semibold mb-1.5 ' . ($uppercase ? 'uppercase tracking-wide text-xs' : ''),
    ]) }}
>
    {{ $slot }}
    @if ($required)
        <span class="text-red-500 ml-0.5">*</span>
    @endif
</label>
