# 🏗️ Arsitektur SIKAP

Dokumen ini menjelaskan arsitektur teknis aplikasi SIKAP: alur data, layer aplikasi, dan integrasi eksternal.

---

## 1. Layer Aplikasi

SIKAP mengikuti pola **MVC + Service Layer** khas Laravel:

```
┌──────────────────────────────────────────────────────┐
│                    HTTP Request                       │
└───────────────────────┬──────────────────────────────┘
                        │
                        ▼
┌──────────────────────────────────────────────────────┐
│              Route (routes/web.php)                   │
│         - middleware: auth, role:xxx                  │
└───────────────────────┬──────────────────────────────┘
                        │
                        ▼
┌──────────────────────────────────────────────────────┐
│         Controller (app/Http/Controllers)             │
│   - Validasi via FormRequest                          │
│   - Delegasi logika ke Service                        │
│   - Return View / JsonResponse                        │
└───────────────────────┬──────────────────────────────┘
                        │
                        ▼
┌──────────────────────────────────────────────────────┐
│              Service (app/Services)                   │
│   - Business logic                                    │
│   - Query ke Model                                    │
│   - Integrasi API eksternal                           │
└───────────────────────┬──────────────────────────────┘
                        │
                        ▼
┌──────────────────────────────────────────────────────┐
│              Model (app/Models)                       │
│   - Eloquent ORM                                      │
│   - Relasi & Accessor                                 │
│   - Scope                                             │
└──────────────────────────────────────────────────────┘
```

---

## 2. Struktur Folder Utama

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          → Kelola user, master data, audit
│   │   ├── Keuangan/       → Target, realisasi, PNBP, dll
│   │   ├── Pimpinan/       → Read-only (laporan, audit)
│   │   └── User/           → Read-only (data publik)
│   ├── Middleware/
│   │   ├── RoleMiddleware.php      → Validasi role
│   │   └── SecurityHeadersMiddleware.php
│   └── Requests/
│       ├── User/StoreUserRequest.php
│       ├── User/UpdateUserRequest.php
│       └── UpdateProfileRequest.php
├── Models/
│   ├── User.php
│   ├── Target.php, TargetDetail.php
│   ├── Realisasi.php, RealisasiDetail.php
│   ├── Pnbp.php, PnbpDetail.php
│   ├── Pengadaan.php, PengadaanTahapan.php
│   ├── Kendala.php
│   ├── Task.php
│   ├── AkselerasiPenyerapan.php
│   ├── AuditTrail.php
│   └── BugReport.php
├── Services/
│   ├── DashboardService.php      → Agregasi data dashboard
│   ├── ReminderService.php        → Hitung reminder realisasi & PNBP
│   └── GeminiService.php          → Integrasi AI Gemini
├── Traits/
│   └── Auditable.php              → Auto-log audit trail
└── Helpers/
    └── TahunHelper.php
```

---

## 3. Alur Data

### 3.1 Login Flow

```
[Login Form] → POST /login
    ↓
LoginController@login
    ↓
RateLimiter check (5x / 15 min)
    ↓
Auth::attempt (username + password)
    ↓
Session::regenerate
    ↓
Session::put('show_login_reminder', true)  ← Khusus role keuangan
    ↓
Redirect per role:
    - admin_keuangan → /admin
    - keuangan → /keuangan
    - pimpinan → /pimpinan
    - user → /user
```

### 3.2 Dashboard Flow (Keuangan)

```
[User buka /keuangan]
    ↓
Keuangan\DashboardController@index
    ↓
Session::forget('show_login_reminder') → $showReminder
    ↓
View: keuangan.dashboard
    ↓
[JS loads: dashboard.js]
    ↓
DOMContentLoaded:
    1. loadData() → fetch /keuangan/data?tahun=YYYY
       → Render summary, chart, progress, dll
    2. showLoginReminder() → fetch /keuangan/reminders
       → Render widget pengingat
       → SweetAlert (jika $showReminder = true)
       → Fetch /keuangan/ai-insight (Gemini)
```

### 3.3 AI Insight Flow

```
[User login sebagai keuangan]
    ↓
dashboard.js → showLoginReminder()
    ↓
fetch /keuangan/ai-insight
    ↓
AiInsightController@analyze
    ↓
ReminderService->getReminders() → ambil data komponen < 100%
    ↓
GeminiService->analyzeRealisasi($reminders)
    ↓
Cache::remember('gemini_analysis_' . md5(json)) { ... }
    ↓
POST Gemini API: gemini-2.5-flash
    ↓
Parse JSON response → return ke JS
    ↓
Render di widget AI (prioritas, alasan, strategi)
```

---

## 4. Service Layer

### 4.1 `DashboardService`

**Fungsi:** Agregasi data untuk dashboard.
**Method utama:**
- `getStats($tahun)` — Total target, realisasi, capaian, selisih
- `getPerBulan($tahun)` — Data chart bulanan
- `getRencanaRealisasiBulanIni($tahun)` — Rencana per komponen
- `getProgressPengadaan($limit)` — Progress paket pengadaan
- `getPnbpSummary($tahun)` — Ringkasan PNBP
- `getCatatanKendala($tahun)` — Matriks risiko
- `getAkselerasi($tahun)` — Tabel akselerasi
- `getKendalaPengadaan($tahun)` — Kendala pengadaan
- `getUpcomingTasks($limit)` — Task mendatang

### 4.2 `ReminderService`

**Fungsi:** Hitung reminder komponen yang belum 100%.
**Method utama:**
- `getReminders($tahun, $bulan)` — Return array `['realisasi', 'pnbp', 'total']`
- `getRealisasiReminders()` — Per komponen belanja
- `getPnbpReminders()` — Per kategori PNBP
- `getReminderCount()` — Count total

**Caching:** Tidak ada (query ringan via `DB::table()`).

### 4.3 `GeminiService`

**Fungsi:** Integrasi dengan Google Gemini AI.
**Method utama:**
- `analyzeRealisasi($reminders)` — Analisa prioritas realisasi
- `buildPrompt($reminders)` — Susun prompt untuk Gemini
- `extractJson($text)` — Robust JSON parser

**Caching:** `Cache::remember('gemini_analysis_' . md5(...), 3600)`.

**Config:** `config/gemini.php`
- `api_key` — dari `GEMINI_API_KEY`
- `model` — dari `GEMINI_MODEL` (default: `gemini-2.5-flash`)

---

## 5. Middleware

### 5.1 `RoleMiddleware`

```php
// Cek user login
if (!Auth::check()) → redirect login

// Cek user aktif
if (!$user->active) → logout

// Cek role
if (!in_array($user->role, $roles)) → 403
```

**Alias:** `'role' => RoleMiddleware::class` (di `bootstrap/app.php`).

**Penggunaan di route:**
```php
Route::middleware(['auth', 'role:keuangan'])->group(...)
Route::middleware(['auth', 'role:keuangan,admin_keuangan'])->group(...)  // Multi-role
```

### 5.2 `SecurityHeadersMiddleware`

**Fungsi:** Set header keamanan global (CSP, X-Frame-Options, dll).
**Registrasi:** `$middleware->prepend(SecurityHeadersMiddleware::class)` di `bootstrap/app.php`.

---

## 6. Model & Relasi

### 6.1 User

```php
User
├── role: admin_keuangan | keuangan | pimpinan | user
├── active: bool
├── hasMany Kendala
├── hasMany Realisasi
└── hasMany AuditTrail
```

### 6.2 Target

```php
Target
├── tahun: int
├── bulan: int (1-12)
├── hasMany TargetDetail
│   └── belongsTo KomponenBelanja
└── hasMany Realisasi
```

### 6.3 Realisasi

```php
Realisasi
├── target_id → Target
├── tahun, bulan
├── hasMany RealisasiDetail
│   └── belongsTo KomponenBelanja
└── hasMany Kendala
```

### 6.4 PNBP

```php
Pnbp
├── tahun
└── hasMany PnbpDetail
    └── belongsTo KategoriPnbp
```

### 6.5 Task

```php
Task
├── bulan, tahun
├── tanggal_mulai, tanggal_selesai
├── status: belum | proses | selesai | dibatalkan
├── assigned_to → User (nullable)
├── created_by → User
└── lampiran: string (nullable)
```

### 6.6 BugReport

```php
BugReport
├── user_id → User
├── judul, deskripsi
├── kategori: ui_ux | fungsional | performa | data | lainnya
├── prioritas: rendah | sedang | tinggi | kritis
└── status: baru | diproses | selesai | ditolak
```

---

## 7. Audit Trail (Trait Auditable)

Model yang pakai `Auditable` otomatis log ke tabel `audit_trails` setiap `create`, `update`, `delete`.

**Contoh:**
```php
class Kendala extends Model
{
    use Auditable;
}
```

**Log disimpan:**
- `user_id`, `user_name`
- `action`: CREATE | UPDATE | DELETE | RESTORE
- `module`: Nama model
- `record_id`: ID record
- `before`, `after`: JSON perubahan
- `ip`, `created_at`

**Cara pakai:**
```php
Kendala::create([...]);           // Auto-log CREATE
$kendala->update([...]);          // Auto-log UPDATE
$kendala->delete();               // Auto-log DELETE
```

---

## 8. Konfigurasi Kunci

| File | Fungsi |
|---|---|
| `config/app-info.php` | Info aplikasi (nama, versi, tim) |
| `config/gemini.php` | API key & model Gemini |
| `config/changelog.php` | Riwayat versi aplikasi |
| `config/documentation.php` | Data dokumentasi (accordion) |
| `bootstrap/app.php` | Middleware alias & redirect per role |

---

## 9. Frontend (JS)

**Lokasi:** `public/js/pages/`
**Pola:** IIFE + `window.XXX_DATA` + SweetAlert.

| File | Fungsi |
|---|---|
| `dashboard.js` | Dashboard (chart, widget, AI, reminder) |
| `users.js` | Kelola user (CRUD + live password rules) |
| `tasks.js` | Kalender task + CRUD |
| `reminder.js` | Widget pengingat + badge topbar |
| `profile.js` | Profile user (edit, password, avatar) |
| `bug-report.js` | Form laporan bug |
| `about.js` | (opsional) interaksi halaman About |

**Konvensi:**
- Selalu pakai `escapeHtml()` sebelum render ke innerHTML.
- Pakai `apiCall()` wrapper (handle 401 → redirect login).
- Pakai `Swal.fire()` untuk modal & notifikasi.
- Pakai `lucide.createIcons()` setelah render HTML baru.

---

## 10. Testing & Debug

**Command penting:**
```bash
php artisan optimize:clear       # Clear semua cache
php artisan route:list           # List semua route
php artisan tinker                # REPL untuk test manual
tail -30 storage/logs/laravel.log # Cek error
```

**Debug AI Gemini:**
```php
// Di tinker
config('gemini.api_key');
config('gemini.model');
app(App\Services\GeminiService::class)->analyzeRealisasi(
    app(App\Services\ReminderService::class)->getReminders()
);
```

---

## 11. Kesimpulan

**Prinsip utama:**
- **Controller tipis** — hanya validasi + delegasi.
- **Service untuk logika kompleks** — dashboard, reminder, AI.
- **Middleware untuk keamanan** — role, CSRF, security headers.
- **Trait untuk cross-cutting** — audit trail.
- **Blade + JS terpisah** — view bersih, JS di `public/js/pages/`.

**Untuk developer baru:** Mulai dari file ini, lalu baca `docs/ROUTES.md` untuk endpoint, dan `docs/PHPDOC_STYLE.md` untuk konvensi komentar.