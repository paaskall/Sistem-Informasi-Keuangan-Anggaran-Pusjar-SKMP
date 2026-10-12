// ============================================================
// SIKAP - Master Data Page
// ============================================================

(function () {
    const DATA = window.MASTER_DATA;

    // ============ ROUTES BUILDER ============
    function buildRoute(template, tab, id) {
        let url = template.replace('__TAB__', tab);
        if (id !== undefined && id !== null) {
            url = url.replace('__ID__', id);
        }
        return url;
    }

    // ============ FETCH HELPER ============
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

    // ============ RENDER TABLE HEAD ============
    function renderTableHead() {
        const conf = DATA.tabConfig[DATA.activeTab];
        const cols = ['id', ...conf.fields.map(f => f.key)];
        const thead = document.getElementById('table-head');

        let html = `<th class="text-center px-4 py-3 font-semibold w-16">ID</th>`;
        conf.fields.forEach(f => {
            html += `<th class="text-left px-4 py-3 font-semibold">${f.label}</th>`;
        });
        html += `<th class="text-center px-4 py-3 font-semibold w-32">Aksi</th>`;
        thead.innerHTML = html;
    }

    // ============ RENDER TABLE BODY ============
    function renderTableBody() {
        const conf = DATA.tabConfig[DATA.activeTab];
        const tbody = document.getElementById('table-body');

        if (!DATA.items || DATA.items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="${conf.fields.length + 2}" class="text-center py-12 text-gray-400">
                        <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                        <p class="text-sm">Belum ada data ${conf.label}</p>
                        <p class="text-xs mt-1">Klik "Tambah" untuk memulai</p>
                    </td>
                </tr>`;
            if (window.lucide) lucide.createIcons();
            return;
        }

        tbody.innerHTML = DATA.items.map(item => {
            let cells = `<td class="px-4 py-3 text-center">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-gray-100 text-gray-600 text-xs font-bold">
                    ${item.id}
                </span>
            </td>`;

            conf.fields.forEach(f => {
                const value = item[f.key];

                if (f.key === 'kode') {
                    cells += `<td class="px-4 py-3">
                        <span class="inline-block font-mono text-[11px] font-bold bg-blue-50 text-blue-700 px-2 py-1 rounded">
                            ${value || '-'}
                        </span>
                    </td>`;
                } else if (f.type === 'select' && f.source) {
                    const related = (DATA.references[f.source] || []).find(x => x.id === value);
                    const label = related ? related[f.source_label] : 'Tidak ditemukan';
                    const prefix = related && related[f.source_prefix] ? related[f.source_prefix] : null;
                    cells += `<td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-md bg-indigo-50 text-indigo-700 flex items-center justify-center text-[10px] font-bold flex-shrink-0">
                                <i data-lucide="folder" class="w-3 h-3"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-gray-800 truncate">${label}</p>
                                ${prefix ? `<p class="text-[10px] text-gray-400 font-mono">${prefix}</p>` : ''}
                            </div>
                        </div>
                    </td>`;
                } else {
                    const cls = f.key === 'nama'
                        ? 'font-medium text-gray-800'
                        : (f.mono ? 'font-mono text-xs text-gray-600' : 'text-gray-600');
                    cells += `<td class="px-4 py-3">
                        <span class="${cls}">${value || '-'}</span>
                    </td>`;
                }
            });

            cells += `<td class="px-4 py-3">
                <div class="flex items-center justify-center gap-1">
                    <button type="button" onclick="editItem(${item.id})"
                        class="text-blue-700 hover:bg-blue-100 p-2 rounded-lg transition" title="Edit">
                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                    </button>
                    <button type="button" onclick="deleteItem(${item.id})"
                        class="text-red-600 hover:bg-red-100 p-2 rounded-lg transition" title="Hapus">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </td>`;

            return `<tr class="hover:bg-blue-50/40 transition">${cells}</tr>`;
        }).join('');

        if (window.lucide) lucide.createIcons();
    }

    // ============ SWITCH TAB ============
    window.switchTab = async function (tab) {
        // Update URL (biar bisa refresh tetap di tab yang sama)
        const url = new URL(window.location);
        url.searchParams.set('tab', tab);
        window.history.pushState({}, '', url);

        // Update UI tab
        document.querySelectorAll('.tab-btn').forEach(btn => {
            const isActive = btn.dataset.tab === tab;
            const count = btn.querySelector('.tab-count');

            btn.classList.toggle('border-blue-700', isActive);
            btn.classList.toggle('text-blue-700', isActive);
            btn.classList.toggle('bg-blue-50/50', isActive);
            btn.classList.toggle('border-transparent', !isActive);
            btn.classList.toggle('text-gray-500', !isActive);

            if (count) {
                count.classList.toggle('bg-blue-100', isActive);
                count.classList.toggle('text-blue-700', isActive);
                count.classList.toggle('bg-gray-100', !isActive);
                count.classList.toggle('text-gray-500', !isActive);
            }
        });

        // Fetch data
        const urlFetch = buildRoute(DATA.routes.items, tab);
        const res = await apiCall('GET', urlFetch);

        if (res.ok) {
            DATA.activeTab = tab;
            DATA.items = res.data.items || [];
            DATA.references = res.data.references || {};

            document.getElementById('btn-add-label').textContent = 'Tambah ' + DATA.tabConfig[tab].label;

            renderTableHead();
            renderTableBody();
        } else {
            toastError('Gagal memuat data');
        }
    };

    // ============ FORM MODAL ============
    function buildFormHtml(item) {
        const conf = DATA.tabConfig[DATA.activeTab];

        return `<div class="text-left space-y-4 text-sm mt-2">
            ${conf.fields.map(f => {
                const value = item ? (item[f.key] ?? '') : '';

                if (f.type === 'select' && f.source) {
                    const options = DATA.references[f.source] || [];
                    return `
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">
                                ${f.label} ${f.required ? '<span class="text-red-500">*</span>' : ''}
                            </label>
                            <select id="f-${f.key}" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white">
                                <option value="">- Pilih ${f.label} -</option>
                                ${options.map(o => `
                                    <option value="${o[f.source_value]}" ${value == o[f.source_value] ? 'selected' : ''}>
                                        ${o[f.source_prefix] ? o[f.source_prefix] + ' - ' : ''}${o[f.source_label]}
                                    </option>
                                `).join('')}
                            </select>
                        </div>`;
                }

                return `
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">
                            ${f.label} ${f.required ? '<span class="text-red-500">*</span>' : ''}
                        </label>
                        <input id="f-${f.key}" type="text"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 ${f.mono ? 'font-mono' : ''}"
                            value="${value}"
                            placeholder="${f.placeholder || ''}" />
                    </div>`;
            }).join('')}
        </div>`;
    }

    function collectForm() {
        const conf = DATA.tabConfig[DATA.activeTab];
        const data = {};
        let valid = true;
        let errMsg = '';

        conf.fields.forEach(f => {
            const el = document.getElementById('f-' + f.key);
            if (!el) return;

            let val = el.value.trim();
            if (f.type === 'select') {
                val = val ? Number(val) : '';
            }

            if (f.required && (val === '' || val === null)) {
                valid = false;
                errMsg = errMsg || `${f.label} wajib diisi`;
                return;
            }

            data[f.key] = val;
        });

        return { valid, errMsg, data };
    }

    // ============ OPEN FORM (CREATE) ============
    window.openForm = function () {
        const conf = DATA.tabConfig[DATA.activeTab];

        Swal.fire({
            title: `Tambah ${conf.label}`,
            width: 520,
            html: buildFormHtml(null),
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-blue-700 hover:bg-blue-800 text-white font-medium px-5 py-2.5 rounded-lg mx-1 transition',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1 transition',
                popup: 'rounded-2xl',
            },
            buttonsStyling: false,
            didOpen: () => { if (window.lucide) lucide.createIcons(); },
            preConfirm: () => {
                const { valid, errMsg, data } = collectForm();
                if (!valid) {
                    Swal.showValidationMessage(errMsg);
                    return false;
                }
                return data;
            }
        }).then(async (r) => {
            if (!r.isConfirmed) return;

            const url = buildRoute(DATA.routes.store, DATA.activeTab);
            const res = await apiCall('POST', url, r.value);

            if (res.ok) {
                toastSuccess(res.data.message || 'Data berhasil ditambahkan');
                switchTab(DATA.activeTab);
            } else {
                const errors = res.data.errors || {};
                const first = Object.values(errors)[0];
                toastError(Array.isArray(first) ? first[0] : (res.data.message || 'Gagal menyimpan'));
            }
        });
    };

    // ============ EDIT ITEM ============
    window.editItem = function (id) {
        const item = DATA.items.find(x => x.id === id);
        if (!item) return;

        const conf = DATA.tabConfig[DATA.activeTab];

        Swal.fire({
            title: `Edit ${conf.label}`,
            width: 520,
            html: buildFormHtml(item),
            showCancelButton: true,
            confirmButtonText: 'Update',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-blue-700 hover:bg-blue-800 text-white font-medium px-5 py-2.5 rounded-lg mx-1 transition',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1 transition',
                popup: 'rounded-2xl',
            },
            buttonsStyling: false,
            didOpen: () => { if (window.lucide) lucide.createIcons(); },
            preConfirm: () => {
                const { valid, errMsg, data } = collectForm();
                if (!valid) {
                    Swal.showValidationMessage(errMsg);
                    return false;
                }
                return data;
            }
        }).then(async (r) => {
            if (!r.isConfirmed) return;

            const url = buildRoute(DATA.routes.update, DATA.activeTab, id);
            const res = await apiCall('PUT', url, r.value);

            if (res.ok) {
                toastSuccess(res.data.message || 'Data berhasil diupdate');
                switchTab(DATA.activeTab);
            } else {
                const errors = res.data.errors || {};
                const first = Object.values(errors)[0];
                toastError(Array.isArray(first) ? first[0] : (res.data.message || 'Gagal menyimpan'));
            }
        });
    };

    // ============ DELETE ITEM ============
    window.deleteItem = function (id) {
        const item = DATA.items.find(x => x.id === id);
        if (!item) return;

        const conf = DATA.tabConfig[DATA.activeTab];
        const label = item.nama || item.name || `ID ${id}`;

        Swal.fire({
            title: `Hapus ${conf.label}?`,
            html: `<div class="text-sm text-gray-600">
                <b>${label}</b> akan dihapus (soft delete).
            </div>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-medium px-5 py-2.5 rounded-lg mx-1 transition',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1 transition',
            },
            buttonsStyling: false,
        }).then(async (r) => {
            if (!r.isConfirmed) return;

            const url = buildRoute(DATA.routes.destroy, DATA.activeTab, id);
            const res = await apiCall('DELETE', url);

            if (res.ok) {
                const warnings = res.data.warnings || [];
                if (warnings.length > 0) {
                    toastInfo(warnings.join(' | '));
                }
                toastSuccess(res.data.message || 'Data berhasil dihapus');
                switchTab(DATA.activeTab);
            } else {
                toastError(res.data.message || 'Gagal menghapus');
            }
        });
    };

    // ============ INIT ============
    document.addEventListener('DOMContentLoaded', () => {
        document.getElementById('btn-add-label').textContent =
            'Tambah ' + DATA.tabConfig[DATA.activeTab].label;

        renderTableHead();
        renderTableBody();
    });
})();