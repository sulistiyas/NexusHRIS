# Sprint 5 Plan: Reimbursement, Inventaris Aset, Analitik & Peluncuran

Dokumen ini memuat rencana implementasi teknis untuk **Sprint 5** NexusHRIS (Sprint Terakhir). Fokus utama sprint ini adalah melengkapi modul operasional harian (klaim reimbursement dengan upload struk, inventaris aset dan serah terima perangkat), membangun dashboard analitik eksekutif, ekspor laporan ke Excel (.xlsx), optimasi performa query, pengerasan keamanan sistem (*Security Hardening*), serta evaluasi kesiapan rilis produksi (*Production Release Readiness*).

---

## 1. Ringkasan & Ruang Lingkup Sprint

* **Durasi Target:** 2 Minggu (14 Hari)
* **Kategori Epics:** Klaim Biaya Operasional (*Reimbursement*), Manajemen Aset Perusahaan, Dashboard Visual Eksekutif, Ekspor Data Excel, & Final System Hardening.
* **Daftar User Stories:**

| Story ID | Judul User Story | Prioritas | Estimasi Poin |
| :--- | :--- | :---: | :---: |
| **US-23** | Pengajuan Klaim Biaya (*Reimbursement*) dengan Lampiran Foto Bukti/Struk Kuitansi | P1 | 5 |
| **US-24** | Verifikasi & Pencairan (*Disbursement*) Klaim Reimbursement oleh Tim Finance | P1 | 3 |
| **US-25** | Manajemen Aset Perusahaan (`assets`) & Log Serah Terima Karyawan (`asset_assignments`) | P1 | 5 |
| **US-26** | Dashboard Analitik Visual untuk Manajemen: Rasio Presensi, Tren Biaya Payroll, & Headcount | P0 | 5 |
| **US-27** | Ekspor Laporan Presensi Bulanan & Rekapitulasi Penggajian ke Format **Excel (.xlsx)** | P0 | 5 |
| **US-28** | Optimasi Performa Query, Konfigurasi Caching, Audit Keamanan, & Simulasi Rilis | P0 | 5 |

---

## 2. Rekomendasi Paket & Pustaka (Packages Recommendation)

1. **`maatwebsite/excel` (Ekspor & Impor Data Spreadsheet):**
   * *Peran:* Menghasilkan file Excel (.xlsx) dengan styling rapi, formula otomatis, dan dukungan ekspor berbasis antrean background (*Queue*) untuk data skala besar tanpa membebani memori server.
   * *Perintah:* `composer require maatwebsite/excel`
2. **`ApexCharts` / `Chart.js` (Visualisasi Grafik Dashboard):**
   * *Peran:* Merender grafik interaktif tren kehadiran karyawan, rasio absensi departemen, dan kurva biaya pengeluaran payroll perusahaan di Dashboard Eksekutif.
3. **`intervention/image` (Kompresi Struk Kuitansi):**
   * *Peran:* Mengompresi foto struk klaim biaya yang diunggah dari kamera smartphone agar ukuran file ringan (< 500 KB) dan cepat dilihat saat proses audit finance.

---

## 3. Rencana Arsitektur & Struktur Direktori

### A. Struktur Folder Baru yang Akan Disiapkan
```
app/
├── Actions/
│   ├── Reimbursement/
│   │   ├── SubmitReimbursementAction.php
│   │   └── DisburseReimbursementAction.php
│   └── Asset/
│       ├── AssignAssetAction.php
│       └── ReturnAssetAction.php
├── Exports/
│   ├── MonthlyAttendanceExport.php
│   └── PayrollSummaryExport.php
├── Services/
│   └── Analytics/
│       └── DashboardMetricService.php
├── Http/
│   ├── Controllers/
│   │   ├── ReimbursementController.php
│   │   ├── AssetController.php
│   │   ├── ReportController.php
│   │   ├── DashboardController.php
│   │   └── Api/
│   │       └── V1/
│   │           ├── ReimbursementApiController.php
│   │           ├── AssetApiController.php
│   │           └── DashboardApiController.php
│   ├── Requests/
│   │   ├── StoreReimbursementRequest.php
│   │   └── AssignAssetRequest.php
│   └── Resources/
│       └── V1/
│           ├── ReimbursementResource.php
│           └── AssetResource.php
```

---

## 4. Rincian Implementasi Logika Bisnis

### US-23 & US-24: Modul Klaim Biaya (Reimbursement)
* **Kategori Biaya (`reimbursement_categories`):** Medis/Kacamata, Transportasi/BBM, Konsumsi Klien, Pelatihan/Sertifikasi.
* **Alur Status Transaksi:**
  ```
  Karyawan Mengajukan ──▶ Status: SUBMITTED (Approval Manager)
                                   │
                                   ▼
                            Status: APPROVED
                                   │
                                   ▼
                      Finance Verifikasi & Transfer
                                   │
                                   ▼
                            Status: DISBURSED
  ```
* **Proteksi Lampiran:** Foto struk disimpan di storage privat (`storage/app/private/reimbursements/`) dan hanya dapat diakses oleh karyawan pengaju, manager terkait, serta role Finance/HR.

### US-25: Inventaris Aset Kantor & Serah Terima Perangkat
* **Status Aset:** `AVAILABLE` (tersedia), `ASSIGNED` (dipinjamkan ke karyawan), `UNDER_MAINTENANCE` (servis), `DISPOSED` (rusak/dihapus).
* **Fitur Penyerahan (`asset_assignments`):**
  * Mencatat tanggal penyerahan, kondisi fisik awal (misal: "Layar mulus, charger ori"), dan persetujuan tanda terima karyawan.
  * Saat dikembalikan (*return*), catat kondisi akhir untuk audit depresiasi/kerusakan.

### US-26: Dashboard Analitik Eksekutif
* **Metrik Utama yang Disajikan:**
  1. *Total Karyawan Aktif vs Nonaktif* (kartu counter).
  2. *Rasio Kehadiran Hari Ini*: Persentase On-Time, Terlambat, Izin/Cuti, dan Alpha (Mangkir).
  3. *Grafik Tren Pengeluaran Payroll*: Komparasi pengeluaran gaji bruto vs neto 6 bulan terakhir.
  4. *Distribusi Headcount*: Grafik sebaran karyawan per kantor cabang dan departemen.
* **Strategi Caching:**
  * Metrik dashboard dibungkus dengan cache Redis/Database (misal TTL 15 menit) agar query berat tidak dijalankan berulang-ulang:
    ```php
    Cache::remember('executive_dashboard_metrics', now()->addMinutes(15), function () {
        return $this->dashboardService->compileMetrics();
    });
    ```

### US-27: Ekspor Data ke Excel (.xlsx)
* **Dua Laporan Wajib:**
  1. *Rekapitulasi Presensi Bulanan*: Tanggal, NIP, Nama, Cabang, Jumlah Hadir, Terlambat (menit), Cuti, Sakit, Mangkir.
  2. *Rekapitulasi Penggajian*: NIP, Nama, Gaji Pokok, Total Tunjangan, Lembur, Potongan BPJS TK, BPJS Kes, PPh 21, Potongan Kasbon, Take Home Pay.
* **Implementasi:** Menggunakan kelas `Maatwebsite\Excel\Concerns\FromCollection`, `WithHeadings`, `ShouldAutoSize`, dan `WithStyles`.

### US-28: Optimasi Sistem & Audit Keamanan Sebelum Rilis
1. **N+1 Query Elimination:** Jalankan audit di semua list view untuk memastikan eager loading (`with(...)`) diterapkan pada relasi relasional.
2. **Database Indexing:** Verifikasi indeks pada kolom pencarian dan filtering sering: `attendance_date`, `employee_code`, `status`, `branch_id`.
3. **Konfigurasi Keamanan API & Web:**
   * Rate limiting: Batasi request API (`throttle:api`, misal 60 req/menit).
   * CORS: Atur konfigurasi `config/cors.php` agar hanya mengizinkan domain frontend resmi atau mobile client.
   * `APP_DEBUG=false` untuk production.

---

## 5. Spesifikasi REST API untuk Aplikasi Mobile

| Method | Endpoint | Deskripsi & Kegunaan Mobile |
| :--- | :--- | :--- |
| `GET` | `/api/v1/reimbursements` | Riwayat klaim biaya karyawan beserta status approval. |
| `POST` | `/api/v1/reimbursements` | Mengajukan klaim biaya baru (multipart: `category_id`, `amount`, `description`, `receipt_image`). |
| `GET` | `/api/v1/assets/my-assets` | Daftar aset kantor yang sedang dipinjamkan ke karyawan (Laptop, Monitor, dll). |
| `GET` | `/api/v1/dashboard/summary` | Ringkasan metrik cepat untuk aplikasi mobile (khusus pengguna role Manager / Eksekutif). |

---

## 6. Rencana Pengujian (Test Scenarios)

1. `test_employee_can_submit_reimbursement_with_receipt_image`: Memastikan pengajuan klaim biaya dan upload file struk tersimpan dengan status `SUBMITTED`.
2. `test_finance_can_disburse_approved_reimbursement`: Memastikan finance dapat mengubah status klaim menjadi `DISBURSED`.
3. `test_asset_status_updates_to_assigned_when_given_to_employee`: Memastikan status aset berubah otomatis dari `AVAILABLE` menjadi `ASSIGNED` saat serah terima.
4. `test_excel_attendance_export_generates_valid_file`: Memastikan ekspor data presensi menghasilkan file `.xlsx` yang dapat diunduh tanpa error.
5. `test_api_rate_limiting_throttles_excessive_requests`: Memastikan proteksi *throttling* aktif saat request API melampaui ambang batas.

---

## 7. Checklist Kesiapan Peluncuran Produksi (Production Launch Checklist)

* [ ] Seluruh migrasi database dan database seeder berjalan 100% mulus dari database kosong (`migrate:fresh --seed`).
* [ ] Mode *Debug* dimatikan pada server produksi (`APP_DEBUG=false`).
* [ ] Storage link terpasang dengan benar (`php artisan storage:link`) dan file sensitif diproteksi di disk privat.
* [ ] Antrean background (*Queue Worker*) berjalan aktif menggunakan Supervisor atau systemd daemon.
* [ ] Scheduler cron job berjalan aktif (`php artisan schedule:run` per menit).
* [ ] Seluruh endpoint REST API versi 1 (`/api/v1/...`) memiliki dokumentasi endpoint (Postman / OpenAPI Swagger) yang siap digunakan tim pengembang aplikasi mobile.
