@extends('layouts.app')

@section('title', 'SIKAP - Master Data')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3 mb-6">
    <x-ui.page-title
        title="Master Data"
        subtitle="Kelola data referensi sistem"
        :breadcrumb="[
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Master Data'],
        ]"
    />
    <x-ui.button variant="primary" icon="plus" onclick="openForm()">
        <span id="btn-add-label">Tambah</span>
    </x-ui.button>
</div>

<x-ui.card padding="p-0">
    {{-- TAB NAVIGATION --}}
    <div class="border-b border-gray-100 overflow-x-auto">
        <div class="flex" id="tab-nav">
            @foreach ($tabConfig as $key => $conf)
                @php $count = $references[$key] ? count($references[$key]) : 0; @endphp
                <button type="button"
                    data-tab="{{ $key }}"
                    onclick="switchTab('{{ $key }}')"
                    class="tab-btn px-4 py-3 text-label font-semibold whitespace-nowrap border-b-2 transition-blueprint flex items-center gap-2
                        {{ $key === $activeTab
                            ? 'border-brand-primary text-brand-primary bg-blue-50/50'
                            : 'border-transparent text-muted hover:text-brand-primary hover:bg-blue-50/30' }}">
                    <i data-lucide="{{ $conf['icon'] }}" class="w-4 h-4"></i>
                    {{ $conf['label'] }}
                    <span class="tab-count text-caption px-2 py-0.5 rounded-full font-bold
                        {{ $key === $activeTab ? 'bg-blue-100 text-brand-primary' : 'bg-gray-100 text-muted' }}">
                        {{ $count }}
                    </span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-gray-500">
                <tr id="table-head">
                    {{-- Diisi via JS --}}
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-label" id="table-body">
                {{-- Diisi via JS --}}
            </tbody>
        </table>
    </div>
</x-ui.card>

<script>
    window.MASTER_DATA = {
        activeTab: @json($activeTab),
        tabConfig: @json($tabConfig),
        items: @json($items),
        references: @json($references),
        routes: {
            items:   @json(route('admin.master.items', ['tab' => '__TAB__'])),
            store:   @json(route('admin.master.store', ['tab' => '__TAB__'])),
            update:  @json(route('admin.master.update', ['tab' => '__TAB__', 'id' => '__ID__'])),
            destroy: @json(route('admin.master.destroy', ['tab' => '__TAB__', 'id' => '__ID__'])),
        },
        csrf: @json(csrf_token()),
    };
</script>
@endsection

@push('scripts')
<script src="{{ asset('js/pages/master.js') }}?v={{ filemtime(public_path('js/pages/master.js')) }}"></script>
@endpush