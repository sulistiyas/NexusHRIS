# NexusHRIS - Master Sprint Implementation Plan & Architectural Blueprint

Dokumen ini merupakan direktori induk perencanaan teknis (*Engineering Blueprint*) untuk seluruh siklus pengembangan **NexusHRIS** berbasis Agile Scrum (Sprint 1 sampai Sprint 5). Dokumen ini mengintegrasikan seluruh kebutuhan dari [AGILE_ROADMAP.md](file:///c:/laragon/www/NexusHRIS/AGILE_ROADMAP.md), skema database [ERD.md](file:///c:/laragon/www/NexusHRIS/ERD.md), dan logika bisnis [FLOWCHART.md](file:///c:/laragon/www/NexusHRIS/FLOWCHART.md).

---

## 1. Peta Navigasi Sprint Plan

Setiap sprint memiliki dokumen panduan teknis mendalam yang mencakup rincian *User Stories*, skema database, implementasi Web & REST API, arsitektur kode (*Service/Action Pattern*), rekomendasi pustaka (*packages*), pengujian (*Unit/Feature Tests*), dan *Definition of Done (DoD)*:

| File Plan | Sprint | Fokus Utama | Target Deliverable |
| :--- | :--- | :--- | :--- |
| [Sprint 1: Fondasi, Auth & RBAC](file:///c:/laragon/www/NexusHRIS/plan/sprint-1-foundation-auth-rbac.md) | **Sprint 1** | Setup Laravel, Tailwind/Livewire, Migrations, RBAC, Audit Log, REST API Baseline (Sanctum) | Web Login, API Auth, Role Shield, Audit Trail |
| [Sprint 2: Struktur Organisasi & Karyawan](file:///c:/laragon/www/NexusHRIS/plan/sprint-2-org-structure-employee.md) | **Sprint 2** | Cabang Geofence GPS, Departemen, Jabatan, Onboarding Karyawan, Berkas KTP/Ijazah, Portal ESS | Master Organisasi, Akun Auto-generate, Profil ESS |
| [Sprint 3: Presensi GPS, Shift & Cuti](file:///c:/laragon/www/NexusHRIS/plan/sprint-3-attendance-leave-shift.md) | **Sprint 3** | Presensi Geofencing (Haversine), Foto Selfie Kamera, Kalkulasi Terlambat, Approval Cuti & Lembur | Smart Attendance Web/Mobile, Approval Flow |
| [Sprint 4: Payroll Engine, Pajak & Slip PDF](file:///c:/laragon/www/NexusHRIS/plan/sprint-4-payroll-bpjs-tax-slip.md) | **Sprint 4** | Struktur Gaji, Kalkulasi Batch Gaji, Potongan BPJS (TK & Kes), PPh 21 TER 2024, Kasbon, Slip PDF | Mesin Penggajian Otomatis, Slip Gaji PDF |
| [Sprint 5: Reimbursement, Aset & Rilis](file:///c:/laragon/www/NexusHRIS/plan/sprint-5-reimbursement-asset-analytics-release.md) | **Sprint 5** | Klaim Biaya, Inventaris Aset, Dashboard Analitik, Export Excel, Optimasi Query & Final Release | Modul Operasional, Export Data, Production Ready |

---

## 2. Arsitektur Bersama: Web (Livewire/Blade) + Mobile-Ready (REST API)

Salah satu prinsip utama dalam rancangan NexusHRIS adalah **Shared Business Logic** (*Single Source of Truth*). Logika bisnis esensial (seperti validasi radius absensi, perhitungan denda keterlambatan, kalkulasi payroll, dan kalkulasi PPh 21) **tidak boleh diduplikasi** di antara Controller Web dan Controller API.

```
                           ┌───────────────────────────────┐
                           │      Web Browser (Blade)      │
                           └───────────────┬───────────────┘
                                           │ Session / CSRF
                                           ▼
                           ┌───────────────────────────────┐
                           │   Livewire / Web Controller   │
                           └───────────────┬───────────────┘
                                           │
                                           │ Invokes
                                           ▼
┌──────────────────────────┐       ┌───────────────┐       ┌──────────────────────────┐
│ Mobile App (Flutter/RN)  │──────▶│ Action /      │◀──────│  Rest API Controller     │
└──────────────────────────┘ Bearer│ Service Layer │       │  (App\Http\Controllers   │
                             Token └───────┬───────┘       │   \Api\V1\...)           │
                             (Sanctum)     │               └──────────────────────────┘
                                           ▼
                                   ┌───────────────┐
                                   │ Eloquent ORM  │
                                   └───────┬───────┘
                                           ▼
                                   ┌───────────────┐
                                   │ MySQL Database│
                                   └───────────────┘
```

### Aturan Arsitektur Layering:
1. **Controllers (Web & API):** Berperan sebagai koordinator HTTP (menerima request, memvalidasi input via FormRequest, memanggil Action/Service, dan mengembalikan format response).
2. **Action / Service Classes (`app/Actions` & `app/Services`):** Menampung *domain logic* murni. Contoh: `CalculatePayrollAction`, `ClockInAction`, `ApproveLeaveAction`.
3. **Eloquent API Resources (`app/Http/Resources`):** Mentransformasi model menjadi JSON standar untuk konsumsi mobile. Menghindari kebocoran data sensitif (hash password, token) dan membakukan format tanggal (ISO 8601).
4. **Unified API Envelope:** Semua response API wajib mengikuti struktur JSON seragam:
   ```json
   {
     "success": true,
     "message": "Operasi berhasil",
     "data": { ... },
     "meta": null
   }
   ```

---

## 3. Rekomendasi Pustaka & Paket (Packages & Tooling Stack)

Berdasarkan analisis kebutuhan fungsional NexusHRIS, berikut paket-paket resmi dan standar industri yang direkomendasikan:

| Kategori | Paket / Tool | Alasan & Kegunaan |
| :--- | :--- | :--- |
| **API Auth & Security** | `laravel/sanctum` | Autentikasi token ringan untuk Mobile App & SPA. |
| **Role & Permission** | `spatie/laravel-permission` | RBAC standar industri: kelola peran (`super_admin`, `hr_admin`, `manager`, `employee`) dan izin granuler. |
| **Audit Trail** | `spatie/laravel-activitylog` | Pencatatan otomatis riwayat aksi pengguna ke tabel `activity_logs`. |
| **PDF Generation** | `barryvdh/laravel-dompdf` atau `spatie/laravel-pdf` | Pembuatan Slip Gaji karyawan (Sprint 4) dan Surat Peringatan secara cepat dan presisi. |
| **Excel Export/Import** | `maatwebsite/excel` atau `openspout/openspout` | Ekspor rekapitulasi presensi bulanan dan laporan payroll ke format `.xlsx` / `.csv`. |
| **Image Processing** | `intervention/image` | Kompresi dan penyesuaian resolusi foto selfie presensi (Sprint 3) dan struk reimbursement (Sprint 5) agar hemat penyimpanan. |
| **QR Code Signature** | `simplesoftwareio/simple-qrcode` | Menghasilkan kode QR validasi keaslian dokumen pada Slip Gaji PDF. |
| **Map & Geofencing (Frontend)** | `Leaflet.js` & `OpenStreetMap` | Menampilkan radius kantor cabang dan visualisasi posisi karyawan tanpa biaya lisensi Google Maps API. |
| **Charts & Analytics** | `ApexCharts` / `Chart.js` | Visualisasi grafik rasio kehadiran harian dan pengeluaran gaji di Dashboard Eksekutif. |
| **Code Style & Quality** | `laravel/pint` | Memastikan seluruh kode mengikuti standar PSR-12 dan konvensi Laravel secara otomatis. |

---

## 4. Standar & Aturan Rekayasa (Engineering Rules & Guidelines)

Semua implementasi dari Sprint 1 hingga Sprint 5 wajib mematuhi aturan berikut:

1. **Strict Types & Clean PHP 8.3/8.4:**
   * Gunakan deklarasi tipe data eksplisit pada semua parameter dan *return type*.
   * Gunakan *constructor property promotion*.
2. **Pencegahan Masalah N+1 Query:**
   * Aktifkan strict mode Eloquent di `AppServiceProvider`:
     ```php
     Model::preventLazyLoading(! app()->isProduction());
     ```
   * Selalu gunakan Eager Loading (`with(['department', 'designation'])`) pada query yang di-loop.
3. **Form Request untuk Seluruh Validasi:**
   * Jangan menulis logika validasi panjang di dalam Controller. Buat Form Request class terpisah (`StoreEmployeeRequest`, `ClockInRequest`).
4. **Proteksi File & Media Pribadi:**
   * Foto selfie absensi, dokumen KTP/NPWP, dan Slip Gaji **tidak boleh** ditaruh di `public/` storage yang bisa diakses publik secara bebas. Simpan di disk privat (`storage/app/private`) dan sajikan melalui rute terproteksi atau *Temporary Signed URL*.
5. **Background Queues untuk Tugas Berat:**
   * Pengiriman email aktivasi akun, kalkulasi massal payroll, dan pembuatan dokumen PDF massal wajib diproses melalui Laravel Queue (`php artisan queue:work`).
