@extends('layouts.app')

@section('title', 'SIKAP - Kelola User')

@section('content')
<div class="flex flex-wrap items-start justify-between gap-3 mb-6">
    <x-ui.page-title
        title="Kelola User"
        subtitle="Manajemen akun pengguna sistem"
        :breadcrumb="[
            ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
            ['label' => 'Kelola User'],
        ]"
    />
    <x-ui.button variant="primary" icon="user-plus" onclick="openUserForm()">
        Tambah User
    </x-ui.button>
</div>

<x-ui.card padding="p-0">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 text-gray-500">
                <tr>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase">User</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase">Username</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase">Role</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase">Unit</th>
                    <th class="text-left px-4 py-3 text-caption font-semibold uppercase">Status</th>
                    <th class="text-right px-4 py-3 text-caption font-semibold uppercase w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-label" id="user-table-body">
                <tr><td colspan="6" class="text-center py-8 text-muted">Memuat...</td></tr>
            </tbody>
        </table>
    </div>
</x-ui.card>

<script>
window.USER_DATA = {
    routes: {
        data:    @json(route('admin.users.data')),
        store:   @json(route('admin.users.store')),
        update:  @json(route('admin.users.update', ['id' => '__ID__'])),
        toggle:  @json(route('admin.users.toggle', ['id' => '__ID__'])),
        destroy: @json(route('admin.users.destroy', ['id' => '__ID__'])),
    },
    references: {
        units: @json(\App\Models\Unit::orderBy('nama')->pluck('nama')),
    },
    csrf: @json(csrf_token()),
};
</script>
@endsection

@push('scripts')
<script src="{{ asset('js/pages/users.js') }}?v={{ filemtime(public_path('js/pages/users.js')) }}"></script>
@endpush