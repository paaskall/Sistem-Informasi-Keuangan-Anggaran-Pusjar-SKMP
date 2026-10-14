// ============================================================
// SIKAP - PNBP Page (Style Target: Tabel + Accordion)
// Versi aman XSS
// ============================================================

(function () {
    const DATA = window.PNBP_DATA;
    let currentTahun = DATA.initialTahun;
    let pnbp = null;
    const openMonths = new Set();

    function buildRoute(template, value) {
        return template.replace('__DID__', value).replace('__ID__', value);
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

    function formatNumberInput(v) {
        if (!v && v !== 0) return '';
        const numeric = String(v).replace(/\D/g, '');
        if (numeric === '') return '';
        return Number(numeric).toLocaleString('id-ID');
    }

    function parseNumberInput(v) {
        if (!v) return 0;
        const numeric = String(v).replace(/\D/g, '');
        return numeric === '' ? 0 : Number(numeric);
    }

    function attachNumberFormat(input) {
        if (!input) return;
        input.addEventListener('input', (e) => {
            const cursorPos = e.target.selectionStart;
            const oldLength = e.target.value.length;
            e.target.value = formatNumberInput(e.target.value);
            const newLength = e.target.value.length;
            const newPos = cursorPos + (newLength - oldLength);
            try { e.target.setSelectionRange(newPos, newPos); } catch(_) {}
        });
        input.addEventListener('keypress', (e) => {
            if (!/[0-9]/.test(e.key) &&
                !['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'Enter'].includes(e.key)) {
                e.preventDefault();
            }
        });
    }

    function monthName(m) {
        return ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'][m - 1] || '-';
    }

    async function loadData() {
        const url = DATA.routes.data + '?tahun=' + currentTahun;
        const res = await apiCall('GET', url);

        if (!res.ok) {
            toastError('Gagal memuat data PNBP');
            return;
        }

        pnbp = res.data.pnbp;

        if (!pnbp) {
            document.getElementById('pnbp-summary').innerHTML = '';
            document.getElementById('pnbp-tbody').innerHTML = '';
            document.getElementById('pnbp-tfoot').innerHTML = '';
            document.getElementById('pnbp-empty').classList.remove('hidden');
            document.getElementById('empty-tahun').textContent = currentTahun;
            if (window.lucide) lucide.createIcons();
            return;
        }

        document.getElementById('pnbp-empty').classList.add('hidden');
        renderSummary();
        renderTable();
    }

    // ============ SUMMARY CARD ============
    function renderSummary() {
        const p = pnbp;
        const capaian = p.capaian;
        const capColor = capaian >= 100 ? 'text-green-700'
                        : capaian >= 70 ? 'text-blue-700'
                        : capaian >= 40 ? 'text-amber-700'
                        : 'text-red-700';

        const barColor = capaian >= 100 ? 'from-green-500 to-green-400'
                        : capaian >= 70 ? 'from-blue-600 to-blue-400'
                        : capaian >= 40 ? 'from-amber-500 to-amber-400'
                        : 'from-red-500 to-red-400';

        document.getElementById('pnbp-summary').innerHTML = `
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-700 to-emerald-600 px-6 py-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="receipt" class="w-5 h-5 text-white"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-white font-bold text-base">Total Realisasi Penerimaan PNBP ${escapeHtml(p.tahun)}</h3>
                        <p class="text-emerald-100 text-xs mt-0.5">${escapeHtml(p.unit || '-')}</p>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-blue-700 flex items-center justify-center">
                                    <i data-lucide="target" class="w-4 h-4 text-white"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Target</p>
                                <p class="text-xl sm:text-2xl font-bold text-blue-800">${rp(p.total_target)}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-4 md:border-l md:border-gray-100 md:pl-6">
                            <div class="w-14 h-14 rounded-2xl bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                <div class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center">
                                    <i data-lucide="trending-up" class="w-4 h-4 text-white"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Realisasi</p>
                                <p class="text-xl sm:text-2xl font-bold text-emerald-700">${rp(p.total_realisasi)}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-gray-600 uppercase tracking-wider">Capaian</span>
                            <span class="text-2xl font-bold ${capColor}">${capaian}%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-6 overflow-hidden shadow-inner">
                            <div class="bg-gradient-to-r ${barColor} h-full rounded-full transition-all duration-1000 ease-out flex items-center justify-end pr-3"
                                 style="width: ${Math.min(capaian, 100)}%">
                                ${capaian >= 15 ? `<span class="text-white text-[10px] font-bold">${capaian}%</span>` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        if (window.lucide) lucide.createIcons();
    }

    // ============ TABLE ============
    function renderTable() {
        const tbody = document.getElementById('pnbp-tbody');
        const tfoot = document.getElementById('pnbp-tfoot');
        const perBulan = pnbp.per_bulan || [];

        if (perBulan.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center py-10 text-gray-400">
                        <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                        <p class="text-sm">Belum ada data PNBP untuk tahun ${escapeHtml(currentTahun)}</p>
                    </td>
                </tr>`;
            tfoot.innerHTML = '';
            if (window.lucide) lucide.createIcons();
            return;
        }

        tbody.innerHTML = perBulan.map((b, i) => {
            const isOpen = openMonths.has(b.bulan);
            const capColor = b.capaian >= 100 ? 'text-green-700 bg-green-50'
                            : b.capaian >= 70 ? 'text-blue-700 bg-blue-50'
                            : b.capaian >= 40 ? 'text-amber-700 bg-amber-50'
                            : 'text-red-700 bg-red-50';

            const totalSub = b.kategoris.reduce((s, k) => s + (k.children?.length || 0), 0);

            const detailRowsHtml = b.kategoris.map(kat => {
                const katCapColor = kat.capaian >= 100 ? 'text-green-700 bg-green-50'
                                    : kat.capaian >= 70 ? 'text-blue-700 bg-blue-50'
                                    : kat.capaian >= 40 ? 'text-amber-700 bg-amber-50'
                                    : 'text-red-700 bg-red-50';

                const icon = kat.has_children ? 'graduation-cap' : 'building-2';

                // ✅ Pakai data-attribute, bukan onclick string interpolation
                let html = `
                    <tr class="border-b border-gray-100 bg-emerald-50/40 hover:bg-emerald-100/50 transition cursor-pointer"
                        data-detail-id="${kat.id}"
                        data-nama="${escapeHtml(kat.nama)}"
                        data-bulan="${b.bulan}"
                        data-target="${kat.target}"
                        data-realisasi="${kat.realisasi}"
                        data-peserta-target="0"
                        data-peserta-realisasi="0"
                        data-keterangan=""
                        data-is-child="false"
                        onclick="event.stopPropagation(); openDetailFormFromRow(this)">
                        <td class="py-3 pl-4 pr-3 text-left" colspan="1">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center flex-shrink-0">
                                    <i data-lucide="${icon}" class="w-3.5 h-3.5"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-800">${escapeHtml(kat.nama)}</span>
                                ${kat.has_children ? `<span class="text-[9px] font-bold bg-emerald-200 text-emerald-800 px-1.5 py-0.5 rounded-full">${kat.children.length} sub</span>` : ''}
                            </div>
                        </td>
                        <td class="py-3 px-2 text-right text-xs font-bold text-blue-700 whitespace-nowrap">${rp(kat.target)}</td>
                        <td class="py-3 px-2 text-right text-xs font-bold text-emerald-700 whitespace-nowrap">${rp(kat.realisasi)}</td>
                        <td class="py-3 px-2 text-right">
                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded ${katCapColor}">${kat.capaian}%</span>
                        </td>
                        <td class="py-3 pl-2 pr-4 text-right">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5 text-blue-600 inline"></i>
                        </td>
                    </tr>
                `;

                if (kat.has_children) {
                    html += kat.children.map(c => {
                        const cCapColor = c.capaian >= 100 ? 'text-green-700 bg-green-50'
                                        : c.capaian >= 70 ? 'text-blue-700 bg-blue-50'
                                        : c.capaian >= 40 ? 'text-amber-700 bg-amber-50'
                                        : 'text-red-700 bg-red-50';

                        const pesertaPct = c.peserta_target > 0
                            ? Math.round((c.peserta_realisasi / c.peserta_target) * 100)
                            : 0;

                        return `
                            <tr class="border-b border-gray-100 hover:bg-blue-50/40 transition cursor-pointer"
                                data-detail-id="${c.id}"
                                data-nama="${escapeHtml(c.nama)}"
                                data-bulan="${b.bulan}"
                                data-target="${c.target}"
                                data-realisasi="${c.realisasi}"
                                data-peserta-target="${c.peserta_target}"
                                data-peserta-realisasi="${c.peserta_realisasi}"
                                data-keterangan="${escapeHtml(c.nama)}"
                                data-is-child="true"
                                onclick="event.stopPropagation(); openDetailFormFromRow(this)">
                                <td class="py-2.5 pl-12 pr-3 text-left">
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-300 font-mono">├─</span>
                                        <span class="text-xs font-medium text-gray-700">${escapeHtml(c.nama)}</span>
                                    </div>
                                </td>
                                <td class="py-2.5 px-2 text-right text-xs text-blue-600 whitespace-nowrap">${rp(c.target)}</td>
                                <td class="py-2.5 px-2 text-right text-xs text-emerald-600 whitespace-nowrap">${rp(c.realisasi)}</td>
                                <td class="py-2.5 px-2 text-right">
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded ${cCapColor}">${c.capaian}%</span>
                                </td>
                                <td class="py-2.5 pl-2 pr-4 text-right">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold ${pesertaPct >= 100 ? 'text-green-700 bg-green-50' : pesertaPct >= 70 ? 'text-blue-700 bg-blue-50' : 'text-amber-700 bg-amber-50'} px-1.5 py-0.5 rounded-full whitespace-nowrap">
                                        👥 ${c.peserta_realisasi}/${c.peserta_target}
                                    </span>
                                </td>
                            </tr>
                        `;
                    }).join('');
                }

                return html;
            }).join('');

            return `
                <tr class="hover:bg-emerald-50/40 transition cursor-pointer"
                    data-month-id="${b.bulan}"
                    onclick="toggleDetail(${b.bulan})">
                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600 text-xs font-bold">${i + 1}</span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <span class="font-semibold text-gray-800">${escapeHtml(b.bulan_nama)}</span>
                            <i data-lucide="chevron-down" class="chevron-icon w-3.5 h-3.5 text-gray-400 flex-shrink-0 ${isOpen ? 'rotate' : ''}"></i>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex items-center gap-1.5 text-xs bg-purple-50 text-purple-700 font-semibold px-3 py-1.5 rounded-full">
                            <i data-lucide="layers" class="w-3 h-3"></i> ${b.kategoris.length} kategori · ${totalSub} sub
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="font-bold text-blue-700 whitespace-nowrap">${rp(b.total_target)}</span>
                        <p class="text-[10px] text-gray-500 mt-0.5">Capaian: ${b.capaian}%</p>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <button type="button" onclick="event.stopPropagation(); toggleDetail(${b.bulan})"
                                class="text-purple-600 hover:bg-purple-100 p-2 rounded-lg transition" title="Lihat Detail">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                </tr>

                <tr data-expand-for="${b.bulan}" class="${isOpen ? '' : 'hidden'} bg-gradient-to-r from-blue-50/40 to-emerald-50/30">
                    <td colspan="5" class="p-0">
                        <div class="detail-expand ${isOpen ? 'open' : ''}">
                            <div class="px-6 py-4">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="layers" class="w-4 h-4 text-emerald-700"></i>
                                        <span class="text-xs font-bold text-emerald-900 uppercase tracking-wide">
                                            Rincian PNBP - ${escapeHtml(b.bulan_nama)} ${escapeHtml(pnbp.tahun)}
                                        </span>
                                    </div>
                                    <span class="text-xs text-gray-500">Total: ${rp(b.total_realisasi)}</span>
                                </div>

                                <div class="overflow-x-auto bg-white rounded-lg border border-emerald-100">
                                    <table class="w-full text-xs">
                                        <thead class="bg-emerald-50/70 text-emerald-700 text-[10px] uppercase">
                                            <tr>
                                                <th class="text-left pl-4 pr-3 py-2 font-bold">Sumber Penerimaan</th>
                                                <th class="text-right px-2 py-2 font-bold w-32">Target</th>
                                                <th class="text-right px-2 py-2 font-bold w-32">Realisasi</th>
                                                <th class="text-right px-2 py-2 font-bold w-16">Cap</th>
                                                <th class="text-right pl-2 pr-4 py-2 font-bold w-28">Peserta</th>
                                            </tr>
                                        </thead>
                                        <tbody>${detailRowsHtml}</tbody>
                                        <tfoot class="bg-gradient-to-r from-blue-50 to-emerald-50 border-t-2 border-emerald-200">
                                            <tr>
                                                <td class="py-3 pl-4 pr-3 font-bold text-emerald-900 text-xs uppercase">Total ${escapeHtml(b.bulan_nama)}</td>
                                                <td class="py-3 px-2 text-right font-bold text-blue-800 text-xs whitespace-nowrap">${rp(b.total_target)}</td>
                                                <td class="py-3 px-2 text-right font-bold text-emerald-800 text-xs whitespace-nowrap">${rp(b.total_realisasi)}</td>
                                                <td class="py-3 px-2 text-right text-xs font-bold text-amber-700">${b.capaian}%</td>
                                                <td class="py-3 pl-2 pr-4"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        tfoot.innerHTML = `
            <tr class="bg-gradient-to-r from-emerald-50 to-blue-50 border-t-2 border-emerald-100">
                <td colspan="3" class="px-5 py-4 text-center font-bold text-emerald-900 text-sm">
                    TOTAL TAHUNAN ${escapeHtml(pnbp.tahun)}
                </td>
                <td class="px-5 py-4 text-center font-bold text-emerald-800 text-base whitespace-nowrap">
                    ${rp(pnbp.total_target)}
                </td>
                <td></td>
            </tr>`;

        if (window.lucide) lucide.createIcons();
    }

    // ============ TOGGLE DETAIL ============
    window.toggleDetail = function (bulanId) {
        const row = document.querySelector(`tr[data-month-id="${bulanId}"]`);
        const expandRow = document.querySelector(`tr[data-expand-for="${bulanId}"]`);
        if (!row || !expandRow) return;

        const container = expandRow.querySelector('.detail-expand');
        const chevron = row.querySelector('.chevron-icon');
        const isOpen = openMonths.has(bulanId);

        if (isOpen) {
            openMonths.delete(bulanId);
            container.classList.remove('open');
            if (chevron) chevron.classList.remove('rotate');
            setTimeout(() => {
                if (!openMonths.has(bulanId)) expandRow.classList.add('hidden');
            }, 350);
        } else {
            openMonths.add(bulanId);
            expandRow.classList.remove('hidden');
            void container.offsetHeight;
            container.classList.add('open');
            if (chevron) chevron.classList.add('rotate');
        }
    };

    // ============ OPEN DETAIL FORM FROM ROW (aman XSS) ============
    window.openDetailFormFromRow = function (row) {
        const ds = row.dataset;
        window.openDetailForm(
            Number(ds.detailId),
            ds.nama || '',
            Number(ds.bulan),
            Number(ds.target) || 0,
            Number(ds.realisasi) || 0,
            Number(ds.pesertaTarget) || 0,
            Number(ds.pesertaRealisasi) || 0,
            ds.keterangan || null,
            ds.isChild === 'true'
        );
    };

    // ============ MODAL FORM DETAIL ============
    window.openDetailForm = function (detailId, nama, bulan, target, realisasi, pesertaTarget, pesertaRealisasi, keterangan, isChild) {
        const bulanNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

        Swal.fire({
            title: isChild ? 'Edit Sub-Kategori' : 'Edit Kategori PNBP',
            width: 560,
            html: `
                <div class="text-left space-y-3 text-sm mt-2">
                    <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-3">
                        <p class="text-[10px] text-emerald-700 uppercase font-bold tracking-wider">${escapeHtml(bulanNames[bulan-1])} ${escapeHtml(currentTahun)}</p>
                        <p class="font-semibold text-emerald-900">${escapeHtml(nama)}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Target (Rp)</label>
                            <div class="relative">
                                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-semibold pointer-events-none">Rp</span>
                                <input id="f-target" type="text" inputmode="numeric"
                                    class="input-nominal w-full pl-8 pr-3 py-2.5 border border-gray-200 rounded-lg text-sm text-right font-semibold focus:ring-2 focus:ring-emerald-500"
                                    value="${formatNumberInput(target)}" placeholder="0" />
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Realisasi (Rp)</label>
                            <div class="relative">
                                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs font-semibold pointer-events-none">Rp</span>
                                <input id="f-realisasi" type="text" inputmode="numeric"
                                    class="input-nominal w-full pl-8 pr-3 py-2.5 border border-gray-200 rounded-lg text-sm text-right font-semibold focus:ring-2 focus:ring-emerald-500"
                                    value="${formatNumberInput(realisasi)}" placeholder="0" />
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Peserta Target</label>
                            <input id="f-peserta-target" type="number" min="0"
                                class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm text-right font-semibold focus:ring-2 focus:ring-emerald-500"
                                value="${pesertaTarget || 0}" />
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Peserta Realisasi</label>
                            <input id="f-peserta-realisasi" type="number" min="0"
                                class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm text-right font-semibold focus:ring-2 focus:ring-emerald-500"
                                value="${pesertaRealisasi || 0}" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Nama / Keterangan</label>
                        <input id="f-keterangan" type="text" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500"
                            value="${escapeHtml(keterangan || '')}" placeholder="Nama sub-kategori (opsional)" />
                    </div>
                </div>
            `,
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: 'Simpan',
            denyButtonText: 'Hapus',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-5 py-2.5 rounded-lg mx-1',
                denyButton: 'bg-red-600 hover:bg-red-700 text-white font-medium px-5 py-2.5 rounded-lg mx-1',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1',
                popup: 'rounded-2xl',
            },
            buttonsStyling: false,
            didOpen: () => {
                attachNumberFormat(document.getElementById('f-target'));
                attachNumberFormat(document.getElementById('f-realisasi'));
                if (window.lucide) lucide.createIcons();
            },
            preConfirm: () => {
                return {
                    target: parseNumberInput(document.getElementById('f-target').value),
                    realisasi: parseNumberInput(document.getElementById('f-realisasi').value),
                    peserta_target: Number(document.getElementById('f-peserta-target').value) || 0,
                    peserta_realisasi: Number(document.getElementById('f-peserta-realisasi').value) || 0,
                    keterangan: document.getElementById('f-keterangan').value.trim() || null,
                };
            }
        }).then(async (r) => {
            if (r.isDenied) {
                const confirmDel = await Swal.fire({
                    title: 'Hapus detail ini?',
                    text: 'Data akan dihapus permanen.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-medium px-5 py-2.5 rounded-lg mx-1',
                        cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1',
                    },
                    buttonsStyling: false,
                });
                if (!confirmDel.isConfirmed) return;

                const url = buildRoute(DATA.routes.destroyDetail, detailId);
                const res = await apiCall('DELETE', url);
                if (res.ok) {
                    toastSuccess(res.data.message);
                    openMonths.add(bulan);
                    loadData();
                } else {
                    toastError(res.data.message || 'Gagal menghapus');
                }
                return;
            }

            if (!r.isConfirmed) return;

            const url = buildRoute(DATA.routes.updateDetail, detailId);
            const res = await apiCall('PUT', url, r.value);

            if (res.ok) {
                toastSuccess(res.data.message);
                openMonths.add(bulan);
                loadData();
            } else {
                const errors = res.data.errors || {};
                const first = Object.values(errors)[0];
                toastError(Array.isArray(first) ? first[0] : (res.data.message || 'Gagal menyimpan'));
            }
        });
    };

    // ============ FORM PNBP BARU ============
    window.openPnbpForm = function () {
        const currentYear = new Date().getFullYear();
        const yearOptions = [currentYear + 1, currentYear, currentYear - 1, currentYear - 2, currentYear - 3];

        Swal.fire({
            title: 'Buat Data PNBP Baru',
            width: 480,
            html: `
                <div class="text-left space-y-3 text-sm mt-2">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Tahun <span class="text-red-500">*</span></label>
                        <select id="f-tahun" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 bg-white">
                            ${yearOptions.map(y => `<option value="${y}">${escapeHtml(y)}</option>`).join('')}
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Unit</label>
                        <input id="f-unit" type="text" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500"
                            value="Bagian Keuangan" placeholder="Bagian Keuangan" />
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <p class="text-xs text-blue-700">
                            <b>Info:</b> Data PNBP akan dibuat untuk semua kategori (Diklat, Sewa Sarana & Prasarana) dengan 12 bulan detail.
                        </p>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Buat',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-5 py-2.5 rounded-lg mx-1',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1',
                popup: 'rounded-2xl',
            },
            buttonsStyling: false,
            didOpen: () => { if (window.lucide) lucide.createIcons(); },
            preConfirm: () => {
                return {
                    tahun: Number(document.getElementById('f-tahun').value),
                    unit: document.getElementById('f-unit').value.trim() || 'Bagian Keuangan',
                };
            }
        }).then(async (r) => {
            if (!r.isConfirmed) return;

            const res = await apiCall('POST', DATA.routes.store, r.value);

            if (res.ok) {
                toastSuccess(res.data.message);
                currentTahun = r.value.tahun;
                document.getElementById('filter-tahun').value = currentTahun;
                loadData();
            } else {
                const errors = res.data.errors || {};
                const first = Object.values(errors)[0];
                toastError(Array.isArray(first) ? first[0] : (res.data.message || 'Gagal membuat'));
            }
        });
    };

    // ============ CHANGE TAHUN ============
    window.changeTahun = function (y) {
        currentTahun = Number(y);
        openMonths.clear();
        const url = new URL(window.location);
        url.searchParams.set('tahun', currentTahun);
        window.history.pushState({}, '', url);
        loadData();
    };

    document.addEventListener('DOMContentLoaded', loadData);
})();