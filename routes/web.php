<?php

use App\Http\Controllers\Admin\AuditTrailController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\MasterController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ChangelogController;
use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\Keuangan\BugReportController;
use App\Http\Controllers\Keuangan\DashboardController as KeuanganDashboardController;
use App\Http\Controllers\Keuangan\KendalaController;
use App\Http\Controllers\Keuangan\LaporanController as KeuanganLaporanController;
use App\Http\Controllers\Keuangan\RealisasiController;
use App\Http\Controllers\Keuangan\TargetController;
use App\Http\Controllers\Pimpinan\AuditTrailController as PimpinanAuditTrailController;
use App\Http\Controllers\Pimpinan\DashboardController as PimpinanDashboardController;
use App\Http\Controllers\Pimpinan\LaporanController as PimpinanLaporanController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use App\Http\Controllers\User\TargetController as UserTargetController;
use App\Http\Controllers\User\RealisasiController as UserRealisasiController;
use App\Http\Controllers\Keuangan\PengadaanController;
use App\Http\Controllers\Pimpinan\PengadaanController as PimpinanPengadaanController;
use App\Http\Controllers\User\PengadaanController as UserPengadaanController;
use App\Http\Controllers\Keuangan\PnbpController;
use App\Http\Controllers\Pimpinan\PnbpController as PimpinanPnbpController;
use App\Http\Controllers\User\PnbpController as UserPnbpController;
use App\Http\Controllers\Keuangan\AkselerasiController;
use App\Http\Controllers\SsoController;
use App\Http\Controllers\Keuangan\TaskController as KeuanganTaskController;
use App\Http\Controllers\Pimpinan\TaskController as PimpinanTaskController;
use App\Http\Controllers\AboutController;

// ============================================================
// SSO CALLBACK
// ============================================================
Route::get('/sso/callback', [SsoController::class, 'callback'])->name('sso.callback');

// ============================================================
// AUTH ROUTES (guest)
// ============================================================
Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::get('/login', [LoginController::class, 'showLoginForm']);
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

// ============================================================
// LOGOUT (auth)
// ============================================================
Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ============================================================
// CONFIRM PASSWORD (admin_keuangan only)
// ============================================================
Route::middleware(['auth', 'role:admin_keuangan'])->group(function () {
    Route::post('/confirm-password', [ConfirmPasswordController::class, 'confirm'])
        ->name('password.confirm.submit');
    Route::get('/confirm-password/status', [ConfirmPasswordController::class, 'status'])
        ->name('password.confirm.status');
});

// ============================================================
// PROFILE, ABOUT, DOCUMENTATION, CHANGELOG (semua role)
// ============================================================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/data', [ProfileController::class, 'data'])->name('profile.data');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.upload-avatar');

    // About (semua role)
    Route::get('/about', [AboutController::class, 'index'])->name('about');

    // Documentation (semua role)
    Route::get('/documentation', [DocumentationController::class, 'index'])->name('documentation');

    // Changelog (semua role)
    Route::get('/changelog', [ChangelogController::class, 'index'])->name('changelog');
});

// ============================================================
// BUG REPORT (Keuangan & Admin Keuangan)
// ============================================================
Route::middleware(['auth', 'role:keuangan,admin_keuangan'])->group(function () {
    Route::prefix('bug-report')->name('bug-report.')->group(function () {
        Route::get('/', [BugReportController::class, 'index'])->name('index');
        Route::get('/data', [BugReportController::class, 'data'])->name('data');
        Route::post('/', [BugReportController::class, 'store'])->name('store');
    });
});

// ============================================================
// ADMIN KEUANGAN
// ============================================================
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin_keuangan'])
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Master Data
        Route::get('/master', [MasterController::class, 'index'])->name('master');
        Route::get('/master/{tab}/items', [MasterController::class, 'items'])->name('master.items');
        Route::post('/master/{tab}', [MasterController::class, 'store'])->name('master.store');
        Route::put('/master/{tab}/{id}', [MasterController::class, 'update'])->name('master.update');
        Route::delete('/master/{tab}/{id}', [MasterController::class, 'destroy'])->name('master.destroy');

        // User Management
        Route::get('/users', [UserController::class, 'index'])->name('users');
        Route::get('/users/data', [UserController::class, 'data'])->name('users.data');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{id}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        // Audit Trail
        Route::get('/audit', [AuditTrailController::class, 'index'])->name('audit');
        Route::get('/audit/data', [AuditTrailController::class, 'data'])->name('audit.data');
        Route::get('/audit/{id}', [AuditTrailController::class, 'show'])->name('audit.show');
    });

// ============================================================
// KEUANGAN
// ============================================================
Route::prefix('keuangan')
    ->name('keuangan.')
    ->middleware(['auth', 'role:keuangan'])
    ->group(function () {
        Route::get('/', [KeuanganDashboardController::class, 'index'])->name('dashboard');
        Route::get('/data', [KeuanganDashboardController::class, 'data'])->name('dashboard.data');

        // Target
        Route::get('/target', [TargetController::class, 'index'])->name('target');
        Route::get('/target/data', [TargetController::class, 'data'])->name('target.data');
        Route::post('/target', [TargetController::class, 'store'])->name('target.store');
        Route::put('/target/{id}', [TargetController::class, 'update'])->name('target.update');
        Route::delete('/target/{id}', [TargetController::class, 'destroy'])->name('target.destroy');

        // Realisasi
        Route::get('/realisasi', [RealisasiController::class, 'index'])->name('realisasi');
        Route::get('/realisasi/data', [RealisasiController::class, 'data'])->name('realisasi.data');
        Route::post('/realisasi', [RealisasiController::class, 'store'])->name('realisasi.store');
        Route::put('/realisasi/{id}', [RealisasiController::class, 'update'])->name('realisasi.update');
        Route::delete('/realisasi/{id}', [RealisasiController::class, 'destroy'])->name('realisasi.destroy');
        Route::get('/realisasi/target/{targetId}', [RealisasiController::class, 'targetDetails'])->name('realisasi.target-details');

        // Kendala
        Route::get('/kendala', [KendalaController::class, 'index'])->name('kendala');
        Route::get('/kendala/data', [KendalaController::class, 'data'])->name('kendala.data');
        Route::post('/kendala', [KendalaController::class, 'store'])->name('kendala.store');
        Route::put('/kendala/{id}', [KendalaController::class, 'update'])->name('kendala.update');
        Route::delete('/kendala/{id}', [KendalaController::class, 'destroy'])->name('kendala.destroy');

        // Laporan
        Route::get('/laporan', [KeuanganLaporanController::class, 'index'])->name('laporan');
        Route::get('/laporan/data', [KeuanganLaporanController::class, 'data'])->name('laporan.data');
        Route::get('/laporan/export/pdf', [KeuanganLaporanController::class, 'exportPdf'])->name('laporan.export.pdf');
        Route::get('/laporan/export/csv', [KeuanganLaporanController::class, 'exportCsv'])->name('laporan.export.csv');

        // Pengadaan
        Route::get('/pengadaan', [PengadaanController::class, 'index'])->name('pengadaan');
        Route::get('/pengadaan/data', [PengadaanController::class, 'data'])->name('pengadaan.data');
        Route::post('/pengadaan', [PengadaanController::class, 'store'])->name('pengadaan.store');
        Route::get('/pengadaan/{id}', [PengadaanController::class, 'detail'])->name('pengadaan.detail');
        Route::get('/pengadaan/{id}/data', [PengadaanController::class, 'detailData'])->name('pengadaan.detail-data');
        Route::put('/pengadaan/{id}', [PengadaanController::class, 'update'])->name('pengadaan.update');
        Route::delete('/pengadaan/{id}', [PengadaanController::class, 'destroy'])->name('pengadaan.destroy');
        Route::patch('/pengadaan/tahapan/{tahapanId}/toggle-kendala', [PengadaanController::class, 'toggleKendala'])->name('pengadaan.tahapan.toggle-kendala');

        // Tahapan
        Route::post('/pengadaan/{pengadaanId}/tahapan', [PengadaanController::class, 'storeTahapan'])->name('pengadaan.tahapan.store');
        Route::put('/pengadaan/tahapan/{tahapanId}', [PengadaanController::class, 'updateTahapan'])->name('pengadaan.tahapan.update');
        Route::patch('/pengadaan/tahapan/{tahapanId}/toggle', [PengadaanController::class, 'toggleTahapan'])->name('pengadaan.tahapan.toggle');
        Route::delete('/pengadaan/tahapan/{tahapanId}', [PengadaanController::class, 'destroyTahapan'])->name('pengadaan.tahapan.destroy');
        Route::patch('/pengadaan/tahapan/{tahapanId}/reorder', [PengadaanController::class, 'reorderTahapan'])->name('pengadaan.tahapan.reorder');

        // PNBP
        Route::get('/pnbp', [PnbpController::class, 'index'])->name('pnbp');
        Route::get('/pnbp/data', [PnbpController::class, 'data'])->name('pnbp.data');
        Route::post('/pnbp', [PnbpController::class, 'store'])->name('pnbp.store');
        Route::put('/pnbp/detail/{detailId}', [PnbpController::class, 'updateDetail'])->name('pnbp.detail.update');
        Route::delete('/pnbp/detail/{detailId}', [PnbpController::class, 'destroyDetail'])->name('pnbp.detail.destroy');
        Route::delete('/pnbp/{id}', [PnbpController::class, 'destroy'])->name('pnbp.destroy');

        // Akselerasi
        Route::get('/akselerasi', [AkselerasiController::class, 'index'])->name('akselerasi');
        Route::get('/akselerasi/data', [AkselerasiController::class, 'data'])->name('akselerasi.data');
        Route::post('/akselerasi', [AkselerasiController::class, 'store'])->name('akselerasi.store');
        Route::put('/akselerasi/{id}', [AkselerasiController::class, 'update'])->name('akselerasi.update');
        Route::delete('/akselerasi/{id}', [AkselerasiController::class, 'destroy'])->name('akselerasi.destroy');

        // Reminder
        Route::get('/reminders', [KeuanganDashboardController::class, 'reminders'])->name('reminders');

        // AI Insight
        Route::get('/ai-insight', [\App\Http\Controllers\Keuangan\AiInsightController::class, 'analyze'])
            ->name('ai-insight');

        // Task Bulanan
        Route::prefix('tasks')->name('tasks.')->group(function () {
            Route::get('/', [KeuanganTaskController::class, 'index'])->name('index');
            Route::get('/data', [KeuanganTaskController::class, 'data'])->name('data');
            Route::get('/calendar', [KeuanganTaskController::class, 'calendar'])->name('calendar');
            Route::get('/upcoming', [KeuanganTaskController::class, 'upcoming'])->name('upcoming');
            Route::get('/assignable-users', [KeuanganTaskController::class, 'assignableUsers'])->name('assignable-users');
            Route::post('/', [KeuanganTaskController::class, 'store'])->name('store');
            Route::put('/{id}', [KeuanganTaskController::class, 'update'])->name('update');
            Route::patch('/{id}/status', [KeuanganTaskController::class, 'updateStatus'])->name('update-status');
            Route::delete('/{id}', [KeuanganTaskController::class, 'destroy'])->name('destroy');
        });
    });

// ============================================================
// PIMPINAN
// ============================================================
Route::prefix('pimpinan')
    ->name('pimpinan.')
    ->middleware(['auth', 'role:pimpinan'])
    ->group(function () {
        Route::get('/', [PimpinanDashboardController::class, 'index'])->name('dashboard');
        Route::get('/data', [PimpinanDashboardController::class, 'data'])->name('dashboard.data');

        Route::get('/laporan', [PimpinanLaporanController::class, 'index'])->name('laporan');
        Route::get('/laporan/data', [PimpinanLaporanController::class, 'data'])->name('laporan.data');
        Route::get('/laporan/export/pdf', [PimpinanLaporanController::class, 'exportPdf'])->name('laporan.export.pdf');
        Route::get('/laporan/export/csv', [PimpinanLaporanController::class, 'exportCsv'])->name('laporan.export.csv');

        Route::get('/audit', [PimpinanAuditTrailController::class, 'index'])->name('audit');
        Route::get('/audit/data', [PimpinanAuditTrailController::class, 'data'])->name('audit.data');
        Route::get('/audit/{id}', [PimpinanAuditTrailController::class, 'show'])->name('audit.show');

        Route::get('/pengadaan', [PimpinanPengadaanController::class, 'index'])->name('pengadaan');
        Route::get('/pengadaan/data', [PimpinanPengadaanController::class, 'data'])->name('pengadaan.data');
        Route::get('/pengadaan/{id}', [PimpinanPengadaanController::class, 'detail'])->name('pengadaan.detail');
        Route::get('/pengadaan/{id}/data', [PimpinanPengadaanController::class, 'detailData'])->name('pengadaan.detail-data');

        Route::get('/pnbp', [PimpinanPnbpController::class, 'index'])->name('pnbp');
        Route::get('/pnbp/data', [PimpinanPnbpController::class, 'data'])->name('pnbp.data');

        Route::prefix('tasks')->name('tasks.')->group(function () {
            Route::get('/', [PimpinanTaskController::class, 'index'])->name('index');
            Route::get('/data', [PimpinanTaskController::class, 'data'])->name('data');
            Route::get('/calendar', [PimpinanTaskController::class, 'calendar'])->name('calendar');
            Route::get('/upcoming', [PimpinanTaskController::class, 'upcoming'])->name('upcoming');
        });
    });

// ============================================================
// USER (read-only)
// ============================================================
Route::prefix('user')
    ->name('user.')
    ->middleware(['auth', 'role:user'])
    ->group(function () {
        Route::get('/', [UserDashboardController::class, 'index'])->name('dashboard');
        Route::get('/data', [UserDashboardController::class, 'data'])->name('dashboard.data');

        Route::get('/target', [UserTargetController::class, 'index'])->name('target');
        Route::get('/target/data', [UserTargetController::class, 'data'])->name('target.data');

        Route::get('/realisasi', [UserRealisasiController::class, 'index'])->name('realisasi');
        Route::get('/realisasi/data', [UserRealisasiController::class, 'data'])->name('realisasi.data');

        Route::get('/pengadaan', [UserPengadaanController::class, 'index'])->name('pengadaan');
        Route::get('/pengadaan/data', [UserPengadaanController::class, 'data'])->name('pengadaan.data');
        Route::get('/pengadaan/{id}', [UserPengadaanController::class, 'detail'])->name('pengadaan.detail');
        Route::get('/pengadaan/{id}/data', [UserPengadaanController::class, 'detailData'])->name('pengadaan.detail-data');

        Route::get('/pnbp', [UserPnbpController::class, 'index'])->name('pnbp');
        Route::get('/pnbp/data', [UserPnbpController::class, 'data'])->name('pnbp.data');
    });