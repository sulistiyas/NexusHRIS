# NexusHRIS - Enterprise Human Resource Information System

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![API Authentication](https://img.shields.io/badge/Sanctum-Token_Auth-blue?style=for-the-badge&logo=auth0&logoColor=white)](https://laravel.com/docs/sanctum)
[![Role Shield](https://img.shields.io/badge/Spatie-Permission_8.x-orange?style=for-the-badge)](https://spatie.be/docs/laravel-permission)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

**NexusHRIS** adalah platform sistem informasi manajemen sumber daya manusia (*Human Resource Information System*) terintegrasi yang dirancang untuk menjawab kebutuhan operasional bisnis modern di Indonesia. Mulai dari kepatuhan regulasi ketenagakerjaan nasional, hierarki organisasi multi-cabang, sistem absensi cerdas anti-kecurangan berbasis GPS Geofencing & Foto Selfie, pengajuan izin/cuti berjenjang, hingga kesiapan arsitektur terpadu antara **Web Application** dan **Mobile App (RESTful API V1)**.

---

## 🌟 Sorotan Utama Proyek

- **Arsitektur Shared Business Logic:** Seluruh aturan validasi bisnis (kalkulasi keterlambatan, verifikasi radius kantor, alur persetujuan cuti & lembur) terpusat pada *Service & Action Layer*, menjamin keseragaman data antara Web Portal dan Mobile App.
- **Kepatuhan Ketenagakerjaan Indonesia:** Dirancang adaptif terhadap regulasi ketenagakerjaan nasional, mencakup PP 35/2021, integrasi skema BPJS Ketenagakerjaan & Kesehatan, serta perhitungan PPh 21 TER 2024.
- **Presensi Cerdas Bebas Kecurangan:** Validasi kehadiran harian dengan rumus matematis jarak bumi (*Haversine formula*) dan verifikasi foto selfie kamera secara real-time.
- **Role-Based Access Control (RBAC) Berlapis:** 4 tingkat hak akses granular (`Super Admin`, `HR Admin`, `Manager`, `Employee`) dengan antarmuka dashboard adaptif.
- **Dokumentasi Rancangan Lengkap:** Skema relasi database dapat dilihat pada [ERD.md](ERD.md) dan alur logika proses bisnis pada [FLOWCHART.md](FLOWCHART.md).

---

## 📌 Daftar Isi

1. [Status Peta Jalan Fitur (Roadmap)](#-status-peta-jalan-fitur-roadmap)
2. [Fitur yang Telah Tersedia](#-fitur-yang-telah-tersedia)
   - [Sprint 1: Fondasi, Autentikasi & RBAC](#1-fondasi-sistem-autentikasi--rbac)
   - [Sprint 2: Struktur Organisasi & Manajemen Karyawan](#2-struktur-organisasi--manajemen-karyawan)
   - [Sprint 3: Presensi GPS, Shift & Manajemen Cuti/Izin](#3-presensi-gps-shift--manajemen-cutiizin)
3. [Fitur Mendatang (Coming Soon Features)](#-fitur-mendatang-coming-soon-features)
   - [Sprint 4: Mesin Penggajian (Payroll Engine), BPJS & PPh 21 TER](#4-mesin-penggajian-payroll-engine-bpjs--pph-21-ter-coming-soon)
   - [Sprint 5: Reimbursement, Aset Kantor & Dashboard Analitik](#5-reimbursement-aset-kantor--dashboard-analitik-coming-soon)
4. [Arsitektur Sistem](#-arsitektur-sistem)
5. [Katalog REST API V1](#-katalog-rest-api-v1)
6. [Panduan Instalasi & Menjalankan](#-panduan-instalasi--menjalankan)
7. [Akun Percobaan (Demo Accounts)](#-akun-percobaan-demo-accounts)
8. [Struktur Direktori Repositori](#-struktur-direktori-repositori)
9. [Lisensi](#-lisensi)

---

## 🧭 Status Peta Jalan Fitur (Roadmap)

Pengembangan NexusHRIS terbagi menjadi 5 siklus berjenjang (*Sprints*). Modul Sprint 1 s.d. 3 telah rampung dan siap digunakan, sedangkan Sprint 4 dan 5 dirancang sebagai fitur rilis mendatang:

| Siklus | Modul / Fokus Pengembangan | Status | Keterangan |
| :---: | :--- | :---: | :--- |
| **Sprint 1** | **Fondasi Sistem, Autentikasi & RBAC** | ✅ **Selesai** | Multi-guard Auth, Spatie 4 Roles, Audit Trail, Web & API baseline. |
| **Sprint 2** | **Struktur Organisasi & Karyawan** | ✅ **Selesai** | Cabang Geofence, Departemen, Onboarding Karyawan, Berkas Dokumen, Portal ESS. |
| **Sprint 3** | **Presensi GPS, Shift & Cuti/Lembur** | ✅ **Selesai** | Geofencing Haversine, Foto Selfie, Evaluasi Keterlambatan, Approval Workflow, Mobile API. |
| **Sprint 4** | **Payroll Engine, BPJS, PPh 21 TER & Slip PDF** | ✅ **Selesai** | Struktur Gaji, Kalkulasi Massal Batch, BPJS TK/Kes, Pajak PPh 21 TER, Slip Gaji PDF + QR. |
| **Sprint 5** | **Reimbursement, Aset & BI Analytics** | ⏳ **Coming Soon** | Klaim Reimbursement, Inventaris Aset Kantor, Dashboard Visual Analitik, Ekspor Excel. |

---

## 🚀 Fitur yang Telah Tersedia

### 1. Fondasi Sistem, Autentikasi & RBAC
- **Autentikasi Aman:** Mendukung login sesi berbasis browser (dengan proteksi CSRF) serta token API *Personal Access Token* (Laravel Sanctum) untuk aplikasi smartphone.
- **Proteksi Akun Non-aktif:** Blokir otomatis bagi akun karyawan yang berstatus non-aktif (`is_active = false`).
- **4 Tingkatan Hak Akses Granular:**
  - `Super Admin`: Akses mutlak pengaturan master sistem dan riwayat audit trail.
  - `HR Admin`: Pengelolaan cabang, departemen, data kepegawaian, dan monitoring absensi.
  - `Manager`: Supervisi anggota tim, persetujuan cuti bawahan, dan verifikasi lembur.
  - `Employee`: Akses portal mandiri (ESS), pencatatan presensi, dan permohonan izin/lembur.
- **Audit Trail Digital (`activity_logs`):** Mencatat otomatis setiap tindakan krusial (login, manipulasi data pegawai, perubahan status) beserta alamat IP dan perangkat pengguna.
- **Antarmuka Modern:** Dibangun dengan Tailwind CSS v4 bertema elegan *Deep Ocean & Cerulean Blue* yang ramah sentuhan jempol di smartphone (*mobile-first*).

---

### 2. Struktur Organisasi & Manajemen Karyawan
- **Manajemen Kantor Cabang & Geofencing GPS:**
  - Penentuan lokasi kantor cabang dengan koordinat lintang (*Latitude*), bujur (*Longitude*), serta toleransi radius presensi dalam meter.
  - Visualisasi pin lokasi kantor dan batas lingkaran geofence interaktif (*Leaflet.js & OpenStreetMap*).
- **Struktur Departemen & Jabatan:** Pengaturan hierarki departemen, divisi kerja, dan tingkatan jabatan karyawan secara terstruktur.
- **Onboarding Karyawan Otomatis & Transaksional:**
  - Pembuatan Nomor Induk Pegawai (NIP) otomatis: `NX-{YYYYMM}-{INCREMENT}` (contoh: `NX-202610-0001`).
  - Pembuatan akun login pengguna secara transaksional (`DB::transaction`) lengkap dengan penugasan peran (*Role*) dan atasan langsung (*Line Manager*).
- **Penyimpanan Berkas Digital Terproteksi:**
  - Pengarsipan berkas identitas (KTP, NPWP, Ijazah, Kontrak Kerja) dalam format PDF/Gambar.
  - Berkas disimpan di disk privat terisolasi (`storage/app/private/`) dan hanya dapat diunduh oleh pemilik berkas atau pihak berwenang via *streaming download*.
- **Portal Mandiri Karyawan / Employee Self-Service (ESS):**
  - Pembaruan informasi kontak pribadi, alamat domisili, dan foto profil (*Avatar*).
  - Manajemen daftar kontak darurat (*Emergency Contacts*) keluarga.

---

### 3. Presensi GPS, Shift & Manajemen Cuti/Izin
- **Pengaturan Jadwal Shift Kerja:**
  - Konfigurasi jam masuk kerja, jam pulang, dan toleransi keterlambatan dalam satuan menit.
- **Presensi Pintar (Geofencing & Foto Selfie):**
  - **Verifikasi Radius Haversine:** Menghitung jarak perangkat karyawan ke titik kantor cabang secara matematis dan mengunci absensi apabila berada di luar radius kantor saat bertugas *WFO*.
  - **Verifikasi Foto Selfie:** Pengambilan foto wajah langsung melalui webcam komputer atau kamera smartphone sebelum tombol absensi dapat ditekan.
- **Deteksi Keterlambatan Otomatis:**
  - Sistem otomatis mengevaluasi status kehadiran: `ON_TIME` (tepat waktu), `LATE` (terlambat), atau `EARLY_LEAVE` (pulang mendahului jam shift).
  - Menghitung durasi menit keterlambatan secara otomatis dari jam masuk resmi.
- **Alur Pengajuan & Persetujuan Cuti:**
  - Pengajuan cuti tahunan, izin sakit (dengan lampiran surat dokter), cuti melahirkan, atau izin keperluan khusus.
  - Pemantauan sisa saldo kuota cuti tahunan karyawan secara real-time.
  - Alur persetujuan bertingkat (*Approval Workflow*) oleh Atasan Langsung dan HR Admin disertai pencatatan catatan/alasan penolakan.
- **Pengajuan & Verifikasi Lembur (Overtime):**
  - Pengajuan surat perintah kerja lembur dengan justifikasi pekerjaan dan konfirmasi atasan.
- **Dukungan Penuh REST API V1:**
  - Seluruh alur presensi, profil ESS, cuti, dan lembur tersedia sebagai endpoint API untuk integrasi aplikasi Android/iOS.

---

### 4. Mesin Penggajian (Payroll Engine), BPJS & PPh 21 TER
- **Struktur Komponen Gaji Karyawan:**
  - Konfigurasi fleksibel gaji pokok, tunjangan tetap, tunjangan transportasi, tunjangan makan, dan status PTKP.
- **Proses Batch Penggajian Bulanan Otomatis:**
  - Eksekusi massal perhitungan gaji bulanan seluruh pegawai dalam transaksi database atomik (`DB::transaction`).
  - Rekonsiliasi data jam lembur terverifikasi (formula PP 35/2021) dan autodebet cicilan kasbon berjalan.
- **Kalkulator Regulasi Finansial Ketenagakerjaan Indonesia:**
  - **BPJS Ketenagakerjaan:** Pemotongan otomatis program Jaminan Hari Tua (JHT 2% pekerja, 3.7% perusahaan), Jaminan Pensiun (JP 1% pekerja, 2% perusahaan dengan plafon Rp 10.042.300), JKK (0.24%), dan JKM (0.30%).
  - **BPJS Kesehatan:** Perhitungan iuran 4% pemberi kerja dan 1% pekerja dengan batas plafon gaji resmi Rp 12.000.000.
  - **Pajak Penghasilan PPh 21 Skema TER 2024:** Otomasi tarif efektif rata-rata bulanan berdasarkan Kategori A, B, atau C (PP 58/2023 & PMK 168/2023).
- **Generator Slip Gaji PDF Resmi dengan Kode QR:**
  - Dokumen slip gaji berformat PDF siap cetak dengan tata letak profesional berbasis Dompdf.
  - Tanda tangan digital kode QR dinamis untuk verifikasi publik keabsahan dokumen slip gaji.
- **Riwayat Slip Gaji Mandiri di Portal ESS:**
  - Pegawai dapat melihat rincian pendapatan & potongan serta mengunduh slip gaji bulanan mereka secara mandiri.
- **Modul Pengajuan Kasbon / Pinjaman Karyawan:**
  - Pengajuan pinjaman darurat karyawan via ESS/Mobile dengan tenor cicilan dan alur persetujuan HR/Admin serta pemotongan otomatis pada slip gaji saat batch dibayarkan (*PAID*).

---

## ⏳ Fitur Mendatang (Coming Soon Features)

Fitur-fitur berikut sedang dalam tahap perencanaan teknis lanjutan dan akan segera dirilis pada pembaruan mendatang:

### 5. Reimbursement, Aset Kantor & Dashboard Analitik (Coming Soon)
- [ ] **Modul Klaim Biaya Operasional (Reimbursement):**
  - Pengajuan klaim biaya perjalanan dinas, medis, atau representasi bisnis disertai unggahan foto struk kuitansi.
  - Verifikasi persetujuan atasan hingga pencairan (*Disbursement*) oleh tim keuangan.
- [ ] **Inventaris Aset Kantor & Serah Terima:**
  - Pencatatan aset perusahaan (Laptop, Monitor, Kendaraan Operasional) beserta kode seri dan kondisi fisik.
  - Log riwayat peminjaman ke karyawan beserta formulir berita acara pengembalian.
- [ ] **Dashboard Analitik Eksekutif (Business Intelligence):**
  - Visualisasi grafik interaktif rasio kehadiran pegawai, tren biaya penggajian bulanan, dan komposisi jumlah karyawan (*Headcount Analytics*).
- [ ] **Ekspor Rekapitulasi Data ke Excel (.xlsx):**
  - Unduh laporan presensi bulanan dan rekapitulasi penggajian ke format spreadsheet Excel dengan formula siap saji.
- [ ] **Optimasi Performa & Rilis Produksi:** Caching terdistribusi (Redis), eliminasi bottleneck query, audit keamanan, dan kesiapan deployment cloud.

---

## 🏛 Arsitektur Sistem

NexusHRIS mengadopsi pola **Shared Business Logic** (*Single Source of Truth*). Web Controller dan API Controller berperan murni sebagai penerima HTTP request, sementara seluruh kalkulasi bisnis dijalankan oleh Action dan Service Layer yang sama.

```
                           ┌───────────────────────────────┐
                           │      Web Browser (Blade)      │
                           └───────────────┬───────────────┘
                                           │ Session / CSRF
                                           ▼
                           ┌───────────────────────────────┐
                           │     Web Controller Layer      │
                           └───────────────┬───────────────┘
                                           │
                                           │ Memanggil
                                           ▼
┌──────────────────────────┐       ┌───────────────┐       ┌──────────────────────────┐
│ Mobile App (Flutter/RN)  │──────▶│ Action /      │◀──────│  REST API Controller     │
└──────────────────────────┘ Bearer│ Service Layer │       │  (App\Http\Controllers   │
                             Token └───────┬───────┘       │   \Api\V1\...)           │
                             (Sanctum)     │               └──────────────────────────┘
                                           ▼
                                   ┌───────────────┐
                                   │ Eloquent ORM  │
                                   └───────┬───────┘
                                           ▼
                                   ┌───────────────────┐
                                   │ PostgreSQL / DB   │
                                   └───────────────────┘
```

---

## 🔌 Katalog REST API V1

Seluruh endpoint REST API menggunakan format response terpadu (*Unified API Envelope*):

```json
{
  "success": true,
  "message": "Operasi berhasil dilakukan",
  "data": { ... },
  "errors": null
}
```

### Ringkasan Endpoint API

| Modul | Method | Endpoint | Hak Akses | Deskripsi |
| :--- | :---: | :--- | :---: | :--- |
| **Auth** | `POST` | `/api/v1/auth/login` | Publik | Otentikasi email & kata sandi, mengembalikan Bearer Token. |
| **Auth** | `GET` | `/api/v1/auth/me` | Bearer Token | Mengambil data akun pengguna yang sedang login. |
| **Auth** | `POST` | `/api/v1/auth/logout` | Bearer Token | Mencabut sesi token pengguna. |
| **Master** | `GET` | `/api/v1/branches` | Bearer Token | Mengambil daftar cabang aktif, titik GPS, dan radius geofence. |
| **Master** | `GET` | `/api/v1/branches/{id}` | Bearer Token | Mengambil detail spesifik kantor cabang. |
| **ESS Profil** | `GET` | `/api/v1/profile` | Bearer Token | Mengambil profil lengkap karyawan yang login. |
| **ESS Profil** | `PUT` | `/api/v1/profile` | Bearer Token | Memperbarui alamat dan nomor telepon karyawan. |
| **ESS Profil** | `POST` | `/api/v1/profile/avatar` | Bearer Token | Mengunggah foto profil baru. |
| **Kontak** | `GET` | `/api/v1/profile/emergency-contacts` | Bearer Token | Mengambil daftar kontak darurat keluarga. |
| **Kontak** | `POST` | `/api/v1/profile/emergency-contacts` | Bearer Token | Menambahkan kontak darurat baru. |
| **Kontak** | `DELETE`| `/api/v1/profile/emergency-contacts/{id}` | Bearer Token | Menghapus kontak darurat. |
| **Presensi** | `GET` | `/api/v1/attendance/today` | Bearer Token | Cek status absensi hari ini (jam masuk/pulang, shift, status). |
| **Presensi** | `POST` | `/api/v1/attendance/clock-in` | Bearer Token | Absensi masuk dengan verifikasi koordinat GPS dan foto selfie. |
| **Presensi** | `POST` | `/api/v1/attendance/clock-out` | Bearer Token | Absensi pulang dengan verifikasi koordinat GPS dan foto selfie. |
| **Presensi** | `GET` | `/api/v1/attendance/history` | Bearer Token | Riwayat absensi bulanan karyawan bersangkutan. |
| **Cuti** | `GET` | `/api/v1/leaves/balances` | Bearer Token | Mengambil sisa kuota saldo cuti aktif karyawan. |
| **Cuti** | `GET` | `/api/v1/leaves` | Bearer Token | Riwayat permohonan cuti pribadi karyawan. |
| **Cuti** | `POST` | `/api/v1/leaves` | Bearer Token | Mengajukan permohonan cuti baru dengan lampiran berkas. |
| **Cuti** | `GET` | `/api/v1/leaves/approvals` | Bearer Token | Mengambil daftar pengajuan cuti bawahan yang perlu disetujui. |
| **Cuti** | `POST` | `/api/v1/leaves/{id}/approve` | Bearer Token | Menyetujui atau menolak cuti bawahan. |
| **Lembur** | `GET` | `/api/v1/overtimes` | Bearer Token | Riwayat pengajuan lembur pribadi karyawan. |
| **Lembur** | `POST` | `/api/v1/overtimes` | Bearer Token | Mengajukan permohonan lembur baru. |
| **Lembur** | `GET` | `/api/v1/overtimes/approvals` | Bearer Token | Mengambil daftar lembur bawahan yang menunggu verifikasi. |
| **Lembur** | `POST` | `/api/v1/overtimes/{id}/approve` | Bearer Token | Menyetujui atau menolak permohonan lembur bawahan. |
| **Slip Gaji** | `GET` | `/api/v1/payslips` | Bearer Token | Riwayat daftar slip gaji disetujui (Approved / Paid). |
| **Slip Gaji** | `GET` | `/api/v1/payslips/{id}` | Bearer Token | Detail rincian pendapatan, potongan, dan net take-home pay. |
| **Slip Gaji** | `GET` | `/api/v1/payslips/{id}/download` | Bearer Token | Unduh stream berkas PDF slip gaji resmi dengan QR signature. |
| **Kasbon** | `GET` | `/api/v1/cash-advances` | Bearer Token | Riwayat daftar permohonan kasbon dan sisa saldo cicilan. |
| **Kasbon** | `POST` | `/api/v1/cash-advances` | Bearer Token | Mengajukan permohonan pinjaman kasbon baru beserta tenor cicilan. |

---

## 🚀 Panduan Instalasi & Menjalankan

### Prasyarat Lingkungan
- **PHP:** Versi 8.3 atau 8.4 (Disarankan PHP 8.4)
- **Ekstensi PHP:** `pdo_pgsql`, `mbstring`, `openssl`, `fileinfo`, `curl`, `gd`
- **Composer:** Versi 2.x
- **Node.js & NPM:** Node.js versi LTS (v18.x atau v20.x)
- **Database:** PostgreSQL 14+ / 15+ / 16+

### Langkah Pemasangan Cepat

1. **Clone Repositori:**
   ```bash
   git clone https://github.com/sulistiyas/NexusHRIS.git
   cd NexusHRIS
   ```

2. **Instal Dependensi:**
   ```bash
   composer install
   npm install
   ```

3. **Pengaturan Environment (`.env`):**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Sesuaikan parameter database di file `.env`:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=your_db_host
   DB_PORT=your_db_port
   DB_DATABASE=your_db_name
   DB_USERNAME=your_db_username
   DB_PASSWORD=your_db_password
   ```

4. **Jalankan Migrasi & Seeder Data Awal:**
   ```bash
   php artisan migrate --seed
   ```

5. **Hubungkan Storage:**
   ```bash
   php artisan storage:link
   ```

6. **Jalankan Aplikasi:**
   - Kompilasi aset frontend via Vite:
     ```bash
     npm run dev
     ```
   - Jalankan server web lokal Laravel:
     ```bash
     php artisan serve
     ```
   Aplikasi siap digunakan melalui browser di: `http://127.0.0.1:8000`.

---

## 👤 Akun Percobaan (Demo Accounts)

Tersedia akun demonstrasi bawaan untuk menguji seluruh fungsi sistem berdasarkan tingkat kewenangan:

| Peran (*Role*) | Email | Kata Sandi | Lingkup Kewenangan |
| :--- | :--- | :---: | :--- |
| **Super Administrator** | `admin@nexus.test` | `password` | Kendali penuh master data, manajemen cabang, shift, dan log audit sistem. |
| **HR Administrator** | `hr@nexus.test` | `password` | Input karyawan baru, arsip dokumen, monitoring presensi kantor & approval cuti HR. |
| **Department Manager** | `manager@nexus.test` | `password` | Pemantauan kehadiran tim, approval cuti bawahan, dan verifikasi lembur. |
| **Regular Employee** | `employee@nexus.test` | `password` | Akses ESS, absensi selfie GPS, pengajuan izin/cuti, dan pengajuan lembur. |
| *Akun Non-aktif* | `inactive@nexus.test` | `password` | Uji coba penolakan login untuk akun yang dinonaktifkan (`is_active = false`). |

---

## 📂 Struktur Direktori Repositori

Struktur direktori di bawah ini memuat berkas dan folder inti yang tersimpan dalam repositori:

```text
NexusHRIS/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/            # Controller Super Admin (Audit Logs)
│   │   │   ├── Api/V1/           # RESTful API Controllers untuk Mobile App
│   │   │   └── Auth/             # Controller Login & Logout
│   │   ├── Requests/             # Form Requests validasi input
│   │   ├── Resources/V1/         # Eloquent API Resources pembungkus JSON
│   │   └── Traits/               # ApiResponseTrait (Unified Envelope)
│   ├── Models/                   # 30+ Model Eloquent sesuai skema ERD
│   ├── Policies/                 # Policy otorisasi berkas
│   ├── Providers/                # AppServiceProvider & boot logic
│   └── Services/                 # Kalkulator bisnis murni (GeoLocation, Attendance, Leave)
├── bootstrap/                    # Bootstrap loader & routing setup
├── config/                       # Konfigurasi aplikasi, auth, permission, database
├── database/
│   ├── factories/                # Model factories untuk testing & seeding
│   ├── migrations/               # 30+ Migrasi skema database relasional
│   └── seeders/                  # Seeder peran, pengguna, tipe cuti
├── public/                       # Entry point aplikasi web & web assets
├── resources/
│   ├── css/                      # Desain styling antarmuka (Tailwind CSS v4)
│   ├── js/                       # Modul JavaScript (Leaflet map, geofencing, webcam selfie)
│   └── views/                    # Template Blade modular (layouts, components, domain views)
├── routes/
│   ├── api.php                   # Rute REST API V1 terproteksi Sanctum
│   ├── console.php               # Perintah kustom CLI Artisan
│   └── web.php                   # Rute antarmuka Web terproteksi Session
├── storage/                      # Direktori penyimpanan privat & file uploads
├── ERD.md                        # Dokumentasi skema relasi database lengkap
├── FLOWCHART.md                  # Dokumentasi diagram alur logika bisnis sistem
├── LICENSE                       # Lisensi terbuka MIT
└── README.md                     # Dokumentasi utama proyek
```

---

## 📄 Lisensi

NexusHRIS dikembangkan sebagai perangkat lunak terbuka berlisensi [MIT License](LICENSE).
Hak Cipta © 2026 NexusHRIS Development Team.
