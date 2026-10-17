// ============================================================
// SIKAP - Task Bulanan (Kalender + CRUD)
// Versi aman XSS
// ============================================================

(function () {
    const DATA = window.TASKS_DATA;
    const IS_READONLY = DATA.role === 'pimpinan';

    let currentBulan = DATA.initialBulan;
    let currentTahun = DATA.initialTahun;
    let tasks = [];
    let calendarData = {};
    let assignableUsers = [];
    let selectedDate = null;
    let filterStatus = '';

    const BULAN_NAMA = [
        '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

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

    // ============================================================
    // LOAD DATA
    // ============================================================
    async function loadAll() {
        updateMonthLabel();
        await Promise.all([loadTasks(), loadCalendar()]);
    }

    async function loadTasks() {
        const url = `${DATA.routes.data}?bulan=${currentBulan}&tahun=${currentTahun}`;
        const res = await apiCall('GET', url);

        if (!res.ok) {
            toastError('Gagal memuat task');
            return;
        }

        tasks = res.data.tasks || [];
        renderTaskList();
        updateStats(res.data.stats || {});
    }

    async function loadCalendar() {
        const url = `${DATA.routes.calendar}?bulan=${currentBulan}&tahun=${currentTahun}`;
        const res = await apiCall('GET', url);

        if (!res.ok) return;

        calendarData = res.data.by_day || {};
        renderCalendar();
    }

    // ============================================================
    // STATS
    // ============================================================
    function updateStats(stats) {
        document.getElementById('stat-belum').textContent = stats.belum || 0;
        document.getElementById('stat-proses').textContent = stats.proses || 0;
        document.getElementById('stat-selesai').textContent = stats.selesai || 0;
        document.getElementById('stat-dibatalkan').textContent = stats.dibatalkan || 0;
    }

    function updateMonthLabel() {
        document.getElementById('current-month').textContent = `${BULAN_NAMA[currentBulan]} ${currentTahun}`;
    }

    // ============================================================
    // CALENDAR
    // ============================================================
    function renderCalendar() {
        const grid = document.getElementById('calendar-grid');
        if (!grid) return;

        const firstDay = new Date(currentTahun, currentBulan - 1, 1);
        const lastDay = new Date(currentTahun, currentBulan, 0);
        const daysInMonth = lastDay.getDate();

        let startDayOfWeek = firstDay.getDay() - 1;
        if (startDayOfWeek < 0) startDayOfWeek = 6;

        const today = new Date();
        const isCurrentMonth = today.getFullYear() === currentTahun && (today.getMonth() + 1) === currentBulan;
        const todayDate = today.getDate();

        let html = '';

        for (let i = 0; i < startDayOfWeek; i++) {
            html += `<div class="aspect-square"></div>`;
        }

        for (let day = 1; day <= daysInMonth; day++) {
            const dayTasks = calendarData[day] || [];
            const isToday = isCurrentMonth && day === todayDate;
            const hasTasks = dayTasks.length > 0;

            let dotColor = 'bg-gray-300';
            if (hasTasks) {
                const statuses = dayTasks.map(t => t.status);
                if (statuses.includes('proses')) dotColor = 'bg-amber-500';
                else if (statuses.includes('belum')) dotColor = 'bg-blue-500';
                else if (statuses.includes('selesai')) dotColor = 'bg-green-500';
                else dotColor = 'bg-red-500';
            }

            const baseClass = isToday
                ? 'bg-brand-primary text-white font-bold shadow-md'
                : hasTasks
                    ? 'bg-blue-50 hover:bg-blue-100 text-gray-800 font-semibold cursor-pointer'
                    : 'text-gray-600 hover:bg-gray-50';

            html += `
                <div class="aspect-square rounded-lg flex flex-col items-center justify-center relative transition ${baseClass}"
                     onclick="showDayDetail(${day})">
                    <span class="text-xs">${day}</span>
                    ${hasTasks ? `<span class="absolute bottom-1 w-1.5 h-1.5 rounded-full ${dotColor}"></span>` : ''}
                    ${hasTasks && dayTasks.length > 1 ? `<span class="absolute top-0.5 right-0.5 text-[8px] font-bold bg-white/80 rounded-full w-3.5 h-3.5 flex items-center justify-center">${dayTasks.length}</span>` : ''}
                </div>
            `;
        }

        grid.innerHTML = html;
        if (window.lucide) lucide.createIcons();
    }

    // ============================================================
    // TASK LIST
    // ============================================================
    function renderTaskList() {
        const container = document.getElementById('task-list');
        if (!container) return;

        let filtered = tasks;
        if (filterStatus) {
            filtered = tasks.filter(t => t.status === filterStatus);
        }

        document.getElementById('total-tasks').textContent = filtered.length;

        if (filtered.length === 0) {
            container.innerHTML = `
                <div class="text-center py-8">
                    <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 text-gray-300"></i>
                    <p class="text-xs text-gray-400">${filterStatus ? 'Tidak ada task dengan status ini' : 'Belum ada task bulan ini'}</p>
                </div>`;
            if (window.lucide) lucide.createIcons();
            return;
        }

        const statusConfig = {
            belum: { label: 'Belum', color: 'bg-blue-100 text-blue-700', icon: 'circle-dashed' },
            proses: { label: 'Proses', color: 'bg-amber-100 text-amber-700', icon: 'loader' },
            selesai: { label: 'Selesai', color: 'bg-green-100 text-green-700', icon: 'check-circle-2' },
            dibatalkan: { label: 'Dibatalkan', color: 'bg-red-100 text-red-700', icon: 'x-circle' },
        };

        container.innerHTML = filtered.map(t => {
            const cfg = statusConfig[t.status] || statusConfig.belum;
            const overdue = t.is_overdue;

            return `
                <div class="bg-white border border-gray-100 rounded-lg p-3 hover:shadow-md transition cursor-pointer"
                     onclick="showTaskDetail(${t.id})">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold ${cfg.color} px-2 py-0.5 rounded-full">
                            <i data-lucide="${cfg.icon}" class="w-3 h-3"></i>
                            ${cfg.label}
                        </span>
                        ${overdue ? '<span class="text-[10px] font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded-full flex items-center gap-1"><i data-lucide="alert-triangle" class="w-3 h-3"></i> Overdue</span>' : ''}
                    </div>
                    <p class="text-xs font-semibold text-gray-800 line-clamp-2 mb-1">${escapeHtml(t.judul)}</p>
                    <div class="flex items-center gap-2 text-[10px] text-gray-500">
                        <i data-lucide="calendar" class="w-3 h-3"></i>
                        <span>${escapeHtml(t.tanggal_mulai_formatted)}</span>
                        ${t.tanggal_selesai_formatted ? `<span>${escapeHtml(t.tanggal_selesai_formatted)}</span>` : ''}
                    </div>
                    ${t.assigned_name ? `
                        <div class="flex items-center gap-1 text-[10px] text-gray-500 mt-1">
                            <i data-lucide="user" class="w-3 h-3"></i>
                            <span>${escapeHtml(t.assigned_name)}</span>
                        </div>
                    ` : ''}
                </div>`;
        }).join('');

        if (window.lucide) lucide.createIcons();
    }

    // ============================================================
    // MONTH NAVIGATION
    // ============================================================
    window.prevMonth = function () {
        currentBulan--;
        if (currentBulan < 1) { currentBulan = 12; currentTahun--; }
        loadAll();
    };

    window.nextMonth = function () {
        currentBulan++;
        if (currentBulan > 12) { currentBulan = 1; currentTahun++; }
        loadAll();
    };

    window.goToToday = function () {
        const today = new Date();
        currentBulan = today.getMonth() + 1;
        currentTahun = today.getFullYear();
        loadAll();
    };

    window.filterByStatus = function (status) {
        filterStatus = status;
        renderTaskList();
    };

    // ============================================================
    // DAY DETAIL (klik tanggal di kalender)
    // ============================================================
    window.showDayDetail = function (day) {
        const dayTasks = calendarData[day] || [];
        if (dayTasks.length === 0) return;

        selectedDate = `${currentTahun}-${String(currentBulan).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

        const statusConfig = {
            belum: { label: 'Belum', color: 'bg-blue-100 text-blue-700' },
            proses: { label: 'Proses', color: 'bg-amber-100 text-amber-700' },
            selesai: { label: 'Selesai', color: 'bg-green-100 text-green-700' },
            dibatalkan: { label: 'Dibatalkan', color: 'bg-red-100 text-red-700' },
        };

        const listHtml = dayTasks.map(t => `
            <div class="bg-white border border-gray-100 rounded-lg p-3 hover:bg-blue-50 transition cursor-pointer"
                 onclick="Swal.close(); setTimeout(() => showTaskDetail(${t.id}), 200)">
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[10px] font-bold ${statusConfig[t.status]?.color} px-2 py-0.5 rounded-full">
                        ${statusConfig[t.status]?.label}
                    </span>
                    ${t.assigned_to ? `<span class="text-[10px] text-gray-500 flex items-center gap-0.5"><i data-lucide="user" class="w-2.5 h-2.5"></i>${escapeHtml(t.assigned_to)}</span>` : ''}
                </div>
                <p class="text-xs font-semibold text-gray-800">${escapeHtml(t.judul)}</p>
            </div>
        `).join('');

        Swal.fire({
            title: `${day} ${BULAN_NAMA[currentBulan]} ${currentTahun}`,
            html: `<div class="text-left space-y-2">${listHtml}</div>`,
            width: 480,
            showConfirmButton: false,
            showCloseButton: true,
            customClass: { popup: 'rounded-2xl' },
            didOpen: () => { if (window.lucide) lucide.createIcons(); },
        });
    };

    // ============================================================
    // TASK DETAIL
    // ============================================================
    window.showTaskDetail = function (id) {
        const t = tasks.find(x => x.id === id);
        if (!t) return;

        const statusConfig = {
            belum: { label: 'Belum Dikerjakan', color: 'bg-blue-100 text-blue-700' },
            proses: { label: 'Sedang Proses', color: 'bg-amber-100 text-amber-700' },
            selesai: { label: 'Selesai', color: 'bg-green-100 text-green-700' },
            dibatalkan: { label: 'Dibatalkan', color: 'bg-red-100 text-red-700' },
        };
        const cfg = statusConfig[t.status];

        const actionsHtml = IS_READONLY ? '' : `
            <div class="flex gap-2 mt-4">
                <button onclick="Swal.close(); setTimeout(() => openTaskForm(${t.id}), 200)"
                    class="flex-1 bg-blue-700 hover:bg-blue-800 text-white text-xs font-semibold px-4 py-2 rounded-lg transition flex items-center justify-center gap-1.5">
                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                    Edit
                </button>
                <button onclick="Swal.close(); setTimeout(() => changeStatus(${t.id}), 200)"
                    class="flex-1 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold px-4 py-2 rounded-lg transition flex items-center justify-center gap-1.5">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                    Ubah Status
                </button>
                <button onclick="Swal.close(); setTimeout(() => deleteTask(${t.id}), 200)"
                    class="bg-red-600 hover:bg-red-700 text-white text-xs font-semibold px-3 py-2 rounded-lg transition flex items-center justify-center">
                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                </button>
            </div>
        `;

        Swal.fire({
            title: t.judul,
            html: `
                <div class="text-left space-y-3 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-bold ${cfg.color} px-2 py-1 rounded-full">${cfg.label}</span>
                        ${t.is_overdue ? '<span class="text-[10px] font-bold text-red-600 bg-red-50 px-2 py-1 rounded-full flex items-center gap-1"><i data-lucide="alert-triangle" class="w-3 h-3"></i> Overdue</span>' : ''}
                    </div>
                    ${t.deskripsi ? `<p class="text-gray-600 text-xs leading-relaxed">${escapeHtml(t.deskripsi)}</p>` : ''}
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold">Mulai</p>
                            <p class="text-gray-700">${escapeHtml(t.tanggal_mulai_formatted)}</p>
                        </div>
                        ${t.tanggal_selesai_formatted ? `
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase font-bold">Selesai</p>
                                <p class="text-gray-700">${escapeHtml(t.tanggal_selesai_formatted)}</p>
                            </div>
                        ` : ''}
                        ${t.assigned_name ? `
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase font-bold">PIC</p>
                                <p class="text-gray-700">${escapeHtml(t.assigned_name)}</p>
                            </div>
                        ` : ''}
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-bold">Dibuat</p>
                            <p class="text-gray-700">${escapeHtml(t.creator_name || '-')}</p>
                        </div>
                    </div>
                    ${t.lampiran_url ? `
                        <a href="${t.lampiran_url}" target="_blank"
                            class="flex items-center gap-2 p-2 bg-blue-50 hover:bg-blue-100 rounded-lg text-xs text-blue-700 font-semibold">
                            <i data-lucide="paperclip" class="w-3.5 h-3.5"></i>
                            Lihat Lampiran
                        </a>
                    ` : ''}
                    ${actionsHtml}
                </div>
            `,
            width: 480,
            showConfirmButton: false,
            showCloseButton: true,
            customClass: { popup: 'rounded-2xl' },
            didOpen: () => { if (window.lucide) lucide.createIcons(); },
        });
    };

    // ============================================================
    // FORM (CREATE / EDIT)
    // ============================================================
    async function loadAssignableUsers() {
        if (assignableUsers.length > 0) return;
        const res = await apiCall('GET', DATA.routes.assignableUsers);
        if (res.ok) {
            assignableUsers = res.data.users || [];
        }
    }

    function buildForm(task) {
        const isEdit = !!task;
        const tglMulai = task?.tanggal_mulai || `${currentTahun}-${String(currentBulan).padStart(2, '0')}-01`;
        const tglSelesai = task?.tanggal_selesai || '';

        const userOptions = assignableUsers.map(u => `
            <option value="${u.id}" ${task && task.assigned_to == u.id ? 'selected' : ''}>
                ${escapeHtml(u.name)} - ${escapeHtml(u.role_label)}
            </option>
        `).join('');

        return `
            <div class="text-left space-y-3 text-sm mt-2">
                <div>
                    <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Judul <span class="text-red-500">*</span></label>
                    <input id="f-judul" type="text" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                        value="${task ? escapeHtml(task.judul) : ''}" placeholder="Contoh: Input realisasi bulan ini" autocomplete="off" />
                </div>
                <div>
                    <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Deskripsi</label>
                    <textarea id="f-deskripsi" rows="3" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                        placeholder="Detail task (opsional)">${task ? escapeHtml(task.deskripsi || '') : ''}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input id="f-tanggal-mulai" type="date" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                            value="${tglMulai}" />
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Perkiraan Selesai</label>
                        <input id="f-tanggal-selesai" type="date" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                            value="${tglSelesai}" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">Status <span class="text-red-500">*</span></label>
                        <select id="f-status" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="belum" ${!task || task.status === 'belum' ? 'selected' : ''}>Belum Dikerjakan</option>
                            <option value="proses" ${task && task.status === 'proses' ? 'selected' : ''}>Sedang Proses</option>
                            <option value="selesai" ${task && task.status === 'selesai' ? 'selected' : ''}>Selesai</option>
                            <option value="dibatalkan" ${task && task.status === 'dibatalkan' ? 'selected' : ''}>Dibatalkan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1.5 text-xs uppercase tracking-wide">PIC / Assignee</label>
                        <select id="f-assigned" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="">- Tidak ada -</option>
                            ${userOptions}
                        </select>
                    </div>
                </div>
            </div>
        `;
    }

    function collectForm() {
        const assigned = document.getElementById('f-assigned').value;
        return {
            judul: document.getElementById('f-judul').value.trim(),
            deskripsi: document.getElementById('f-deskripsi').value.trim() || null,
            tanggal_mulai: document.getElementById('f-tanggal-mulai').value,
            tanggal_selesai: document.getElementById('f-tanggal-selesai').value || null,
            status: document.getElementById('f-status').value,
            assigned_to: assigned ? Number(assigned) : null,
        };
    }

    window.openTaskForm = async function (id) {
        if (IS_READONLY) return;

        await loadAssignableUsers();
        const task = id ? tasks.find(t => t.id === id) : null;

        Swal.fire({
            title: task ? 'Edit Task' : 'Tambah Task',
            width: 580,
            html: buildForm(task),
            showCancelButton: true,
            confirmButtonText: task ? 'Update' : 'Simpan',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-blue-700 hover:bg-blue-800 text-white font-medium px-5 py-2.5 rounded-lg mx-1 transition',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1 transition',
                popup: 'rounded-2xl',
            },
            buttonsStyling: false,
            didOpen: () => { if (window.lucide) lucide.createIcons(); },
            preConfirm: () => {
                const d = collectForm();
                if (!d.judul) {
                    Swal.showValidationMessage('Judul wajib diisi');
                    return false;
                }
                if (!d.tanggal_mulai) {
                    Swal.showValidationMessage('Tanggal mulai wajib diisi');
                    return false;
                }
                if (d.tanggal_selesai && d.tanggal_selesai < d.tanggal_mulai) {
                    Swal.showValidationMessage('Tanggal selesai harus setelah tanggal mulai');
                    return false;
                }
                return d;
            }
        }).then(async (r) => {
            if (!r.isConfirmed) return;

            const url = task ? buildRoute(DATA.routes.update, id) : DATA.routes.store;
            const method = task ? 'PUT' : 'POST';
            const res = await apiCall(method, url, r.value);

            if (res.ok) {
                toastSuccess(res.data.message);
                loadAll();
            } else {
                const errors = res.data.errors || {};
                const first = Object.values(errors)[0];
                toastError(Array.isArray(first) ? first[0] : (res.data.message || 'Gagal menyimpan'));
            }
        });
    };

    // ============================================================
    // CHANGE STATUS
    // ============================================================
    window.changeStatus = async function (id) {
        if (IS_READONLY) return;

        const { value: status } = await Swal.fire({
            title: 'Ubah Status',
            input: 'select',
            inputOptions: {
                belum: 'Belum Dikerjakan',
                proses: 'Sedang Proses',
                selesai: 'Selesai',
                dibatalkan: 'Dibatalkan',
            },
            inputValue: tasks.find(t => t.id === id)?.status || 'belum',
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-amber-600 hover:bg-amber-700 text-white font-medium px-5 py-2.5 rounded-lg mx-1',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1',
                popup: 'rounded-2xl',
            },
            buttonsStyling: false,
        });

        if (!status) return;

        const url = buildRoute(DATA.routes.updateStatus, id);
        const res = await apiCall('PATCH', url, { status });

        if (res.ok) {
            toastSuccess(res.data.message);
            loadAll();
        } else {
            toastError(res.data.message || 'Gagal mengubah status');
        }
    };

    // ============================================================
    // DELETE
    // ============================================================
    window.deleteTask = async function (id) {
        if (IS_READONLY) return;

        const task = tasks.find(t => t.id === id);
        if (!task) return;

        const confirm = await Swal.fire({
            title: 'Hapus Task?',
            html: `<p class="text-sm text-gray-600"><b>${escapeHtml(task.judul)}</b> akan dihapus.</p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'bg-red-600 hover:bg-red-700 text-white font-medium px-5 py-2.5 rounded-lg mx-1',
                cancelButton: 'bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-5 py-2.5 rounded-lg mx-1',
                popup: 'rounded-2xl',
            },
            buttonsStyling: false,
        });

        if (!confirm.isConfirmed) return;

        const url = buildRoute(DATA.routes.destroy, id);
        const res = await apiCall('DELETE', url);

        if (res.ok) {
            toastSuccess(res.data.message);
            loadAll();
        } else {
            toastError(res.data.message || 'Gagal menghapus');
        }
    };

    // ============================================================
    // INIT
    // ============================================================
    document.addEventListener('DOMContentLoaded', () => {
        loadAll().then(() => {
            // Auto-open task dari query param ?open=X
            const params = new URLSearchParams(window.location.search);
            const openId = params.get('open');
            if (openId && !isNaN(openId)) {
                setTimeout(() => window.showTaskDetail(Number(openId)), 500);
            }
        });
    });
})();