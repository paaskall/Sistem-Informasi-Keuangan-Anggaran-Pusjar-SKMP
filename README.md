# SIKAP - Sistem Informasi Keuangan & Anggaran Pusjar SKMP

Aplikasi terintegrasi untuk pengelolaan target, realisasi anggaran, PNBP, pengadaan, dan analisa keuangan menggunakan kecerdasan buatan (AI) Gemini. Dirancang khusus untuk mendukung efisiensi kerja di lingkungan Pusjar SKMP LAN RI.

![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat&logo=php)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=flat&logo=tailwind-css)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat&logo=mysql)

---

## 📋 Daftar Isi
1. [Fitur Utama](#-fitur-utama)
2. [Prasyarat Sistem](#-prasyarat-sistem)
3. [Cara Instalasi](#-cara-instalasi)
4. [Akun Default (Seeder)](#-akun-default-seeder)
5. [Struktur Direktori](#-struktur-direktori)
6. [Konfigurasi AI Gemini](#-konfigurasi-ai-gemini)
7. [Lisensi](#-lisensi)

---

## 🚀 Fitur Utama

- **Multi-Role**: Akses berbeda untuk Admin Keuangan, User Keuangan, Pimpinan, dan User (Viewer).
- **Modul Perencanaan**: Kelola Target dan Realisasi per komponen belanja.
- **Modul Pelaksanaan**: Manajemen paket pengadaan, tahapan, dan kendala.
- **Modul Pelaporan**: PNBP, Laporan Bulanan (Export PDF/CSV), Akselerasi Penyerapan.
- **Pengingat Otomatis**: Notifikasi widget dan SweetAlert untuk komponen yang belum 100%.
- **Analisa AI Gemini**: Rekomendasi cerdas prioritas realisasi dengan AI.
- **Task Bulanan**: Kalender agenda dan reminder task dengan dukungan assignee.
- **Keamanan**: Audit trail, CSRF protection, password policy ketat, dan konfirmasi password untuk aksi sensitif.

---

## 🛠️ Prasyarat Sistem

Sebelum menjalankan aplikasi, pastikan server Anda memenuhi persyaratan berikut:

- **PHP** >= 8.3 (dengan ekstensi `pdo`, `mbstring`, `openssl`, `xml`, `curl`, `bcmath`)
- **Composer** >= 2.x
- **Node.js** & **NPM** (untuk build frontend)
- **MySQL** >= 8.0 atau **MariaDB** >= 10.5
- **Git**

---

## ⚙️ Cara Instalasi (Local Development)

Ikuti langkah-langkah di bawah ini untuk menjalankan SIKAP di komputer lokal Anda.

### 1. Clone Repositori

git clone <URL_REPOSITORY_ANDA> sikap
cd sikap

### 2. Install Dependensi PHP

composer install

### 3. Konfigurasi Environment

Copy file `.env.example` menjadi `.env`:

cp .env.example .env

Buka file `.env` dan sesuaikan konfigurasi database serta Gemini API:

APP_NAME=SIKAP
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sikap
DB_USERNAME=root
DB_PASSWORD=

# Konfigurasi Gemini AI
GEMINI_API_KEY=masukkan_api_key_anda_di_sini
GEMINI_MODEL=gemini-2.0-flash

> **Penting:** Dapatkan API Key Gemini secara gratis di [Google AI Studio](https://aistudio.google.com/app/apikey).

### 4. Generate Application Key

php artisan key:generate

### 5. Migrasi Database & Seeder

Pastikan database `sikap` sudah dibuat di MySQL Anda, lalu jalankan:

php artisan migrate --seed

### 6. Link Storage (untuk upload avatar & lampiran)

php artisan storage:link

### 7. Jalankan Server

php artisan serve

Aplikasi akan berjalan di `http://127.0.0.1:8000`.

---

## 🔐 Akun Default (Seeder)

Jika Anda menjalankan `--seed`, berikut adalah akun default yang bisa digunakan untuk login:

| Role | Username | Password | Akses Utama |
|---|---|---|---|
| **Admin Keuangan** | admin | Admin!12345 | Master Data, Kelola User, Audit Trail |
| **User Keuangan** | keuangan | Keuangan!12345 | Target, Realisasi, PNBP, Pengadaan |
| **Pimpinan** | pimpinan | Pimpinan!12345 | Laporan (Read-Only), Audit Trail |
| **User (Viewer)** | user | User!12345 | Dashboard & Data (Read-Only) |

> **Peringatan Keamanan:** Segera ganti password default ini setelah login pertama kali melalui halaman Profile.

---

## 📁 Struktur Direktori

Berikut adalah struktur direktori utama dari aplikasi SIKAP:

sikap/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   ├── Services/
│   └── Traits/
├── config/
├── database/
│   ├── migrations/
│   └── seeders/
├── public/
│   └── js/pages/
├── resources/
│   └── views/
└── routes/
    └── web.php

---

## 🤖 Konfigurasi AI Gemini

Aplikasi ini menggunakan **Gemini API** untuk fitur analisa prioritas realisasi.

1. Daftar di [Google AI Studio](https://aistudio.google.com/).
2. Buat API Key.
3. Masukkan ke `.env` pada variabel `GEMINI_API_KEY`.

> **Catatan:** Jika model yang Anda gunakan deprecated, ganti `GEMINI_MODEL` dengan model terbaru (misal `gemini-2.5-flash` atau `gemini-3.8-flash`) dan jalankan `php artisan config:clear`.

---

## 📝 Lisensi

Proyek ini dilisensikan di bawah **MIT License**.

---

## 👨‍💻 Tim Pengembang

- **Paskal Arvito Weni** - Full-Stack Developer
  - [Portofolio](https://aboutpaskal.vercel.app/)

---

© 2026 Lembaga Administrasi Negara RI (LAN RI). All rights reserved.
