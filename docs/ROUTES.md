# 🛣️ Daftar Route SIKAP

Dokumen ini merangkum semua route aplikasi SIKAP, dikelompokkan per modul.

> **Auto-generate:** Jalankan `php artisan route:list` untuk versi terbaru.

---

## 1. Public Routes (Guest)

| Method | URL | Name | Controller | Middleware |
|---|---|---|---|---|
| GET | `/` | `login` | `Auth\LoginController@showLoginForm` | guest |
| GET | `/login` | - | `Auth\LoginController@showLoginForm` | guest |
| POST | `/login` | `login.attempt` | `Auth\LoginController@login` | guest |
| GET | `/sso/callback` | `sso.callback` | `SsoController@callback` | - |

---

## 2. Auth Routes (semua role)

| Method | URL | Name | Controller |
|---|---|---|---|
| POST | `/logout` | `logout` | `Auth\LoginController@logout` |
| GET | `/profile` | `profile` | `ProfileController@index` |
| GET | `/profile/data` | `profile.data` | `ProfileController@data` |
| PUT | `/profile` | `profile.update` | `ProfileController@update` |
| PUT | `/profile/password` | `profile.update-password` | `ProfileController@updatePassword` |
| POST | `/profile/avatar` | `profile.upload-avatar` | `ProfileController@uploadAvatar` |
| GET | `/about` | `about` | `AboutController@index` |
| GET | `/documentation` | `documentation` | `DocumentationController@index` |
| GET | `/changelog` | `changelog` | `ChangelogController@index` |

---

## 3. Confirm Password (admin_keuangan only)

| Method | URL | Name | Controller |
|---|---|---|---|
| POST | `/confirm-password` | `password.confirm.submit` | `Auth\ConfirmPasswordController@confirm` |
| GET | `/confirm-password/status` | `password.confirm.status` | `Auth\ConfirmPasswordController@status` |

---

## 4. Bug Report (keuangan & admin_keuangan)

| Method | URL | Name | Controller |
|---|---|---|---|
| GET | `/bug-report` | `bug-report.index` | `Keuangan\BugReportController@index` |
| GET | `/bug-report/data` | `bug-report.data` | `Keuangan\BugReportController@data` |
| POST | `/bug-report` | `bug-report.store` | `Keuangan\BugReportController@store` |

---

## 5. Admin Keuangan (role: admin_keuangan)

Prefix: `/admin` — Name: `admin.*`

| Method | URL | Name | Controller |
|---|---|---|---|
| GET | `/admin` | `admin.dashboard` | `Admin\DashboardController@index` |
| GET | `/admin/master` | `admin.master` | `Admin\MasterController@index` |
| GET | `/admin/master/{tab}/items` | `admin.master.items` | `Admin\MasterController@items` |
| POST | `/admin/master/{tab}` | `admin.master.store` | `Admin\MasterController@store` |
| PUT | `/admin/master/{tab}/{id}` | `admin.master.update` | `Admin\MasterController@update` |
| DELETE | `/admin/master/{tab}/{id}` | `admin.master.destroy` | `Admin\MasterController@destroy` |
| GET | `/admin/users` | `admin.users` | `Admin\UserController@index` |
| GET | `/admin/users/data` | `admin.users.data` | `Admin\UserController@data` |
| POST | `/admin/users` | `admin.users.store` | `Admin\UserController@store` |
| PUT | `/admin/users/{id}` | `admin.users.update` | `Admin\UserController@update` |
| PATCH | `/admin/users/{id}/toggle` | `admin.users.toggle` | `Admin\UserController@toggleActive` |
| DELETE | `/admin/users/{id}` | `admin.users.destroy` | `Admin\UserController@destroy` |
| GET | `/admin/audit` | `admin.audit` | `Admin\AuditTrailController@index` |
| GET | `/admin/audit/data` | `admin.audit.data` | `Admin\AuditTrailController@data` |
| GET | `/admin/audit/{id}` | `admin.audit.show` | `Admin\AuditTrailController@show` |

---

## 6. Keuangan (role: keuangan)

Prefix: `/keuangan` — Name: `keuangan.*`

### Dashboard
| Method | URL | Name | Controller |
|---|---|---|---|
| GET | `/keuangan` | `keuangan.dashboard` | `Keuangan\DashboardController@index` |
| GET | `/keuangan/data` | `keuangan.dashboard.data` | `Keuangan\DashboardController@data` |
| GET | `/keuangan/reminders` | `keuangan.reminders` | `Keuangan\DashboardController@reminders` |
| GET | `/keuangan/ai-insight` | `keuangan.ai-insight` | `Keuangan\AiInsightController@analyze` |

### Target
| Method | URL | Name |
|---|---|---|
| GET | `/keuangan/target` | `keuangan.target` |
| GET | `/keuangan/target/data` | `keuangan.target.data` |
| POST | `/keuangan/target` | `keuangan.target.store` |
| PUT | `/keuangan/target/{id}` | `keuangan.target.update` |
| DELETE | `/keuangan/target/{id}` | `keuangan.target.destroy` |

### Realisasi
| Method | URL | Name |
|---|---|---|
| GET | `/keuangan/realisasi` | `keuangan.realisasi` |
| GET | `/keuangan/realisasi/data` | `keuangan.realisasi.data` |
| POST | `/keuangan/realisasi` | `keuangan.realisasi.store` |
| PUT | `/keuangan/realisasi/{id}` | `keuangan.realisasi.update` |
| DELETE | `/keuangan/realisasi/{id}` | `keuangan.realisasi.destroy` |
| GET | `/keuangan/realisasi/target/{targetId}` | `keuangan.realisasi.target-details` |

### Kendala
| Method | URL | Name |
|---|---|---|
| GET | `/keuangan/kendala` | `keuangan.kendala` |
| GET | `/keuangan/kendala/data` | `keuangan.kendala.data` |
| POST | `/keuangan/kendala` | `keuangan.kendala.store` |
| PUT | `/keuangan/kendala/{id}` | `keuangan.kendala.update` |
| DELETE | `/keuangan/kendala/{id}` | `keuangan.kendala.destroy` |

### Laporan
| Method | URL | Name |
|---|---|---|
| GET | `/keuangan/laporan` | `keuangan.laporan` |
| GET | `/keuangan/laporan/data` | `keuangan.laporan.data` |
| GET | `/keuangan/laporan/export/pdf` | `keuangan.laporan.export.pdf` |
| GET | `/keuangan/laporan/export/csv` | `keuangan.laporan.export.csv` |

### Pengadaan
| Method | URL | Name |
|---|---|---|
| GET | `/keuangan/pengadaan` | `keuangan.pengadaan` |
| GET | `/keuangan/pengadaan/data` | `keuangan.pengadaan.data` |
| POST | `/keuangan/pengadaan` | `keuangan.pengadaan.store` |
| GET | `/keuangan/pengadaan/{id}` | `keuangan.pengadaan.detail` |
| GET | `/keuangan/pengadaan/{id}/data` | `keuangan.pengadaan.detail-data` |
| PUT | `/keuangan/pengadaan/{id}` | `keuangan.pengadaan.update` |
| DELETE | `/keuangan/pengadaan/{id}` | `keuangan.pengadaan.destroy` |
| PATCH | `/keuangan/pengadaan/tahapan/{tahapanId}/toggle-kendala` | `keuangan.pengadaan.tahapan.toggle-kendala` |
| POST | `/keuangan/pengadaan/{pengadaanId}/tahapan` | `keuangan.pengadaan.tahapan.store` |
| PUT | `/keuangan/pengadaan/tahapan/{tahapanId}` | `keuangan.pengadaan.tahapan.update` |
| PATCH | `/keuangan/pengadaan/tahapan/{tahapanId}/toggle` | `keuangan.pengadaan.tahapan.toggle` |
| DELETE | `/keuangan/pengadaan/tahapan/{tahapanId}` | `keuangan.pengadaan.tahapan.destroy` |
| PATCH | `/keuangan/pengadaan/tahapan/{tahapanId}/reorder` | `keuangan.pengadaan.tahapan.reorder` |

### PNBP
| Method | URL | Name |
|---|---|---|
| GET | `/keuangan/pnbp` | `keuangan.pnbp` |
| GET | `/keuangan/pnbp/data` | `keuangan.pnbp.data` |
| POST | `/keuangan/pnbp` | `keuangan.pnbp.store` |
| PUT | `/keuangan/pnbp/detail/{detailId}` | `keuangan.pnbp.detail.update` |
| DELETE | `/keuangan/pnbp/detail/{detailId}` | `keuangan.pnbp.detail.destroy` |
| DELETE | `/keuangan/pnbp/{id}` | `keuangan.pnbp.destroy` |

### Akselerasi
| Method | URL | Name |
|---|---|---|
| GET | `/keuangan/akselerasi` | `keuangan.akselerasi` |
| GET | `/keuangan/akselerasi/data` | `keuangan.akselerasi.data` |
| POST | `/keuangan/akselerasi` | `keuangan.akselerasi.store` |
| PUT | `/keuangan/akselerasi/{id}` | `keuangan.akselerasi.update` |
| DELETE | `/keuangan/akselerasi/{id}` | `keuangan.akselerasi.destroy` |

### Task Bulanan
| Method | URL | Name |
|---|---|---|
| GET | `/keuangan/tasks` | `keuangan.tasks.index` |
| GET | `/keuangan/tasks/data` | `keuangan.tasks.data` |
| GET | `/keuangan/tasks/calendar` | `keuangan.tasks.calendar` |
| GET | `/keuangan/tasks/upcoming` | `keuangan.tasks.upcoming` |
| GET | `/keuangan/tasks/assignable-users` | `keuangan.tasks.assignable-users` |
| POST | `/keuangan/tasks` | `keuangan.tasks.store` |
| PUT | `/keuangan/tasks/{id}` | `keuangan.tasks.update` |
| PATCH | `/keuangan/tasks/{id}/status` | `keuangan.tasks.update-status` |
| DELETE | `/keuangan/tasks/{id}` | `keuangan.tasks.destroy` |

---

## 7. Pimpinan (role: pimpinan)

Prefix: `/pimpinan` — Name: `pimpinan.*`

### Dashboard & Laporan
| Method | URL | Name |
|---|---|---|
| GET | `/pimpinan` | `pimpinan.dashboard` |
| GET | `/pimpinan/data` | `pimpinan.dashboard.data` |
| GET | `/pimpinan/laporan` | `pimpinan.laporan` |
| GET | `/pimpinan/laporan/data` | `pimpinan.laporan.data` |
| GET | `/pimpinan/laporan/export/pdf` | `pimpinan.laporan.export.pdf` |
| GET | `/pimpinan/laporan/export/csv` | `pimpinan.laporan.export.csv` |

### Audit Trail
| Method | URL | Name |
|---|---|---|
| GET | `/pimpinan/audit` | `pimpinan.audit` |
| GET | `/pimpinan/audit/data` | `pimpinan.audit.data` |
| GET | `/pimpinan/audit/{id}` | `pimpinan.audit.show` |

### Pengadaan & PNBP
| Method | URL | Name |
|---|---|---|
| GET | `/pimpinan/pengadaan` | `pimpinan.pengadaan` |
| GET | `/pimpinan/pengadaan/data` | `pimpinan.pengadaan.data` |
| GET | `/pimpinan/pengadaan/{id}` | `pimpinan.pengadaan.detail` |
| GET | `/pimpinan/pengadaan/{id}/data` | `pimpinan.pengadaan.detail-data` |
| GET | `/pimpinan/pnbp` | `pimpinan.pnbp` |
| GET | `/pimpinan/pnbp/data` | `pimpinan.pnbp.data` |

### Task Bulanan (read-only)
| Method | URL | Name |
|---|---|---|
| GET | `/pimpinan/tasks` | `pimpinan.tasks.index` |
| GET | `/pimpinan/tasks/data` | `pimpinan.tasks.data` |
| GET | `/pimpinan/tasks/calendar` | `pimpinan.tasks.calendar` |
| GET | `/pimpinan/tasks/upcoming` | `pimpinan.tasks.upcoming` |

---

## 8. User (role: user)

Prefix: `/user` — Name: `user.*`

| Method | URL | Name |
|---|---|---|
| GET | `/user` | `user.dashboard` |
| GET | `/user/data` | `user.dashboard.data` |
| GET | `/user/target` | `user.target` |
| GET | `/user/target/data` | `user.target.data` |
| GET | `/user/realisasi` | `user.realisasi` |
| GET | `/user/realisasi/data` | `user.realisasi.data` |
| GET | `/user/pengadaan` | `user.pengadaan` |
| GET | `/user/pengadaan/data` | `user.pengadaan.data` |
| GET | `/user/pengadaan/{id}` | `user.pengadaan.detail` |
| GET | `/user/pengadaan/{id}/data` | `user.pengadaan.detail-data` |
| GET | `/user/pnbp` | `user.pnbp` |
| GET | `/user/pnbp/data` | `user.pnbp.data` |

---

## 9. Format Respons JSON

Semua endpoint `data()` dan `store()` mengembalikan format konsisten:

**Success:**
```json
{
    "ok": true,
    "message": "Data berhasil disimpan",
    "data": { ... }
}
```

**Error validasi (422):**
```json
{
    "ok": false,
    "message": "Validasi gagal.",
    "errors": {
        "field": ["Pesan error"]
    }
}
```

**Error otorisasi (403):**
```json
{
    "ok": false,
    "message": "Anda tidak memiliki akses."
}
```

**Butuh konfirmasi password (423):**
```json
{
    "ok": false,
    "require_confirmation": true,
    "message": "Aksi ini memerlukan konfirmasi password."
}
```

---

## 10. Middleware per Route

| Middleware | Fungsi |
|---|---|
| `auth` | Wajib login |
| `guest` | Hanya untuk guest (belum login) |
| `role:xxx` | Cek role user |
| `role:xxx,yyy` | Cek role (multi) |

**Contoh:**
```php
Route::middleware(['auth', 'role:keuangan,admin_keuangan'])->group(...)
// Hanya keuangan ATAU admin_keuangan bisa akses
```