// SIKAP - Kendala

(function () {
    const DATA = window.KENDALA_DATA;
    let kendalas = [];
    let kategoris = [];

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

    function formatTanggal(s) {
        if (!s) return '-';
        const d = new Date(s);
        if (isNaN(d)) return s;
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function badge(text) {
        const cls = text === 'Selesai' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700';
        return `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ${cls}">${text}</span>`;
    }

    async function loadData() {
        const params = new URLSearchParams();
        const kat = document.getElementById('filter-kategori').value;
        const stat = document.getElementById('filter-status').value;
        if (kat && kat !== 'semua') params.set('kategori', kat);
        if (stat && stat !== 'semua') params.set('status', stat);

        const url = DATA.routes.data + (params.toString() ? '?' + params.toString() : '');
        const res = await apiCall('GET', url);

        if (!res.ok) {
            toastError('Gagal memuat data kendala');
            return;
        }

        kendalas = res.data.kendalas || [];
        kategoris = res.data.kategoris || [];

        renderStats(res.data.stats);
        renderFilterDropdown();
        renderTable();
    }

    function renderStats(s) {
        const container = document.getElementById('stat-cards');
        if (!container) return;
        container.innerHTML = `
            ${statCard({ label: 'Total Kendala', value: s.total, icon: 'alert-triangle', color: 'amber' })}
            ${statCard({ label: 'Dalam Proses', value: s.proses, icon: 'loader', color: 'blue' })}
            ${statCard({ label: 'Selesai', value: s.selesai, icon: 'check-circle', color: 'emerald' })}
        `;
        document.getElementById('filter-info').textContent = `${kendalas.length} kendala ditampilkan`;
        if (window.lucide) lucide.createIcons();
    }

    function renderFilterDropdown() {
        const sel = document.getElementById('filter-kategori');
        const current = sel.value;
        sel.innerHTML = '<option value="semua">Semua</option>' +
            kategoris.map(k => `<option value="${k.id}">${k.nama}</option>`).join('');
        sel.value = current;
    }

    function renderTable() {
        const tbody = document.getElementById('kendala-tbody');
        if (!tbody) return;

        if (kendalas.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-gray-400">Belum ada kendala</td></tr>`;
            return;
        }

        tbody.innerHTML = kendalas.map(k => {
            const katNama = k.kategori_kendala ? k.kategori_kendala.nama : '-';
            
            // Build "Terkait" info
            let terkaitHtml = '<span class="text-[10px] text-gray-400 italic">Umum</span>';
            if (k.target) {
                const bulanNama = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Ags','Sep','Okt','Nov','Des'][k.target.bulan - 1] || '-';
                const targetInfo = `<span class="text-[10px] font-semibold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded">${bulanNama} ${k.target.tahun}</span>`;
                const komInfo = k.komponen_belanja 
                    ? `<span class="text-[10px] font-mono text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded">${k.komponen_belanja.kode}</span>` 
                    : '';
                terkaitHtml = `<div class="flex flex-wrap items-center gap-1">${targetInfo}${komInfo ? ' · ' + komInfo : ''}</div>`;
            }
            
            return `
                <tr class="hover:bg-blue-50/40 transition">
                    <td class="px-4 py-3 text-gray-700 text-xs whitespace-nowrap">${formatTanggal(k.tanggal)}</td>
                    <td class="px-4 py-3">
                        <span class="inline-block px-2 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-700">
                            ${katNama}
                        </span>
                    </td>
                    <td class="px-4 py-3">${terkaitHtml}</td>
                    <td class="px-4 py-3 text-gray-800">${k.deskripsi}</td>
                    <td class="px-4 py-3">${badge(k.status)}</td>
                    <td class="px-4 py-3 text-right">
                        <button type="button" onclick="editKendala(${k.id})" class="text-blue-700 hover:bg-blue-100 px-2 py-1 rounded transition" title="Edit">
                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                        </button>
                        <button type="button" onclick="deleteKendala(${k.id})" class="text-red-600 hover:bg-red-100 px-2 py-1 rounded transition ml-1" title="Hapus">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </td>
                </tr>`;
        }).join('');

        if (window.lucide) lucide.createIcons();
    }

    function buildForm(k) {
        const today = new Date().toISOString().substring(0, 10);
        return `<div class="text-left space-y-3 text-sm mt-2">
            <div>
                <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Tanggal <span class="text-red-500">*</span></label>
                <input id="f-tanggal" type="date" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                    value="${k ? k.tanggal.substring(0,10) : today}" />
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Kategori <span class="text-red-500">*</span></label>
                <select id="f-kategori" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">- Pilih Kategori -</option>
                    ${kategoris.map(x => `<option value="${x.id}" ${k && k.kategori_kendala_id === x.id ? 'selected' : ''}>${x.nama}</option>`).join('')}
                </select>
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Deskripsi <span class="text-red-500">*</span></label>
                <textarea id="f-deskripsi" rows="2" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">${k ? k.deskripsi : ''}</textarea>
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Dampak</label>
                <input id="f-dampak" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500" value="${k ? (k.dampak || '') : ''}" />
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Tindak Lanjut</label>
                <input id="f-tindak" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500" value="${k ? (k.tindak_lanjut || '') : ''}" />
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Status <span class="text-red-500">*</span></label>
                <select id="f-status" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="Proses" ${k && k.status === 'Proses' ? 'selected' : ''}>Proses</option>
                    <option value="Selesai" ${k && k.status === 'Selesai' ? 'selected' : ''}>Selesai</option>
                </select>
            </div>
        </div>`;
    }

    function collectForm() {
        return {
            tanggal: document.getElementById('f-tanggal').value,
            kategori_kendala_id: Number(document.getElementById('f-kategori').value) || null,
            deskripsi: document.getElementById('f-deskripsi').value.trim(),
            dampak: document.getElementById('f-dampak').value.trim(),
            tindak_lanjut: document.getElementById('f-tindak').value.trim(),
            status: document.getElementById('f-status').value,
        };
    }

    window.openKendalaForm = function () {
        Swal.fire({
            title: 'Tambah Kendala',
            width: 520,
            html: buildForm(null),
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-blue-700 hover:bg-blue-800 text-white font-medium px-5 py-2.5 rounded-lg mx-1',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1',
            },
            buttonsStyling: false,
            didOpen: () => { if (window.lucide) lucide.createIcons(); },
            preConfirm: () => {
                const data = collectForm();
                if (!data.tanggal || !data.kategori_kendala_id || !data.deskripsi) {
                    Swal.showValidationMessage('Tanggal, kategori, dan deskripsi wajib diisi');
                    return false;
                }
                return data;
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

    window.editKendala = function (id) {
        const k = kendalas.find(x => x.id === id);
        if (!k) return;
        Swal.fire({
            title: 'Edit Kendala',
            width: 520,
            html: buildForm(k),
            showCancelButton: true,
            confirmButtonText: 'Update',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-blue-700 hover:bg-blue-800 text-white font-medium px-5 py-2.5 rounded-lg mx-1',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1',
            },
            buttonsStyling: false,
            didOpen: () => { if (window.lucide) lucide.createIcons(); },
            preConfirm: () => {
                const data = collectForm();
                if (!data.tanggal || !data.kategori_kendala_id || !data.deskripsi) {
                    Swal.showValidationMessage('Tanggal, kategori, dan deskripsi wajib diisi');
                    return false;
                }
                return data;
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

    window.deleteKendala = function (id) {
        const k = kendalas.find(x => x.id === id);
        if (!k) return;
        Swal.fire({
            title: 'Hapus Kendala?',
            html: `<div class="text-sm text-gray-600"><b>${k.deskripsi}</b> akan dihapus.</div>`,
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