@props(['items' => []])
{{-- items: [['label' => 'Dashboard', 'url' => '/admin'], ['label' => 'Users']] --}}

<nav aria-label="Breadcrumb" class="mb-4">
    <ol class="flex items-center gap-2 text-sm flex-wrap">
        @foreach ($items as $i => $item)
            @if ($i > 0)
                <li aria-hidden="true">
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400"></i>
                </li>
            @endif
            <li>
                @if (!empty($item['url']) && $i < count($items) - 1)
                    <a href="{{ $item['url'] }}" class="text-brand-primary hover:underline font-medium">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="text-gray-700 font-semibold" aria-current="page">
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
