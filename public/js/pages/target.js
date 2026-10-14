// SIKAP - Target Page (with Tahun Lock)
// Versi aman XSS

(function () {
    const DATA = window.TARGET_DATA;
    let currentTahun = DATA.initialTahun;
    let targets = [];
    let references = {};
    const openDetails = new Set();

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

    function monthName(m) {
        return ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][m - 1] || '-';
    }

    function isTahunArsip() {
        const tahunBerjalan = DATA.tahunBerjalan || new Date().getFullYear();
        return Number(currentTahun) !== Number(tahunBerjalan);
    }

    function updateArsipBanner() {
        const banner = document.getElementById('arsip-banner');
        const tahunEl = document.getElementById('arsip-tahun');
        const btnAdd = document.getElementById('btn-add-target');
        if (!banner) return;

        const isArsip = isTahunArsip();

        if (isArsip) {
            banner.classList.remove('hidden');
            if (tahunEl) tahunEl.textContent = currentTahun;
            if (btnAdd) btnAdd.style.display = 'none';
        } else {
            banner.classList.add('hidden');
            if (btnAdd) btnAdd.style.display = '';
        }
        if (window.lucide) lucide.createIcons();
    }

    async function loadData() {
        const url = DATA.routes.data + '?tahun=' + currentTahun;
        const res = await apiCall('GET', url);

        if (!res.ok) {
            toastError('Gagal memuat data target');
            return;
        }

        targets = res.data.targets || [];
        references = res.data.references || {};

        updateArsipBanner();
        renderStats(res.data.total_tahunan, targets.length);
        renderTable();
    }

    function renderStats(totalTahunan, jumlahBulan) {
        const rata = jumlahBulan > 0 ? Math.round(totalTahunan / jumlahBulan) : 0;
        document.getElementById('stat-cards').innerHTML = `
            ${statCard({ label: 'Total Target ' + currentTahun, value: rp(totalTahunan), icon: 'target', color: 'blue' })}
            ${statCard({ label: 'Jumlah Bulan Terdaftar', value: jumlahBulan + ' bulan', icon: 'calendar', color: 'emerald' })}
            ${statCard({ label: 'Rata-rata per Bulan', value: rp(rata), icon: 'trending-up', color: 'amber' })}
        `;
        document.getElementById('filter-info').innerHTML =
            `${jumlahBulan} bulan terdaftar · Total: <b class="text-blue-700">${rp(totalTahunan)}</b>`;
        if (window.lucide) lucide.createIcons();
    }

    function renderTable() {
        const tbody = document.getElementById('target-tbody');
        const tfoot = document.getElementById('target-tfoot');

        if (targets.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-10 text-gray-400">
                        <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                        <p class="text-sm">Belum ada target untuk tahun ${currentTahun}</p>
                        <p class="text-xs mt-1">Klik "Tambah Target Bulanan" untuk memulai</p>
                    </td>
                </tr>`;
            tfoot.innerHTML = '';
            if (window.lucide) lucide.createIcons();
            return;
        }

        tbody.innerHTML = targets.map((t, i) => {
            const isOpen = openDetails.has(t.id);
            const detailCount = t.details.length;

            const grouped = {};
            t.details.forEach(d => {
                const progId = d.program_id;
                if (!grouped[progId]) {
                    grouped[progId] = { program: d.program, subs: {}, total: 0 };
                }
                const subId = d.sub_kegiatan_id || 0;
                if (!grouped[progId].subs[subId]) {
                    grouped[progId].subs[subId] = { sub_kegiatan: d.sub_kegiatan, komponens: [], total: 0 };
                }
                grouped[progId].subs[subId].komponens.push(d);
                grouped[progId].subs[subId].total += d.nominal;
                grouped[progId].total += d.nominal;
            });

            const programBlocksHtml = Object.values(grouped).map(g => {
                const prog = g.program;
                const progTotal = g.total;
                const progPct = t.total > 0 ? ((progTotal / t.total) * 100).toFixed(1) : 0;

                const subBlocksHtml = Object.values(g.subs).map(s => {
                    const sub = s.sub_kegiatan;
                    const subTotal = s.total;
                    const subPct = t.total > 0 ? ((subTotal / t.total) * 100).toFixed(1) : 0;

                    const komponensHtml = s.komponens.map(d => {
                        const kom = d.komponen_belanja;
                        const pct = t.total > 0 ? ((d.nominal / t.total) * 100).toFixed(1) : 0;
                        return `
                            <tr class="border-b border-gray-100 last:border-0 hover:bg-blue-50/30">
                                <td class="py-2.5 pl-14 pr-3 text-left">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-md bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0">
                                            <i data-lucide="receipt" class="w-3 h-3"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-medium text-gray-800 truncate">${escapeHtml(kom ? kom.nama : '-')}</p>
                                            <p class="text-[9px] text-gray-400 font-mono">${escapeHtml(kom ? kom.kode : '-')}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-2.5 px-3 text-right text-xs font-semibold text-emerald-700 whitespace-nowrap">${rp(d.nominal)}</td>
                                <td class="py-2.5 pl-3 pr-4 text-right text-[10px] text-gray-500 w-16">${pct}%</td>
                            </tr>`;
                    }).join('');

                    return `
                        <div class="mb-3 last:mb-0">
                            <div class="flex items-center gap-2 px-3 py-2 bg-indigo-50/70 rounded-t-lg border border-indigo-100">
                                <div class="w-6 h-6 rounded-md bg-indigo-100 text-indigo-700 flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="git-branch" class="w-3 h-3"></i>
                                </div>
                                <span class="text-[10px] font-mono font-bold text-indigo-700 bg-white px-1.5 py-0.5 rounded">${escapeHtml(sub ? sub.kode : '-')}</span>
                                <span class="text-xs font-semibold text-gray-800 flex-1 truncate">${escapeHtml(sub ? sub.nama : 'Tanpa Sub-Kegiatan')}</span>
                                <span class="text-xs font-bold text-indigo-700 whitespace-nowrap">${rp(subTotal)}</span>
                                <span class="text-[10px] text-gray-500 w-12 text-right">${subPct}%</span>
                            </div>
                            <div class="bg-white border border-t-0 border-indigo-100 rounded-b-lg overflow-hidden">
                                <table class="w-full"><tbody>${komponensHtml}</tbody></table>
                            </div>
                        </div>`;
                }).join('');

                return `
                    <div class="mb-4 last:mb-0">
                        <div class="flex items-center gap-2.5 px-3 py-2.5 bg-blue-100/80 rounded-lg border border-blue-200 mb-2">
                            <div class="w-7 h-7 rounded-md bg-blue-700 text-white flex items-center justify-center flex-shrink-0">
                                <i data-lucide="folder-kanban" class="w-3.5 h-3.5"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-mono font-bold text-blue-700 bg-white px-1.5 py-0.5 rounded">${escapeHtml(prog ? prog.kode : '???')}</span>
                                    <span class="text-xs font-bold text-blue-900 truncate">${escapeHtml(prog ? prog.nama : 'Tidak diketahui')}</span>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-blue-800 whitespace-nowrap">${rp(progTotal)}</span>
                            <span class="text-[10px] text-blue-700 w-12 text-right">${progPct}%</span>
                        </div>
                        <div class="pl-3">${subBlocksHtml}</div>
                    </div>`;
            }).join('');

            return `
                <tr class="hover:bg-blue-50/40 transition" data-target-id="${t.id}" onclick="toggleDetail(${t.id})">
                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600 text-xs font-bold">${i + 1}</span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <span class="font-semibold text-gray-800">${escapeHtml(monthName(t.bulan))}</span>
                            <i data-lucide="chevron-down" class="chevron-icon w-3.5 h-3.5 text-gray-400 flex-shrink-0 ${isOpen ? 'rotate' : ''}"></i>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex items-center gap-1.5 text-xs bg-purple-50 text-purple-700 font-semibold px-3 py-1.5 rounded-full">
                            <i data-lucide="layers" class="w-3 h-3"></i> ${detailCount} item
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="font-bold text-blue-700 whitespace-nowrap">${rp(t.total)}</span>
                    </td>
                    <td class="px-5 py-4" onclick="event.stopPropagation()">
                        <div class="flex items-center justify-center gap-1">
                            <button type="button" onclick="toggleDetail(${t.id})"
                                class="text-purple-600 hover:bg-purple-100 p-2 rounded-lg transition" title="Lihat Detail">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                            ${t.editable ? `
                                <button type="button" onclick="openTargetForm(${t.id})"
                                    class="text-blue-700 hover:bg-blue-100 p-2 rounded-lg transition" title="Edit">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                                <button type="button" onclick="deleteTarget(${t.id})"
                                    class="text-red-600 hover:bg-red-100 p-2 rounded-lg transition" title="Hapus">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            ` : `
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-gray-500 bg-gray-100 px-2 py-1 rounded" title="Tahun arsip - read only">
                                    <i data-lucide="lock" class="w-3 h-3"></i> ARSIP
                                </span>
                            `}
                        </div>
                    </td>
                </tr>

                <tr data-expand-for="${t.id}" class="${isOpen ? '' : 'hidden'} bg-gradient-to-r from-blue-50/40 to-indigo-50/30">
                    <td colspan="5" class="p-0">
                        <div class="detail-expand ${isOpen ? 'open' : ''}">
                            <div class="px-6 py-4">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="layers" class="w-4 h-4 text-blue-700"></i>
                                        <span class="text-xs font-bold text-blue-900 uppercase tracking-wide">
                                            Rincian Target - ${escapeHtml(monthName(t.bulan))} ${escapeHtml(t.tahun)}
                                        </span>
                                    </div>
                                    <span class="text-xs text-gray-500">Total: ${rp(t.total)}</span>
                                </div>
                                ${programBlocksHtml || '<p class="text-sm text-gray-400 text-center py-4">Tidak ada komponen</p>'}
                            </div>
                        </div>
                    </td>
                </tr>`;
        }).join('');

        const totalTahunan = targets.reduce((s, t) => s + t.total, 0);
        tfoot.innerHTML = `
            <tr class="bg-gradient-to-r from-blue-50 to-indigo-50 border-t-2 border-blue-100">
                <td colspan="3" class="px-5 py-4 text-center font-bold text-blue-900 text-sm">
                    TOTAL TAHUNAN ${currentTahun}
                </td>
                <td class="px-5 py-4 text-center font-bold text-blue-800 text-base whitespace-nowrap">
                    ${rp(totalTahunan)}
                </td>
                <td></td>
            </tr>`;

        if (window.lucide) lucide.createIcons();
    }

    window.toggleDetail = function (targetId) {
        const row = document.querySelector(`tr[data-target-id="${targetId}"]`);
        const expandRow = document.querySelector(`tr[data-expand-for="${targetId}"]`);
        if (!row || !expandRow) return;

        const container = expandRow.querySelector('.detail-expand');
        const chevron = row.querySelector('.chevron-icon');
        const isOpen = openDetails.has(targetId);

        if (isOpen) {
            openDetails.delete(targetId);
            container.classList.remove('open');
            if (chevron) chevron.classList.remove('rotate');
            setTimeout(() => {
                if (!openDetails.has(targetId)) expandRow.classList.add('hidden');
            }, 350);
        } else {
            openDetails.add(targetId);
            expandRow.classList.remove('hidden');
            void container.offsetHeight;
            container.classList.add('open');
            if (chevron) chevron.classList.add('rotate');
        }
    };

    window.changeTahun = function (y) {
        currentTahun = Number(y);
        openDetails.clear();
        const url = new URL(window.location);
        url.searchParams.set('tahun', currentTahun);
        window.history.pushState({}, '', url);
        loadData();
    };

    function buildFormHeader(target) {
        const years = [2026, 2025, 2024];
        return `
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Tahun</label>
                    <select id="f-tahun" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white">
                        ${years.map(y => `<option value="${y}" ${(target ? target.tahun : currentTahun) === y ? 'selected' : ''}>${y}</option>`).join('')}
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Bulan</label>
                    <select id="f-bulan" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white">
                        ${Array.from({ length: 12 }, (_, i) => i + 1).map(m =>
                            `<option value="${m}" ${target && target.bulan === m ? 'selected' : ''}>${escapeHtml(monthName(m))}</option>`
                        ).join('')}
                    </select>
                </div>
            </div>`;
    }

    function buildDetailRow(detail, idx) {
        const programs = references.programs || [];
        const komponens = references.komponen_belanjas || [];
        const subs = references.sub_kegiatans || [];

        const programId = detail?.program_id || '';
        const subId = detail?.sub_kegiatan_id || '';
        const kompId = detail?.komponen_belanja_id || '';
        const nominal = detail?.nominal || 0;

        const subOptions = subs.filter(s => s.program_id === programId);

        return `
            <div class="form-detail-row detail-item" data-idx="${idx}" style="border:1px solid #e5e7eb;border-radius:8px;padding:12px;background:#fff;">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-2">
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase font-semibold block mb-1">Program</label>
                        <select class="f-program w-full px-2.5 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="">- Pilih Program -</option>
                            ${programs.map(p => `<option value="${escapeHtml(p.id)}" ${programId == p.id ? 'selected' : ''}>${escapeHtml(p.kode)} - ${escapeHtml(p.nama)}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase font-semibold block mb-1">Sub-Kegiatan</label>
                        <select class="f-sub w-full px-2.5 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="">- Pilih Sub-Kegiatan -</option>
                            ${subOptions.map(s => `<option value="${escapeHtml(s.id)}" ${subId == s.id ? 'selected' : ''}>${escapeHtml(s.kode)} - ${escapeHtml(s.nama)}</option>`).join('')}
                        </select>
                    </div>
                </div>
                <div class="grid gap-2" style="grid-template-columns: 1fr 180px 40px; align-items: end;">
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase font-semibold block mb-1">Komponen Belanja</label>
                        <select class="f-komponen w-full px-2.5 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="">- Pilih Komponen -</option>
                            ${komponens.map(k => `<option value="${escapeHtml(k.id)}" ${kompId == k.id ? 'selected' : ''}>${escapeHtml(k.kode)} - ${escapeHtml(k.nama)}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase font-semibold block mb-1">Nominal</label>
                        <div class="relative">
                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-[10px] font-semibold pointer-events-none">Rp</span>
                            <input type="text" inputmode="numeric" class="f-nominal input-nominal w-full pl-7 pr-2 py-2 border border-gray-200 rounded-lg text-xs text-right font-semibold focus:ring-2 focus:ring-blue-500"
                                value="${nominal ? Number(nominal).toLocaleString('id-ID') : ''}" placeholder="0" />
                        </div>
                    </div>
                    <button type="button" onclick="removeDetailRow(this)"
                        class="text-red-500 hover:bg-red-50 p-2 rounded-lg transition flex-shrink-0" title="Hapus baris">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>`;
    }

    function buildForm(target) {
        const details = target && target.details.length > 0
            ? target.details
            : [{ program_id: '', sub_kegiatan_id: '', komponen_belanja_id: '', nominal: 0 }];

        return `
            <div class="text-left space-y-4 text-sm mt-2">
                ${buildFormHeader(target)}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block font-semibold text-gray-700 text-xs uppercase tracking-wide">
                            Komponen Belanja <span class="text-gray-400 normal-case font-normal">(${details.length} item)</span>
                        </label>
                        <button type="button" onclick="addDetailRow()"
                            class="text-xs bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold px-2.5 py-1 rounded-md transition flex items-center gap-1">
                            <i data-lucide="plus" class="w-3 h-3"></i> Tambah Baris
                        </button>
                    </div>
                    <div id="detail-container" class="space-y-2 max-h-96 overflow-y-auto pr-1">
                        ${details.map((d, i) => buildDetailRow(d, i)).join('')}
                    </div>
                </div>
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-lg p-3 flex items-center justify-between">
                    <span class="text-sm font-semibold text-blue-900">Total Target Bulanan</span>
                    <span id="f-total" class="text-lg font-bold text-blue-800">Rp 0</span>
                </div>
            </div>`;
    }

    function collectForm() {
        const tahun = Number(document.getElementById('f-tahun').value);
        const bulan = Number(document.getElementById('f-bulan').value);
        const details = [];

        document.querySelectorAll('#detail-container .form-detail-row').forEach((row, idx) => {
            const program_id = Number(row.querySelector('.f-program').value) || null;
            const sub_kegiatan_id = Number(row.querySelector('.f-sub').value) || null;
            const komponen_belanja_id = Number(row.querySelector('.f-komponen').value) || null;
            const nominalRaw = row.querySelector('.f-nominal').value;
            const nominal = Number(nominalRaw.replace(/\D/g, '')) || 0;

            if (program_id && komponen_belanja_id && nominal > 0) {
                details.push({ program_id, sub_kegiatan_id, komponen_belanja_id, nominal });
            }
        });

        return { tahun, bulan, details };
    }

    function attachFormEvents() {
        document.querySelectorAll('#detail-container .form-detail-row').forEach(row => {
            const progEl = row.querySelector('.f-program');
            const subEl = row.querySelector('.f-sub');
            const nomEl = row.querySelector('.f-nominal');

            if (progEl && !progEl.dataset.bound) {
                progEl.addEventListener('change', () => {
                    const pid = Number(progEl.value);
                    const subs = (references.sub_kegiatans || []).filter(s => s.program_id === pid);
                    subEl.innerHTML = '<option value="">- Pilih Sub-Kegiatan -</option>' +
                        subs.map(s => `<option value="${escapeHtml(s.id)}">${escapeHtml(s.kode)} - ${escapeHtml(s.nama)}</option>`).join('');
                });
                progEl.dataset.bound = '1';
            }

            if (nomEl && !nomEl.dataset.bound) {
                attachNumberFormat(nomEl);
                nomEl.addEventListener('input', updateFormTotal);
                nomEl.dataset.bound = '1';
            }
        });

        updateFormTotal();
        if (window.lucide) lucide.createIcons();
    }

    function updateFormTotal() {
        const el = document.getElementById('f-total');
        if (!el) return;
        let total = 0;
        document.querySelectorAll('#detail-container .f-nominal').forEach(i => {
            total += Number(i.value.replace(/\D/g, '')) || 0;
        });
        el.textContent = rp(total);
    }

    window.addDetailRow = function () {
        const container = document.getElementById('detail-container');
        if (!container) return;
        const idx = container.children.length;
        const html = buildDetailRow(null, idx);
        container.insertAdjacentHTML('beforeend', html);
        attachFormEvents();
    };

    window.removeDetailRow = function (btn) {
        const row = btn.closest('.form-detail-row');
        if (!row) return;
        const container = document.getElementById('detail-container');
        if (container.children.length === 1) {
            toastError('Minimal 1 baris');
            return;
        }
        row.classList.add('removing');
        setTimeout(() => {
            row.remove();
            updateFormTotal();
        }, 150);
    };

    window.openTargetForm = function (id) {
        if (isTahunArsip()) {
            toastError('Tahun ' + currentTahun + ' sudah dikunci (arsip). Tidak bisa edit.');
            return;
        }

        const target = id ? targets.find(t => t.id === id) : null;

        if (target && !target.editable) {
            toastError('Tahun ' + target.tahun + ' sudah dikunci (arsip).');
            return;
        }

        Swal.fire({
            title: target ? 'Edit Target Bulanan' : 'Tambah Target Bulanan',
            width: 900,
            html: buildForm(target),
            showCancelButton: true,
            confirmButtonText: target ? 'Update' : 'Simpan',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-blue-700 hover:bg-blue-800 text-white font-medium px-5 py-2.5 rounded-lg mx-1 transition',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1 transition',
                popup: 'rounded-2xl',
            },
            buttonsStyling: false,
            didOpen: () => { attachFormEvents(); },
            preConfirm: () => {
                const data = collectForm();
                if (data.details.length === 0) {
                    Swal.showValidationMessage('Minimal 1 detail dengan nominal > 0');
                    return false;
                }
                return data;
            }
        }).then(async (r) => {
            if (!r.isConfirmed) return;

            const url = target ? buildRoute(DATA.routes.update, target.id) : DATA.routes.store;
            const method = target ? 'PUT' : 'POST';
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

    window.deleteTarget = function (id) {
        const t = targets.find(x => x.id === id);
        if (!t) return;

        if (!t.editable) {
            toastError('Tahun ' + t.tahun + ' sudah dikunci (arsip). Tidak bisa hapus.');
            return;
        }

        Swal.fire({
            title: 'Hapus Target?',
            html: `
                <div class="text-sm text-gray-600 text-left">
                    <p>Anda akan menghapus <b>${escapeHtml(monthName(t.bulan))} ${escapeHtml(t.tahun)}</b>.</p>
                    <div class="bg-red-50 border border-red-200 rounded p-2 mt-2 text-xs">
                        <b class="text-red-700">⚠ Peringatan:</b>
                        <p class="text-red-600 mt-1">Data yang dihapus tidak bisa dikembalikan.</p>
                    </div>
                </div>`,
            icon: 'warning',
            iconColor: '#dc2626',
            showCancelButton: true,
            confirmButtonText: '<i class="lucide-trash-2"></i> Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-2xl shadow-2xl',
                title: 'text-2xl font-bold text-red-700',
                htmlContainer: 'text-sm',
                confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-2.5 rounded-lg mx-1 transition',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-6 py-2.5 rounded-lg mx-1 transition',
            },
            buttonsStyling: false,
            reverseButtons: true,
            focusCancel: true,
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