// ============================================================
// SIKAP - Pengadaan Viewer (Read-Only)
// Versi aman XSS
// ============================================================

(function () {
    const DATA = window.PENGADAAN_DATA;
    let pengadaans = [];

    function buildRoute(template, id) {
        return template.replace('__ID__', id);
    }

    async function apiCall(method, url) {
        const res = await fetch(url, {
            method,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
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
            'Proses': 'bg-blue-100 text-blue-700',
            'Selesai': 'bg-green-100 text-green-700',
            'Batal': 'bg-red-100 text-red-700',
        };
        const cls = map[s] || 'bg-gray-100 text-gray-700';
        return `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ${cls}">${escapeHtml(s)}</span>`;
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
        renderStats(res.data.stats);
        renderTable();
    }

    function renderStats(stats) {
        document.getElementById('stat-cards').innerHTML = `
            ${statCard({ label: 'Total Paket', value: stats.total, icon: 'package', color: 'blue' })}
            ${statCard({ label: 'Draft', value: stats.draft, icon: 'file-text', color: 'purple' })}
            ${statCard({ label: 'Proses', value: stats.proses, icon: 'loader', color: 'amber' })}
            ${statCard({ label: 'Selesai', value: stats.selesai, icon: 'check-circle', color: 'emerald' })}
        `;
        const filterInfoEl = document.getElementById('filter-info'); 
        if (filterInfoEl) filterInfoEl.innerHTML = `${pengadaans.length} paket ditampilkan`;
        if (window.lucide) lucide.createIcons();
    }

    function renderTable() {
        const tbody = document.getElementById('pengadaan-tbody');

        if (pengadaans.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-10 text-gray-400">
                        <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                        <p class="text-sm">Belum ada paket pengadaan</p>
                    </td>
                </tr>`;
            if (window.lucide) lucide.createIcons();
            return;
        }

        tbody.innerHTML = pengadaans.map(p => {
            const fillClass = progressColor(p.progress);
            return `
                <tr class="hover:bg-blue-50/40 transition">
                    <td class="px-4 py-3">
                        <span class="inline-block font-mono text-[11px] font-bold bg-blue-50 text-blue-700 px-2 py-1 rounded">
                            ${escapeHtml(p.kode)}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">${escapeHtml(p.nama)}</p>
                        ${p.penyedia ? `<p class="text-[10px] text-gray-500 mt-0.5">${escapeHtml(p.penyedia)}</p>` : ''}
                        <p class="text-[10px] text-gray-400 mt-0.5">${rp(p.nilai)}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">${escapeHtml(p.jenis)}</td>
                    <td class="px-4 py-3">
                        <div class="progress-bar-container">
                            <div class="progress-bar">
                                <div class="progress-fill ${fillClass}" style="width: ${p.progress}%"></div>
                            </div>
                            <span class="text-xs font-bold text-gray-700 w-10 text-right">${p.progress}%</span>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-1">${p.jumlah_selesai}/${p.jumlah_tahapan} tahapan</p>
                    </td>
                    <td class="px-4 py-3">${statusBadge(p.status)}</td>
                    <td class="px-4 py-3 text-right">
                        <button type="button" onclick="viewDetail(${p.id})"
                            class="text-purple-600 hover:bg-purple-100 p-2 rounded-lg transition" title="Lihat Detail">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </button>
                    </td>
                </tr>`;
        }).join('');

        if (window.lucide) lucide.createIcons();
    }

    window.viewDetail = function (id) {
        window.location.href = buildRoute(DATA.routes.detail, id);
    };

    document.addEventListener('DOMContentLoaded', loadData);
})();