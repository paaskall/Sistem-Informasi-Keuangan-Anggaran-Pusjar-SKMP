# 📝 Panduan PHPDoc SIKAP

Dokumen ini menjelaskan konvensi PHPDoc yang dipakai di SIKAP.

---

## 1. Class Header

```php
<?php

namespace App\Services;

/**
 * Service untuk mengelola reminder realisasi dan PNBP.
 *
 * Menghitung komponen belanja yang belum mencapai 100%
 * dan kategori PNBP yang belum mencapai target tahunan.
 *
 * @package App\Services
 */
class ReminderService
{
```

---

## 2. Method Docblock

```php
    /**
     * Hitung semua reminder (realisasi + PNBP) untuk user keuangan.
     *
     * @param int|null $tahun Tahun (default: tahun berjalan)
     * @param int|null $bulan Bulan 1-12 (default: bulan berjalan)
     * @return array{
     *     realisasi: array{count: int, items: array, message: string},
     *     pnbp: array{count: int, exists: bool, items: array, message: string},
     *     total: int,
     *     is_late_month: bool,
     *     periode: array{bulan: int, tahun: int, bulan_nama: string}
     * }
     */
    public function getReminders(?int $tahun = null, ?int $bulan = null): array
    {
```

**Ketentuan:**
- **`@param`** — tipe + nama + deskripsi singkat. Gunakan `?int` untuk nullable.
- **`@return`** — tipe + struktur data. Untuk array kompleks, pakai `array{...}`.
- **`@throws`** — kalau method bisa throw exception.
- **`@deprecated`** — kalau method akan dihapus.

---

## 3. Properti Docblock

```php
    /**
     * Cache total nominal (dalam rupiah) untuk menghindari query berulang.
     *
     * @var float|null
     */
    private ?float $_cachedTotalNominal = null;
```

---

## 4. Trait/Interface

```php
/**
 * Trait untuk auto-log perubahan model ke tabel audit_trails.
 *
 * Model yang pakai trait ini akan otomatis log CREATE, UPDATE, DELETE.
 *
 * @package App\Traits
 */
trait Auditable
{
```

---

## 5. Inline Comment

**Untuk logika yang tidak obvious:**

```php
// ⬇️ Pakai cache karena query join bisa lambat
$realisasiMap = Cache::remember('realisasi_' . $tahun, 3600, function () use ($tahun) {
    // ...
});

// ⬇️ Pastikan null bukan 0
if ($item['target'] === null) {
    continue;
}
```

**Hindari komentar yang redundant:**
```php
// ❌ Buruk
$total = $a + $b;  // Hitung total

// ✅ Baik (komentar menjelaskan "kenapa", bukan "apa")
// Total wajib float, bukan int, karena bisa negatif
$total = (float) ($a + $b);
```

---

## 6. Format Tipe Data

| Tipe | Contoh |
|---|---|
| Nullable | `?int`, `?string` |
| Union | `int\|string` |
| Array | `array`, `int[]` |
| Array asosiatif | `array{key: string, value: int}` |
| Object | `\App\Models\User` |
| Collection | `\Illuminate\Support\Collection` |
| Response | `\Illuminate\Http\JsonResponse` |

---

## 7. Contoh Lengkap

```php
<?php

namespace App\Services;

use App\Models\Realisasi;
use Illuminate\Support\Facades\Cache;

/**
 * Service untuk mengelola reminder realisasi.
 *
 * @package App\Services
 */
class ReminderService
{
    /**
     * Cache hasil analisa AI per data (key: md5 data).
     *
     * @var string
     */
    private const CACHE_PREFIX = 'gemini_analysis_';

    /**
     * Hitung reminder realisasi per komponen.
     *
     * @param int $tahun Tahun (2020-2099)
     * @param int $bulan Bulan (1-12)
     * @return array{
     *     count: int,
     *     items: array<int, array{
     *         komponen_id: int,
     *         kode: string,
     *         nama: string,
     *         target: float,
     *         realisasi: float,
     *         capaian: int
     *     }>,
     *     message: string
     * }
     */
    public function getRealisasiReminders(int $tahun, int $bulan): array
    {
        // ...
    }
}
```

---

## 8. Tools untuk Validasi PHPDoc

**Install PHPStan** (opsional, untuk cek tipe):

```bash
composer require --dev phpstan/phpstan
./vendor/bin/phpstan analyse app/ --level=5
```

**Install Laravel Pint** (untuk format kode):

```bash
./vendor/bin/pint
```

---

## 9. Kesimpulan

**Prinsip:**
- **Docblock** untuk setiap class, method public, dan properti non-trivial.
- **`@param`** & **`@return`** wajib untuk method public.
- **Inline comment** untuk "kenapa", bukan "apa".
- **Konsisten** — kalau satu method pakai `array{...}`, method lain juga.

**Untuk developer baru:** Baca file ini dulu, lalu pelajari 3-4 class contoh: `ReminderService`, `UserController`, `TaskController`.