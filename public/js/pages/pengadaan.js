// ============================================================
// SIKAP - Pengadaan List (Blueprint)
// Versi aman XSS
// ============================================================

(function () {
    const DATA = window.PENGADAAN_DATA;
    let pengadaans = [];
    let jenisList = [];
    let templateTahapan = [];
    let nextKode = '';

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

    function rp(n) {
        if (!n && n !== 0) return 'Rp 0';
        return 'Rp' + Number(n).toLocaleString('id-ID');
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
            'Proses': 'bg-blue-100 text-brand-primary',
            'Selesai': 'bg-green-100 text-green-700',
            'Batal': 'bg-red-100 text-red-700',
        };
        const cls = map[s] || 'bg-gray-100 text-gray-700';
        return `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-caption font-semibold ${cls}">${escapeHtml(s)}</span>`;
    }

    async function loadData() {
        const tahun = document.getElementById('filter-tahun').value;
        const url = DATA.routes.data + (tahun ? '?tahun=' + tahun : '');

        const res = await apiCall('GET', url);
        if (!res.ok) {
            toastError('Gagal memuat data pengadaan');
            return;
        }

        pengadaans = res.data.pengadaans || [];
        jenisList = res.data.jenis_list || [];
        templateTahapan = res.data.template_tahapan || [];
        nextKode = res.data.next_kode || '';

        renderStats(res.data.stats);
        renderTable();
    }

    function renderStats(stats) {
        const statCardsEl = document.getElementById('stat-cards');
        if (statCardsEl) {
            statCardsEl.innerHTML = `
                ${statCard({ label: 'Total Paket', value: stats.total, icon: 'package', color: 'primary' })}
                ${statCard({ label: 'Draft', value: stats.draft, icon: 'file-text', color: 'secondary' })}
                ${statCard({ label: 'Proses', value: stats.proses, icon: 'loader', color: 'warning' })}
                ${statCard({ label: 'Selesai', value: stats.selesai, icon: 'check-circle', color: 'success' })}
            `;
        }

        const filterInfoEl = document.getElementById('filter-info');
        if (filterInfoEl) {
            filterInfoEl.innerHTML = `${pengadaans.length} paket ditampilkan`;
        }

        if (window.lucide) lucide.createIcons();
    }

    function renderTable() {
        const tbody = document.getElementById('pengadaan-tbody');
        if (!tbody) return;

        if (pengadaans.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-10 text-muted">
                        <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                        <p class="text-sm">Belum ada paket pengadaan</p>
                    </td>
                </tr>`;
            if (window.lucide) lucide.createIcons();
            return;
        }

        tbody.innerHTML = pengadaans.map((p, i) => {
            const fillClass = progressColor(p.progress);
            const icon = getIconForJenis(p.jenis);
            const statusLabel = p.progress === 100 ? 'Sudah selesai' :
                            p.progress > 0 ? 'Pelaksanaan' : 'Belum dimulai';

            return `
                <tr class="hover:bg-blue-50/40 transition-blueprint">
                    <td class="px-4 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600 text-label font-bold">
                            ${i + 1}
                        </span>
                    </td>
                    <td class="px-4 py-4">
                        <div class="flex items-start gap-3">
                            <div class="w-11 h-11 rounded-lg bg-gradient-to-br from-blue-100 to-blue-50 text-brand-primary flex items-center justify-center flex-shrink-0">
                                <i data-lucide="${icon}" class="w-5 h-5"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="font-semibold text-slate text-label leading-tight">${escapeHtml(p.nama)}</p>
                                <p class="text-caption text-muted mt-0.5">(${escapeHtml(p.jenis)})</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-4 text-right">
                        <p class="font-bold text-brand-primary text-label whitespace-nowrap">${rp(p.nilai)}</p>
                        <p class="text-caption text-gray-400 font-mono mt-0.5">${escapeHtml(p.kode)}</p>
                    </td>
                    <td class="px-4 py-4">
                        <div class="text-caption text-gray-600 leading-relaxed">
                            ${p.tanggal_mulai ? `
                                <p>${escapeHtml(formatTanggalShort(p.tanggal_mulai))}</p>
                                <p class="text-[10px] text-gray-400">s.d</p>
                                <p>${escapeHtml(formatTanggalShort(p.tanggal_selesai))}</p>
                            ` : '<span class="text-gray-300">-</span>'}
                        </div>
                    </td>
                    <td class="px-4 py-4">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-100 rounded-full h-2.5 overflow-hidden min-w-[80px]">
                                <div class="progress-fill ${fillClass} h-full rounded-full transition-all duration-500"
                                    style="width: ${p.progress}%"></div>
                            </div>
                            <span class="text-caption font-bold text-gray-700 w-10 text-right whitespace-nowrap">${p.progress}%</span>
                        </div>
                        <p class="text-caption text-muted mt-1">${p.jumlah_selesai}/${p.jumlah_tahapan} tahapan</p>
                    </td>
                    <td class="px-4 py-4">
                        <span class="inline-block text-caption font-semibold ${p.progress === 100 ? 'text-green-700 bg-green-50' : p.progress > 0 ? 'text-brand-primary bg-blue-50' : 'text-gray-500 bg-gray-50'} px-2.5 py-1 rounded-md">
                            ${escapeHtml(statusLabel)}
                        </span>
                    </td>
                    <td class="px-4 py-4 text-right" onclick="event.stopPropagation()">
                        <button type="button" onclick="viewDetail(${p.id})"
                            class="text-purple-600 hover:bg-purple-100 p-2 rounded-lg transition-blueprint" title="Lihat Detail">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                        <button type="button" onclick="openPengadaanForm(${p.id})"
                            class="text-brand-primary hover:bg-blue-100 p-2 rounded-lg transition-blueprint ml-1" title="Edit">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </button>
                        <button type="button" onclick="deletePengadaan(${p.id})"
                            class="text-red-600 hover:bg-red-100 p-2 rounded-lg transition-blueprint ml-1" title="Hapus">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </td>
                </tr>`;
        }).join('');

        if (window.lucide) lucide.createIcons();
    }

    function getIconForJenis(jenis) {
        if (!jenis) return 'package';
        const j = jenis.toLowerCase();
        if (j.includes('kontrak') || j.includes('renovasi') || j.includes('konstruksi')) return 'hard-hat';
        if (j.includes('barang')) return 'monitor';
        if (j.includes('jasa')) return 'briefcase';
        if (j.includes('sewa')) return 'building-2';
        return 'package';
    }

    function formatTanggalShort(s) {
        if (!s) return '-';
        const d = new Date(s);
        if (isNaN(d)) return s;
        const bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'];
        return `${d.getDate()} ${bulan[d.getMonth()]} ${d.getFullYear()}`;
    }

    function buildForm(p) {
        return `<div class="text-left space-y-3 text-sm mt-2">
            <div>
                <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Kode <span class="text-red-500">*</span></label>
                <input id="f-kode" type="text" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 font-mono"
                    value="${escapeHtml(p ? p.kode : nextKode)}" placeholder="PGD-00001" />
                <p class="text-caption text-muted mt-1">Auto-generate, tapi bisa diubah manual.</p>
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Nama Paket <span class="text-red-500">*</span></label>
                <input id="f-nama" type="text" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                    value="${escapeHtml(p ? p.nama : '')}" placeholder="Nama pengadaan" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Jenis <span class="text-red-500">*</span></label>
                    <select id="f-jenis" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">- Pilih Jenis -</option>
                        ${jenisList.map(j => `<option value="${escapeHtml(j.id)}" ${p && p.jenis_pengadaan_id === j.id ? 'selected' : ''}>${escapeHtml(j.nama)}</option>`).join('')}
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Nilai Kontrak <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-semibold pointer-events-none">Rp</span>
                        <input id="f-nilai" type="text" inputmode="numeric" class="input-nominal w-full pl-8 pr-3 py-2.5 border border-gray-200 rounded-lg text-sm text-right font-semibold focus:ring-2 focus:ring-blue-500"
                            value="${p && p.nilai ? Number(p.nilai).toLocaleString('id-ID') : ''}" placeholder="0" />
                    </div>
                </div>
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Penyedia</label>
                <input id="f-penyedia" type="text" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                    value="${escapeHtml(p && p.penyedia ? p.penyedia : '')}" placeholder="PT/CV/Perorangan" />
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Tgl Mulai</label>
                    <input id="f-tgl-mulai" type="date" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                        value="${escapeHtml(p && p.tanggal_mulai ? p.tanggal_mulai : '')}" />
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Tgl Selesai</label>
                    <input id="f-tgl-selesai" type="date" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                        value="${escapeHtml(p && p.tanggal_selesai ? p.tanggal_selesai : '')}" />
                </div>
            </div>
            ${!p ? `
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <label class="flex items-start gap-2 cursor-pointer">
                        <input type="checkbox" id="f-use-template" checked class="mt-0.5 w-4 h-4 rounded border-gray-300 text-brand-primary" />
                        <div>
                            <p class="text-label font-semibold text-brand-primary">Gunakan template tahapan standar</p>
                            <p class="text-caption text-brand-primary mt-0.5">${escapeHtml(templateTahapan.join(' → '))}</p>
                        </div>
                    </label>
                </div>
            ` : ''}
        </div>`;
    }

    function collectForm() {
        const nilaiRaw = document.getElementById('f-nilai').value;
        const nilai = Number(nilaiRaw.replace(/\D/g, '')) || 0;
        const useTemplateEl = document.getElementById('f-use-template');

        return {
            kode: document.getElementById('f-kode').value.trim(),
            nama: document.getElementById('f-nama').value.trim(),
            jenis_pengadaan_id: Number(document.getElementById('f-jenis').value) || null,
            nilai: nilai,
            penyedia: document.getElementById('f-penyedia').value.trim() || null,
            tanggal_mulai: document.getElementById('f-tgl-mulai').value || null,
            tanggal_selesai: document.getElementById('f-tgl-selesai').value || null,
            use_template: useTemplateEl ? useTemplateEl.checked : false,
        };
    }

    window.openPengadaanForm = function (id) {
        const p = id ? pengadaans.find(x => x.id === id) : null;

        Swal.fire({
            title: p ? 'Edit Pengadaan' : 'Tambah Pengadaan',
            width: 640,
            html: buildForm(p),
            showCancelButton: true,
            confirmButtonText: p ? 'Update' : 'Simpan',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'bg-brand-primary hover:bg-primary-dark text-white font-semibold px-5 py-2.5 rounded-lg mx-1 transition-blueprint',
                cancelButton: 'bg-white hover:bg-gray-50 text-brand-primary border border-brand-primary font-semibold px-5 py-2.5 rounded-lg mx-1 transition-blueprint',
            },
            buttonsStyling: false,
            didOpen: () => {
                const nilaiEl = document.getElementById('f-nilai');
                if (nilaiEl) {
                    attachNumberFormat(nilaiEl);
                }
                if (window.lucide) lucide.createIcons();
            },
            preConfirm: () => {
                const data = collectForm();
                if (!data.kode || !data.nama || !data.jenis_pengadaan_id || data.nilai <= 0) {
                    Swal.showValidationMessage('Kode, nama, jenis, dan nilai wajib diisi');
                    return false;
                }
                return data;
            }
        }).then(async (r) => {
            if (!r.isConfirmed) return;

            const url = p ? buildRoute(DATA.routes.update, p.id) : DATA.routes.store;
            const method = p ? 'PUT' : 'POST';
            const res = await apiCall(method, url, r.value);

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

    window.viewDetail = function (id) {
        const url = buildRoute(DATA.routes.detail, id);
        window.location.href = url;
    };

    window.deletePengadaan = function (id) {
        const p = pengadaans.find(x => x.id === id);
        if (!p) return;

        Swal.fire({
            title: 'Hapus Pengadaan?',
            html: `<div class="text-sm text-gray-600 text-left">
                <p>Paket <b>${escapeHtml(p.nama)}</b> (${escapeHtml(p.kode)}) akan dihapus.</p>
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-xs mt-3 text-red-700">
                    <b>⚠ Peringatan:</b> Semua tahapan pengadaan (${p.jumlah_tahapan} item) juga akan dihapus.
                </div>
            </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-2.5 rounded-lg mx-1 transition-blueprint',
                cancelButton: 'bg-white hover:bg-gray-50 text-brand-primary border border-brand-primary font-semibold px-5 py-2.5 rounded-lg mx-1 transition-blueprint',
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