// ============================================================
// SIKAP - Bug Report
// ============================================================

(function () {
    const DATA = window.BUG_REPORT_DATA;
    let reports = [];

    async function apiCall(method, url, body) {
        const opts = {
            method,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': DATA.csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        };
        if (body) {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
        const res = await fetch(url, opts);
        const json = await res.json().catch(() => ({}));
        return { ok: res.ok, status: res.status, data: json };
    }

    async function loadReports() {
        const res = await apiCall('GET', DATA.routes.data);
        if (!res.ok) {
            toastError('Gagal memuat laporan');
            return;
        }
        reports = res.data.reports || [];
        renderList();
    }

    function renderList() {
        const container = document.getElementById('bug-report-list');
        if (!container) return;

        if (reports.length === 0) {
            container.innerHTML = `
                <div class="text-center py-10">
                    <i data-lucide="inbox" class="w-12 h-12 mx-auto text-gray-300 mb-2"></i>
                    <p class="text-sm text-gray-500">Belum ada laporan</p>
                    <p class="text-xs text-gray-400 mt-1">Klik "Lapor Bug Baru" untuk mulai</p>
                </div>`;
            if (window.lucide) lucide.createIcons();
            return;
        }

        const prioritasColorMap = {
            red: 'text-red-700 bg-red-100',
            orange: 'text-orange-700 bg-orange-100',
            amber: 'text-amber-700 bg-amber-100',
            blue: 'text-blue-700 bg-blue-100',
            gray: 'text-gray-700 bg-gray-100',
        };
        const statusColorMap = {
            blue: 'text-blue-700 bg-blue-100',
            amber: 'text-amber-700 bg-amber-100',
            green: 'text-green-700 bg-green-100',
            red: 'text-red-700 bg-red-100',
            gray: 'text-gray-700 bg-gray-100',
        };

        container.innerHTML = reports.map(r => `
            <div class="p-4 hover:bg-gray-50 transition">
                <div class="flex items-start justify-between gap-3 mb-2">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-gray-800">${escapeHtml(r.judul)}</p>
                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">${escapeHtml(r.deskripsi)}</p>
                    </div>
                    <div class="flex flex-col items-end gap-1 flex-shrink-0">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${statusColorMap[r.status_color] || statusColorMap.gray}">
                            ${escapeHtml(r.status_label)}
                        </span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${prioritasColorMap[r.prioritas_color] || prioritasColorMap.gray}">
                            ${escapeHtml(r.prioritas_label)}
                        </span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 text-[10px] text-gray-500">
                    <span class="flex items-center gap-1">
                        <i data-lucide="tag" class="w-3 h-3"></i>
                        ${escapeHtml(r.kategori_label)}
                    </span>
                    ${r.halaman_terkait ? `
                    <span class="flex items-center gap-1">
                        <i data-lucide="link" class="w-3 h-3"></i>
                        ${escapeHtml(r.halaman_terkait)}
                    </span>
                    ` : ''}
                    <span class="flex items-center gap-1">
                        <i data-lucide="calendar" class="w-3 h-3"></i>
                        ${escapeHtml(r.created_at)}
                    </span>
                </div>
                ${r.catatan_admin ? `
                <div class="mt-2 p-2 bg-blue-50 border border-blue-100 rounded text-xs text-blue-800">
                    <b>Respon Admin:</b> ${escapeHtml(r.catatan_admin)}
                </div>
                ` : ''}
            </div>
        `).join('');

        if (window.lucide) lucide.createIcons();
    }

    function buildForm() {
        return `
            <div class="text-left space-y-3 text-sm mt-2">
                <div>
                    <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Judul Bug <span class="text-red-500">*</span></label>
                    <input id="f-judul" type="text" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500"
                        placeholder="Contoh: Tombol simpan tidak berfungsi" />
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Deskripsi <span class="text-red-500">*</span></label>
                    <textarea id="f-deskripsi" rows="4" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500"
                        placeholder="Jelaskan langkah reproduksi bug..."></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Kategori <span class="text-red-500">*</span></label>
                        <select id="f-kategori" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-amber-500">
                            <option value="fungsional">Fungsional</option>
                            <option value="ui_ux">UI/UX</option>
                            <option value="performa">Performa</option>
                            <option value="data">Data</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Prioritas <span class="text-red-500">*</span></label>
                        <select id="f-prioritas" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm bg-white focus:ring-2 focus:ring-amber-500">
                            <option value="rendah">Rendah</option>
                            <option value="sedang" selected>Sedang</option>
                            <option value="tinggi">Tinggi</option>
                            <option value="kritis">Kritis</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Halaman Terkait</label>
                    <input id="f-halaman" type="text" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-amber-500"
                        placeholder="Contoh: /keuangan/realisasi" />
                </div>
            </div>
        `;
    }

    window.openBugForm = function () {
        Swal.fire({
            title: 'Laporkan Bug Baru',
            width: 580,
            html: buildForm(),
            showCancelButton: true,
            confirmButtonText: 'Kirim Laporan',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-amber-600 hover:bg-amber-700 text-white font-medium px-5 py-2.5 rounded-lg mx-1 transition',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1 transition',
                popup: 'rounded-2xl',
            },
            buttonsStyling: false,
            didOpen: () => { if (window.lucide) lucide.createIcons(); },
            preConfirm: () => {
                const data = {
                    judul: document.getElementById('f-judul').value.trim(),
                    deskripsi: document.getElementById('f-deskripsi').value.trim(),
                    kategori: document.getElementById('f-kategori').value,
                    prioritas: document.getElementById('f-prioritas').value,
                    halaman_terkait: document.getElementById('f-halaman').value.trim() || null,
                };
                if (!data.judul) {
                    Swal.showValidationMessage('Judul wajib diisi');
                    return false;
                }
                if (!data.deskripsi) {
                    Swal.showValidationMessage('Deskripsi wajib diisi');
                    return false;
                }
                return data;
            }
        }).then(async (result) => {
            if (!result.isConfirmed) return;

            const res = await apiCall('POST', DATA.routes.store, result.value);

            if (res.ok) {
                toastSuccess(res.data.message);
                loadReports();
            } else {
                const errors = res.data.errors || {};
                const first = Object.values(errors)[0];
                toastError(Array.isArray(first) ? first[0] : (res.data.message || 'Gagal mengirim laporan'));
            }
        });
    };

    document.addEventListener('DOMContentLoaded', loadReports);
})();