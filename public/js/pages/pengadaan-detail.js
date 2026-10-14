// ============================================================
// SIKAP - Pengadaan Detail
// Versi aman XSS — semua data di-escape
// ============================================================

(function () {
    const DATA = window.PENGADAAN_DETAIL_DATA;
    let pengadaan = null;

    function buildRoute(template, id, key = '__ID__') {
        return template.replace(key, id);
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

    function rp(n) {
        if (!n && n !== 0) return 'Rp 0';
        return 'Rp' + Number(n).toLocaleString('id-ID');
    }

    function formatTanggal(s) {
        if (!s) return '-';
        const d = new Date(s);
        if (isNaN(d)) return s;
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function progressColor(p) {
        if (p === 0) return 'fill-0';
        if (p >= 100) return 'fill-done';
        if (p >= 70) return 'fill-high';
        if (p >= 40) return 'fill-mid';
        return 'fill-low';
    }

    function statusBadge(s) {
        const map = {
            'Draft': 'bg-gray-100 text-gray-700',
            'Proses': 'bg-blue-100 text-blue-700',
            'Selesai': 'bg-green-100 text-green-700',
            'Batal': 'bg-red-100 text-red-700',
        };
        const cls = map[s] || 'bg-gray-100 text-gray-700';
        return `<span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold ${cls}">${escapeHtml(s)}</span>`;
    }

    async function loadData() {
        const url = buildRoute(DATA.routes.data, DATA.pengadaanId);
        const res = await apiCall('GET', url);

        if (!res.ok) {
            toastError('Gagal memuat data pengadaan');
            return;
        }

        pengadaan = res.data.pengadaan;
        renderHeader();
        renderProgress();
        renderTahapan();
    }

    function renderHeader() {
        const p = pengadaan;
        const container = document.getElementById('pengadaan-header');

        container.innerHTML = `
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-3 flex-wrap mb-2">
                            <span class="font-mono text-xs font-bold bg-blue-50 text-blue-700 px-3 py-1 rounded-lg">
                                ${escapeHtml(p.kode)}
                            </span>
                            ${statusBadge(p.status)}
                        </div>
                        <h1 class="text-2xl font-bold text-gray-800 mb-2">${escapeHtml(p.nama)}</h1>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-2 text-sm mt-4">
                            <div class="flex items-center gap-2 text-gray-600">
                                <i data-lucide="tag" class="w-4 h-4 text-gray-400"></i>
                                <span class="text-xs text-gray-500">Jenis:</span>
                                <b class="text-gray-800">${escapeHtml(p.jenis)}</b>
                            </div>
                            <div class="flex items-center gap-2 text-gray-600">
                                <i data-lucide="banknote" class="w-4 h-4 text-gray-400"></i>
                                <span class="text-xs text-gray-500">Nilai:</span>
                                <b class="text-blue-700 font-semibold">${rp(p.nilai)}</b>
                            </div>
                            <div class="flex items-center gap-2 text-gray-600">
                                <i data-lucide="building" class="w-4 h-4 text-gray-400"></i>
                                <span class="text-xs text-gray-500">Penyedia:</span>
                                <b class="text-gray-800">${escapeHtml(p.penyedia || '-')}</b>
                            </div>
                            <div class="flex items-center gap-2 text-gray-600">
                                <i data-lucide="calendar" class="w-4 h-4 text-gray-400"></i>
                                <span class="text-xs text-gray-500">Periode:</span>
                                <b class="text-gray-800">${escapeHtml(formatTanggal(p.tanggal_mulai))} - ${escapeHtml(formatTanggal(p.tanggal_selesai))}</b>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        if (window.lucide) lucide.createIcons();
    }

    function renderProgress() {
        const p = pengadaan;
        const container = document.getElementById('progress-card');

        container.innerHTML = `
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-5 text-white shadow-lg shadow-blue-200/50">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center">
                            <i data-lucide="trending-up" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="text-xs text-blue-100 uppercase tracking-wider font-semibold">Progress Pengadaan</p>
                            <p class="text-3xl font-bold">${p.progress}%</p>
                        </div>
                    </div>
                    <div class="flex gap-6 text-sm">
                        <div>
                            <p class="text-blue-100 text-xs mb-1">Tahapan Selesai</p>
                            <p class="font-bold">${p.jumlah_selesai} / ${p.jumlah_tahapan}</p>
                        </div>
                        <div>
                            <p class="text-blue-100 text-xs mb-1">Sisa</p>
                            <p class="font-bold">${p.jumlah_tahapan - p.jumlah_selesai} tahapan</p>
                        </div>
                    </div>
                </div>
                <div class="mt-4 bg-white/20 rounded-full h-3 overflow-hidden">
                    <div class="bg-white h-full rounded-full transition-all duration-700"
                         style="width: ${p.progress}%"></div>
                </div>
            </div>
        `;

        if (window.lucide) lucide.createIcons();
    }

    function renderTahapan() {
        const container = document.getElementById('tahapan-list');
        const list = pengadaan.tahapans || [];

        if (list.length === 0) {
            container.innerHTML = `
                <div class="text-center py-10">
                    <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 text-gray-300"></i>
                    <p class="text-sm text-gray-500 mb-1">Belum ada tahapan</p>
                    <p class="text-xs text-gray-400">Klik "Tambah Tahapan" untuk memulai</p>
                </div>
            `;
            if (window.lucide) lucide.createIcons();
            return;
        }

        container.innerHTML = list.map((t, i) => `
            <div class="tahapan-row ${t.selesai ? 'selesai' : ''} p-4" data-tahapan-id="${t.id}">
                <div class="flex items-start gap-3">
                    <input type="checkbox" class="tahapan-checkbox mt-1"
                        ${t.selesai ? 'checked' : ''}
                        onchange="toggleTahapan(${t.id}, this.checked)" />

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3 mb-1">
                            <div class="min-w-0 flex-1">
                                <p class="tahapan-nama font-semibold text-gray-800">
                                    <span class="text-xs text-gray-400 font-mono mr-1">${i + 1}.</span>
                                    ${escapeHtml(t.nama)}
                                </p>
                                ${t.selesai && t.tanggal_selesai ? `
                                    <p class="text-[11px] text-green-700 flex items-center gap-1 mt-1">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i>
                                        Selesai: ${escapeHtml(formatTanggal(t.tanggal_selesai))}
                                    </p>
                                ` : ''}
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button type="button" onclick="reorderTahapan(${t.id}, 'up')"
                                    class="text-gray-400 hover:text-blue-700 hover:bg-blue-50 p-1.5 rounded transition"
                                    title="Naikkan" ${i === 0 ? 'disabled' : ''}>
                                    <i data-lucide="arrow-up" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" onclick="reorderTahapan(${t.id}, 'down')"
                                    class="text-gray-400 hover:text-blue-700 hover:bg-blue-50 p-1.5 rounded transition"
                                    title="Turunkan" ${i === list.length - 1 ? 'disabled' : ''}>
                                    <i data-lucide="arrow-down" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" onclick="editTahapan(${t.id})"
                                    class="text-blue-700 hover:bg-blue-100 p-1.5 rounded transition" title="Edit">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                </button>
                                <button type="button" onclick="deleteTahapan(${t.id})"
                                    class="text-red-600 hover:bg-red-100 p-1.5 rounded transition" title="Hapus">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        </div>

                        ${t.kendala ? `
                            <div class="mt-2 p-2.5 rounded-lg border ${t.kendala_selesai ? 'bg-green-50 border-green-200' : 'bg-amber-50 border-amber-200'}">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex items-start gap-2 flex-1 min-w-0">
                                        <i data-lucide="${t.kendala_selesai ? 'check-circle' : 'alert-triangle'}" class="w-3.5 h-3.5 flex-shrink-0 mt-0.5 ${t.kendala_selesai ? 'text-green-600' : 'text-amber-600'}"></i>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[11px] font-semibold ${t.kendala_selesai ? 'text-green-900' : 'text-amber-900'}">
                                                ${t.kendala_selesai ? 'Kendala Sudah Diatasi' : 'Kendala'}
                                            </p>
                                            <p class="text-[11px] ${t.kendala_selesai ? 'text-green-800 line-through' : 'text-amber-800'} mt-0.5">
                                                ${escapeHtml(t.kendala)}
                                            </p>
                                            ${t.kendala_selesai && t.kendala_selesai_at ? `
                                                <p class="text-[10px] text-green-700 mt-1 flex items-center gap-1">
                                                    <i data-lucide="calendar-check" class="w-3 h-3"></i>
                                                    Diatasi: ${escapeHtml(formatTanggal(t.kendala_selesai_at))}
                                                </p>
                                            ` : ''}
                                        </div>
                                    </div>
                                    <button type="button"
                                        onclick="event.stopPropagation(); toggleKendala(${t.id})"
                                        class="flex-shrink-0 text-[10px] font-bold px-2 py-1 rounded-md transition ${t.kendala_selesai ? 'bg-white text-amber-700 hover:bg-amber-50 border border-amber-200' : 'bg-white text-green-700 hover:bg-green-50 border border-green-200'}"
                                        title="${t.kendala_selesai ? 'Batal tandai' : 'Tandai sudah diatasi'}">
                                        ${t.kendala_selesai ? '↺ Aktifkan' : 'Diatasi'}
                                    </button>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `).join('');

        if (window.lucide) lucide.createIcons();
    }

    // ============================================================
    // TAHAPAN ACTIONS
    // ============================================================

    window.toggleTahapan = async function (tahapanId, checked) {
        const url = buildRoute(DATA.routes.toggleTahapan, tahapanId, '__TID__');
        const res = await apiCall('PATCH', url);

        if (res.ok) {
            pengadaan = res.data.pengadaan;
            toastSuccess(res.data.message);
            renderProgress();
            renderTahapan();
        } else {
            toastError(res.data.message || 'Gagal toggle tahapan');
            loadData();
        }
    };

    window.reorderTahapan = async function (tahapanId, direction) {
        const url = buildRoute(DATA.routes.reorderTahapan, tahapanId, '__TID__');
        const res = await apiCall('PATCH', url, { direction });

        if (res.ok) {
            pengadaan = res.data.pengadaan;
            renderTahapan();
        } else {
            toastError(res.data.message || 'Gagal mengubah urutan');
        }
    };

    window.deleteTahapan = function (tahapanId) {
        const t = pengadaan.tahapans.find(x => x.id === tahapanId);
        if (!t) return;

        Swal.fire({
            title: 'Hapus Tahapan?',
            html: `<div class="text-sm text-gray-600 text-left">
                <p><b>${escapeHtml(t.nama)}</b> akan dihapus permanen.</p>
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
            const url = buildRoute(DATA.routes.destroyTahapan, tahapanId, '__TID__');
            const res = await apiCall('DELETE', url);
            if (res.ok) {
                pengadaan = res.data.pengadaan;
                toastSuccess(res.data.message);
                renderProgress();
                renderTahapan();
            } else {
                toastError(res.data.message || 'Gagal menghapus');
            }
        });
    };

    // ============================================================
    // FORM TAHAPAN
    // ============================================================

    function buildTahapanForm(t) {
        return `<div class="text-left space-y-3 text-sm mt-2">
            <div>
                <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Nama Tahapan <span class="text-red-500">*</span></label>
                <input id="f-tahapan-nama" type="text" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                    value="${t ? escapeHtml(t.nama) : ''}" placeholder="Contoh: Perencanaan" />
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Kendala (opsional)</label>
                <textarea id="f-tahapan-kendala" rows="2" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                    placeholder="Contoh: Vendor tidak memenuhi syarat">${t && t.kendala ? escapeHtml(t.kendala) : ''}</textarea>
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Dampak Potensial (opsional)</label>
                <textarea id="f-tahapan-dampak" rows="2" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                    placeholder="Contoh: Jadwal pengadaan mundur">${t && t.dampak ? escapeHtml(t.dampak) : ''}</textarea>
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Tindak Lanjut / Mitigasi (opsional)</label>
                <textarea id="f-tahapan-tindak" rows="2" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                    placeholder="Contoh: Percepat pengumuman ulang">${t && t.tindak_lanjut ? escapeHtml(t.tindak_lanjut) : ''}</textarea>
            </div>
            ${t ? `
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="checkbox" id="f-tahapan-selesai" ${t.selesai ? 'checked' : ''}
                            class="mt-0.5 w-4 h-4 rounded border-gray-300 text-green-600" />
                        <div>
                            <p class="text-sm font-semibold text-gray-800">Tandai tahapan sebagai selesai</p>
                            <p class="text-xs text-gray-500 mt-0.5">Tanggal selesai otomatis diisi hari ini.</p>
                        </div>
                    </label>
                </div>
            ` : ''}
        </div>`;
    }

    function collectTahapanForm() {
        const data = {
            nama: document.getElementById('f-tahapan-nama').value.trim(),
            kendala: document.getElementById('f-tahapan-kendala').value.trim() || null,
            dampak: document.getElementById('f-tahapan-dampak').value.trim() || null,
            tindak_lanjut: document.getElementById('f-tahapan-tindak').value.trim() || null,
        };
        const selesaiEl = document.getElementById('f-tahapan-selesai');
        if (selesaiEl) {
            data.selesai = selesaiEl.checked;
        }
        return data;
    }

    window.openTahapanForm = function () {
        Swal.fire({
            title: 'Tambah Tahapan',
            width: 520,
            html: buildTahapanForm(null),
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-blue-700 hover:bg-blue-800 text-white font-medium px-5 py-2.5 rounded-lg mx-1',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1',
                popup: 'rounded-2xl',
            },
            buttonsStyling: false,
            didOpen: () => { if (window.lucide) lucide.createIcons(); },
            preConfirm: () => {
                const data = collectTahapanForm();
                if (!data.nama) {
                    Swal.showValidationMessage('Nama tahapan wajib diisi');
                    return false;
                }
                return data;
            }
        }).then(async (r) => {
            if (!r.isConfirmed) return;
            const url = buildRoute(DATA.routes.storeTahapan, DATA.pengadaanId);
            const res = await apiCall('POST', url, r.value);
            if (res.ok) {
                pengadaan = res.data.pengadaan;
                toastSuccess(res.data.message);
                renderProgress();
                renderTahapan();
            } else {
                const errors = res.data.errors || {};
                const first = Object.values(errors)[0];
                toastError(Array.isArray(first) ? first[0] : (res.data.message || 'Gagal menyimpan'));
            }
        });
    };

    window.editTahapan = function (tahapanId) {
        const t = pengadaan.tahapans.find(x => x.id === tahapanId);
        if (!t) return;

        Swal.fire({
            title: 'Edit Tahapan',
            width: 520,
            html: buildTahapanForm(t),
            showCancelButton: true,
            confirmButtonText: 'Update',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-blue-700 hover:bg-blue-800 text-white font-medium px-5 py-2.5 rounded-lg mx-1',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1',
                popup: 'rounded-2xl',
            },
            buttonsStyling: false,
            didOpen: () => { if (window.lucide) lucide.createIcons(); },
            preConfirm: () => {
                const data = collectTahapanForm();
                if (!data.nama) {
                    Swal.showValidationMessage('Nama tahapan wajib diisi');
                    return false;
                }
                return data;
            }
        }).then(async (r) => {
            if (!r.isConfirmed) return;
            const url = buildRoute(DATA.routes.updateTahapan, tahapanId, '__TID__');
            const res = await apiCall('PUT', url, r.value);
            if (res.ok) {
                pengadaan = res.data.pengadaan;
                toastSuccess(res.data.message);
                renderProgress();
                renderTahapan();
            } else {
                const errors = res.data.errors || {};
                const first = Object.values(errors)[0];
                toastError(Array.isArray(first) ? first[0] : (res.data.message || 'Gagal menyimpan'));
            }
        });
    };

    window.toggleKendala = async function (tahapanId) {
        const url = buildRoute(DATA.routes.toggleKendala, tahapanId, '__TID__');
        const res = await apiCall('PATCH', url);

        if (res.ok) {
            pengadaan = res.data.pengadaan;
            toastSuccess(res.data.message);
            renderTahapan();
        } else {
            toastError(res.data.message || 'Gagal toggle kendala');
        }
    };

    document.addEventListener('DOMContentLoaded', loadData);
})();