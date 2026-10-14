// ============================================================
// SIKAP - Pengadaan Detail Viewer (Read-Only)
// Versi aman XSS
// ============================================================

(function () {
    const DATA = window.PENGADAAN_DETAIL_DATA;
    let pengadaan = null;

    function buildRoute(template, id) {
        return template.replace('__ID__', id);
    }

    async function apiCall(url) {
        const res = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        return await res.json();
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
        const json = await apiCall(url);

        if (!json.ok) {
            toastError('Gagal memuat data pengadaan');
            return;
        }

        pengadaan = json.pengadaan;
        renderHeader();
        renderProgress();
        renderTahapan();
    }

    function renderHeader() {
        const p = pengadaan;
        document.getElementById('pengadaan-header').innerHTML = `
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center gap-3 flex-wrap mb-2">
                    <span class="font-mono text-xs font-bold bg-blue-50 text-blue-700 px-3 py-1 rounded-lg">${escapeHtml(p.kode)}</span>
                    ${statusBadge(p.status)}
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mb-2">${escapeHtml(p.nama)}</h1>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-2 text-sm mt-4">
                    <div class="flex items-center gap-2">
                        <i data-lucide="tag" class="w-4 h-4 text-gray-400"></i>
                        <span class="text-xs text-gray-500">Jenis:</span>
                        <b class="text-gray-800">${escapeHtml(p.jenis)}</b>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="banknote" class="w-4 h-4 text-gray-400"></i>
                        <span class="text-xs text-gray-500">Nilai:</span>
                        <b class="text-blue-700 font-semibold">${rp(p.nilai)}</b>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="building" class="w-4 h-4 text-gray-400"></i>
                        <span class="text-xs text-gray-500">Penyedia:</span>
                        <b class="text-gray-800">${escapeHtml(p.penyedia || '-')}</b>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="calendar" class="w-4 h-4 text-gray-400"></i>
                        <span class="text-xs text-gray-500">Periode:</span>
                        <b class="text-gray-800">${escapeHtml(formatTanggal(p.tanggal_mulai))} - ${escapeHtml(formatTanggal(p.tanggal_selesai))}</b>
                    </div>
                </div>
            </div>
        `;
        if (window.lucide) lucide.createIcons();
    }

    function renderProgress() {
        const p = pengadaan;
        document.getElementById('progress-card').innerHTML = `
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
                    <div class="bg-white h-full rounded-full transition-all duration-700" style="width: ${p.progress}%"></div>
                </div>
            </div>
        `;
        if (window.lucide) lucide.createIcons();
    }

    function renderTahapan() {
        const container = document.getElementById('tahapan-list');
        const list = pengadaan.tahapans || [];

        if (list.length === 0) {
            container.innerHTML = `<p class="text-center py-8 text-gray-400 text-sm">Belum ada tahapan</p>`;
            return;
        }

        container.innerHTML = list.map((t, i) => `
            <div class="tahapan-row ${t.selesai ? 'selesai' : ''} p-4">
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 rounded-full ${t.selesai ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-500'} flex items-center justify-center flex-shrink-0 text-xs font-bold mt-0.5">
                        ${t.selesai ? '✓' : (i + 1)}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="tahapan-nama font-semibold text-gray-800">
                            ${escapeHtml(t.nama)}
                        </p>
                        ${t.selesai && t.tanggal_selesai ? `
                            <p class="text-[11px] text-green-700 flex items-center gap-1 mt-1">
                                <i data-lucide="check-circle" class="w-3 h-3"></i>
                                Selesai: ${escapeHtml(formatTanggal(t.tanggal_selesai))}
                            </p>
                        ` : ''}
                        ${t.kendala ? `
                            <div class="kendala-badge mt-1.5">
                                <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                                <span>${escapeHtml(t.kendala)}</span>
                            </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        `).join('');

        if (window.lucide) lucide.createIcons();
    }

    document.addEventListener('DOMContentLoaded', loadData);
})();