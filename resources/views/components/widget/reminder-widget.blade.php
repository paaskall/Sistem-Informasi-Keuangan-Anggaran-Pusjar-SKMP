<div id="reminder-widget" class="bg-white rounded-2xl border-2 border-amber-200 shadow-sm overflow-hidden mb-6 hidden">
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 px-5 py-4 flex items-center justify-between gap-2 border-b border-amber-200/60">
        <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center shadow-sm flex-shrink-0">
                <i data-lucide="bell-ring" class="w-4 h-4 text-amber-700"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-amber-900 leading-tight">Pengingat Input</h3>
                <p class="text-[10px] text-amber-700" id="reminder-subtitle">Memuat...</p>
            </div>
        </div>
        <span id="reminder-total" class="text-xs font-bold bg-amber-500 text-white px-2.5 py-1 rounded-full">0</span>
    </div>
    <div id="reminder-body" class="p-4 space-y-3 max-h-[500px] overflow-y-auto">
        <div class="text-center py-4">
            <i data-lucide="loader" class="w-5 h-5 mx-auto text-gray-300 animate-spin"></i>
            <p class="text-[10px] text-gray-400 mt-2">Memuat...</p>
        </div>
    </div>
</div>