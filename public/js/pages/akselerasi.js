// ============================================================
// SIKAP — Akselerasi Penyerapan Anggaran (CRUD)
// Versi aman XSS
// ============================================================

(function () {
    const DATA = window.AKSELERASI_DATA;
    let items = [];

    function buildRoute(template, id) {
        return template.replace('__ID__', id);
    }

    async function apiCall(method, url, body) {
        const opts = {
            method,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': DATA.csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
        };
        if (body) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
        const res = await fetch(url, opts);
        const json = await res.json().catch(() => ({}));
        return { ok: res.ok, status: res.status, data: json };
    }

    async function loadData() {
        const res = await apiCall('GET', DATA.routes.data);
        if (!res.ok) {
            toastError('Gagal memuat data');
            return;
        }
        items = res.data.items || [];
        renderTable();
    }

    function renderTable() {
        const tbody = document.getElementById('akselerasi-tbody');
        if (!tbody) return;

        if (items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-10 text-muted">
                        <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                        <p class="text-sm">Belum ada kegiatan akselerasi</p>
                        <p class="text-xs mt-1">Klik "Tambah Kegiatan" untuk memulai</p>
                    </td>
                </tr>`;
            if (window.lucide) lucide.createIcons();
            return;
        }

        tbody.innerHTML = items.map((item, idx) => {
            const pct = item.progress;
            const pctColor = pct >= 80 ? 'text-green-700 bg-green-100'
                            : pct >= 60 ? 'text-blue-700 bg-blue-100'
                            : pct >= 40 ? 'text-amber-700 bg-amber-100'
                            : 'text-red-700 bg-red-100';
            const barColor = pct >= 80 ? 'from-green-600 to-green-400'
                            : pct >= 60 ? 'from-blue-600 to-blue-400'
                            : pct >= 40 ? 'from-amber-500 to-amber-400'
                            : 'from-red-500 to-red-400';

            return `
                <tr class="hover:bg-blue-50/40 transition">
                    <td class="px-4 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600 text-label font-bold">
                            ${idx + 1}
                        </span>
                    </td>
                    <td class="px-4 py-4">
                        <p class="font-semibold text-slate text-label">${escapeHtml(item.kegiatan)}</p>
                    </td>
                    <td class="px-4 py-4">
                        <span class="text-caption text-gray-600">${escapeHtml(item.jangka_waktu)}</span>
                    </td>
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden min-w-[60px]">
                                <div class="bg-gradient-to-r ${barColor} h-full rounded-full" style="width: ${pct}%"></div>
                            </div>
                            <span class="text-caption font-bold px-2 py-0.5 rounded ${pctColor}">${pct}%</span>
                        </div>
                    </td>
                    <td class="px-4 py-4">
                        <span class="text-caption text-gray-600">${escapeHtml(item.keterangan || '-')}</span>
                    </td>
                    <td class="px-4 py-4 text-right">
                        <button type="button" onclick="editAkselerasi(${item.id})"
                            class="text-brand-primary hover:bg-blue-100 p-2 rounded-lg transition-blueprint" title="Edit">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </button>
                        <button type="button" onclick="deleteAkselerasi(${item.id})"
                            class="text-red-600 hover:bg-red-100 p-2 rounded-lg transition-blueprint ml-1" title="Hapus">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </td>
                </tr>`;
        }).join('');

        if (window.lucide) lucide.createIcons();
    }

    // ============ FORM ============
    function buildForm(item) {
        return `
            <div class="text-left space-y-3 text-sm mt-2">
                <div>
                    <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">
                        Kegiatan <span class="text-red-500">*</span>
                    </label>
                    <input id="f-kegiatan" type="text"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                        value="${item ? escapeHtml(item.kegiatan) : ''}"
                        placeholder="Contoh: Percepatan penyelesaian dokumen..." />
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">
                        Jangka Waktu <span class="text-red-500">*</span>
                    </label>
                    <input id="f-jangka" type="text"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                        value="${item ? escapeHtml(item.jangka_waktu) : ''}"
                        placeholder="Contoh: Oktober 2026" />
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">
                        Progress (%) <span class="text-red-500">*</span>
                    </label>
                    <input id="f-progress" type="number" min="0" max="100"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                        value="${item ? escapeHtml(item.progress) : 0}" />
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">
                        Keterangan
                    </label>
                    <textarea id="f-keterangan" rows="3"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                        placeholder="Tindak lanjut / catatan...">${item ? escapeHtml(item.keterangan || '') : ''}</textarea>
                </div>
            </div>
        `;
    }

    function collectForm() {
        return {
            kegiatan: document.getElementById('f-kegiatan').value.trim(),
            jangka_waktu: document.getElementById('f-jangka').value.trim(),
            progress: Number(document.getElementById('f-progress').value) || 0,
            keterangan: document.getElementById('f-keterangan').value.trim() || null,
        };
    }

    window.openAkselerasiForm = function () {
        Swal.fire({
            title: 'Tambah Kegiatan Akselerasi',
            width: 560,
            html: buildForm(null),
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-brand-primary hover:bg-primary-dark text-white font-semibold px-5 py-2.5 rounded-lg mx-1',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1',
                popup: 'rounded-2xl',
            },
            buttonsStyling: false,
            didOpen: () => { if (window.lucide) lucide.createIcons(); },
            preConfirm: () => {
                const d = collectForm();
                if (!d.kegiatan || !d.jangka_waktu) {
                    Swal.showValidationMessage('Kegiatan dan jangka waktu wajib diisi');
                    return false;
                }
                if (d.progress < 0 || d.progress > 100) {
                    Swal.showValidationMessage('Progress harus 0-100');
                    return false;
                }
                return d;
            }
        }).then(async (r) => {
            if (!r.isConfirmed) return;
            const res = await apiCall('POST', DATA.routes.store, r.value);
            if (res.ok) {
                toastSuccess(res.data.message);
                loadData();
            } else {
                const errors = res.data.errors || {};
                const first = Object.values(errors)[0];
                toastError(Array.isArray(first) ? first[0] : (res.data.message || 'Gagal menyimpan'));
            }
        });
    };

    window.editAkselerasi = function (id) {
        const item = items.find(x => x.id === id);
        if (!item) return;

        Swal.fire({
            title: 'Edit Kegiatan Akselerasi',
            width: 560,
            html: buildForm(item),
            showCancelButton: true,
            confirmButtonText: 'Update',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-brand-primary hover:bg-primary-dark text-white font-semibold px-5 py-2.5 rounded-lg mx-1',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1',
                popup: 'rounded-2xl',
            },
            buttonsStyling: false,
            didOpen: () => { if (window.lucide) lucide.createIcons(); },
            preConfirm: () => {
                const d = collectForm();
                if (!d.kegiatan || !d.jangka_waktu) {
                    Swal.showValidationMessage('Kegiatan dan jangka waktu wajib diisi');
                    return false;
                }
                if (d.progress < 0 || d.progress > 100) {
                    Swal.showValidationMessage('Progress harus 0-100');
                    return false;
                }
                return d;
            }
        }).then(async (r) => {
            if (!r.isConfirmed) return;
            const url = buildRoute(DATA.routes.update, id);
            const res = await apiCall('PUT', url, r.value);
            if (res.ok) {
                toastSuccess(res.data.message);
                loadData();
            } else {
                const errors = res.data.errors || {};
                const first = Object.values(errors)[0];
                toastError(Array.isArray(first) ? first[0] : (res.data.message || 'Gagal menyimpan'));
            }
        });
    };

    window.deleteAkselerasi = function (id) {
        const item = items.find(x => x.id === id);
        if (!item) return;

        Swal.fire({
            title: 'Hapus Kegiatan?',
            html: `<div class="text-sm text-gray-600">
                <p><b>${escapeHtml(item.kegiatan)}</b> akan dihapus.</p>
            </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-medium px-5 py-2.5 rounded-lg mx-1',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1',
            },
            buttonsStyling: false,
        }).then(async (r) => {
            if (!r.isConfirmed) return;
            const url = buildRoute(DATA.routes.destroy, id);
            const res = await apiCall('DELETE', url);
            if (res.ok) {
                toastSuccess(res.data.message);
                loadData();
            } else {
                toastError(res.data.message || 'Gagal menghapus');
            }
        });
    };

    document.addEventListener('DOMContentLoaded', loadData);
})();