// ============================================================
// SIKAP - Audit Trail (with Pagination)
// Versi aman XSS
// ============================================================

(function () {
    const DATA = window.AUDIT_DATA;
    let currentPage = 1;
    let perPage = 20;
    let totalPages = 1;

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

    function badge(action) {
        const map = {
            CREATE: 'bg-green-100 text-green-700',
            UPDATE: 'bg-blue-100 text-blue-700',
            DELETE: 'bg-red-100 text-red-700',
            RESTORE: 'bg-purple-100 text-purple-700',
        };
        const cls = map[action] || 'bg-gray-100 text-gray-700';
        return `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ${cls}">${escapeHtml(action)}</span>`;
    }

    function formatDate(s) {
        if (!s) return '-';
        const d = new Date(s);
        if (isNaN(d)) return s;
        return d.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function renderTable(logs) {
        const tbody = document.getElementById('audit-table-body') || document.getElementById('audit-tbody');
        if (!tbody) {
            console.warn('[Audit] Element tbody tidak ditemukan. Cek ID di view.');
            return;
        }

        if (!logs || logs.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6" class="text-center py-8 text-gray-400">
                <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                <p class="text-sm">Tidak ada log</p>
            </td></tr>`;
            if (window.lucide) lucide.createIcons();
            return;
        }

        tbody.innerHTML = logs.map(l => `
            <tr class="hover:bg-blue-50/40 transition cursor-pointer" onclick="showDetail(${l.id})">
                <td class="px-4 py-3 text-gray-600 text-xs whitespace-nowrap">
                    <i data-lucide="clock" class="w-3.5 h-3.5 inline mr-1 text-gray-400"></i>${escapeHtml(formatDate(l.created_at))}
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">
                            ${escapeHtml((l.user_name || 'S').charAt(0))}
                        </div>
                        <span class="text-sm font-medium text-gray-800">${escapeHtml(l.user_name)}</span>
                    </div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">${escapeHtml(l.module)}</td>
                <td class="px-4 py-3">${badge(l.action)}</td>
                <td class="px-4 py-3 text-xs font-mono text-gray-600">${escapeHtml(l.record_id)}</td>
                <td class="px-4 py-3 text-right">
                    <i data-lucide="eye" class="w-4 h-4 text-blue-700 inline"></i>
                </td>
            </tr>`).join('');

        if (window.lucide) lucide.createIcons();
    }

    // ============ PAGINATION ============
    function renderPagination(p) {
        const container = document.getElementById('pagination-container');
        if (!container) return;

        totalPages = p.last_page;
        currentPage = p.current_page;

        if (p.total === 0) {
            container.innerHTML = '';
            return;
        }

        const infoText = `Menampilkan <b>${escapeHtml(p.from)}</b> - <b>${escapeHtml(p.to)}</b> dari <b>${escapeHtml(p.total)}</b> log`;

        const pages = [];
        const maxVisible = 7;
        let start = Math.max(1, currentPage - Math.floor(maxVisible / 2));
        let end = Math.min(totalPages, start + maxVisible - 1);

        if (end - start + 1 < maxVisible) {
            start = Math.max(1, end - maxVisible + 1);
        }

        for (let i = start; i <= end; i++) {
            pages.push(i);
        }

        const prevBtn = currentPage > 1
            ? `<button type="button" onclick="goToPage(${currentPage - 1})"
                class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-100 transition disabled:opacity-50">
                <i data-lucide="chevron-left" class="w-4 h-4"></i> Prev
               </button>`
            : `<span class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-300 cursor-not-allowed">
                <i data-lucide="chevron-left" class="w-4 h-4"></i> Prev
               </span>`;

        const nextBtn = currentPage < totalPages
            ? `<button type="button" onclick="goToPage(${currentPage + 1})"
                class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-100 transition">
                Next <i data-lucide="chevron-right" class="w-4 h-4"></i>
               </button>`
            : `<span class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-300 cursor-not-allowed">
                Next <i data-lucide="chevron-right" class="w-4 h-4"></i>
               </span>`;

        const pageButtons = pages.map(pg => {
            const isActive = pg === currentPage;
            return `<button type="button" onclick="goToPage(${pg})"
                class="min-w-[32px] h-8 rounded-lg text-xs font-bold transition
                ${isActive
                    ? 'bg-blue-700 text-white shadow-md shadow-blue-200'
                    : 'text-gray-600 hover:bg-gray-100'}">
                ${escapeHtml(pg)}
            </button>`;
        }).join('');

        let startEllipsis = start > 1 ? `<span class="px-1 text-gray-400">...</span>` : '';
        let endEllipsis = end < totalPages ? `<span class="px-1 text-gray-400">...</span>` : '';

        container.innerHTML = `
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs text-gray-500">${infoText}</p>

                <div class="flex items-center gap-1">
                    ${prevBtn}
                    ${startEllipsis}
                    ${pageButtons}
                    ${endEllipsis}
                    ${nextBtn}
                </div>
            </div>
        `;

        if (window.lucide) lucide.createIcons();
    }

    // ============ LOAD DATA ============
    async function loadData(page = 1) {
        currentPage = page;

        const params = new URLSearchParams();
        const tgl = document.getElementById('f-tanggal').value;
        const user = document.getElementById('f-user').value;
        const mod = document.getElementById('f-module').value;
        const act = document.getElementById('f-action').value;

        if (tgl) params.set('tanggal', tgl);
        if (user && user !== 'semua') params.set('user', user);
        if (mod && mod !== 'semua') params.set('module', mod);
        if (act && act !== 'semua') params.set('action', act);
        params.set('page', page);
        params.set('per_page', perPage);

        const url = DATA.routes.data + '?' + params.toString();
        const res = await apiCall('GET', url);

        if (res.ok) {
            renderTable(res.data.logs);
            if (res.data.pagination) {
                renderPagination(res.data.pagination);
            }
            const pag = res.data.pagination || { total: 0 };
            document.getElementById('log-count').textContent = `${pag.total} log ditampilkan`;
            
            // Update filter dropdown
            const userSelect = document.getElementById('f-user');
            const modSelect = document.getElementById('f-module');
            const currentUser = userSelect.value;
            const currentMod = modSelect.value;

            userSelect.innerHTML = '<option value="semua">Semua</option>' +
                (res.data.filters.users || []).map(u => `<option value="${escapeHtml(u)}">${escapeHtml(u)}</option>`).join('');
            modSelect.innerHTML = '<option value="semua">Semua</option>' +
                (res.data.filters.modules || []).map(m => `<option value="${escapeHtml(m)}">${escapeHtml(m)}</option>`).join('');

            userSelect.value = currentUser;
            modSelect.value = currentMod;

            if (window.lucide) lucide.createIcons();
        } else {
            toastError('Gagal memuat log');
        }
    }

    window.changePerPage = function (val) {
        perPage = Number(val);
        loadData(1);
    };

    // ============ FILTERS ============
    window.applyFilters = () => loadData(1);
    window.resetFilters = () => {
        document.getElementById('f-tanggal').value = '';
        document.getElementById('f-user').value = 'semua';
        document.getElementById('f-module').value = 'semua';
        document.getElementById('f-action').value = 'semua';
        loadData(1);
    };

    // ============ PAGINATION ============
    window.goToPage = function (page) {
        if (page < 1 || page > totalPages) return;
        loadData(page);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    // ============ SHOW DETAIL ============
    window.showDetail = async function (id) {
        const url = buildRoute(DATA.routes.show, id);
        const res = await apiCall('GET', url);
        if (!res.ok) return;

        const l = res.data.log;
        Swal.fire({
            title: 'Detail Perubahan',
            width: 600,
            html: `
                <div class="text-left text-sm">
                    <div class="mb-3 text-gray-600 text-xs">
                        ${escapeHtml(formatDate(l.created_at))} - <b>${escapeHtml(l.user_name)}</b> (${escapeHtml(l.module)}) - <span class="font-mono">${escapeHtml(l.record_id)}</span>
                    </div>
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 font-mono text-xs space-y-2">
                        <div><span class="text-red-700 font-bold">BEFORE</span> : ${escapeHtml(l.before || '-')}</div>
                        <div><span class="text-green-700 font-bold">AFTER</span> &nbsp;: ${escapeHtml(l.after || '-')}</div>
                    </div>
                    <div class="mt-3 text-[11px] text-gray-500">
                        <div>IP: ${escapeHtml(l.ip || '-')}</div>
                        <div>Action: ${escapeHtml(l.action)}</div>
                    </div>
                </div>
            `,
            confirmButtonText: 'Tutup',
            customClass: {
                confirmButton: 'bg-blue-700 hover:bg-blue-800 text-white font-medium px-5 py-2.5 rounded-lg',
            },
            buttonsStyling: false,
        });
    };

    document.addEventListener('DOMContentLoaded', () => loadData(1));
})();