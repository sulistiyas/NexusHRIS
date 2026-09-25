# Sprint 4 Plan: Mesin Penggajian, BPJS, Pajak PPh 21 & Slip Gaji PDF

Dokumen ini memuat rencana implementasi teknis untuk **Sprint 4** NexusHRIS. Sprint ini merupakan pusat kalkulasi finansial sistem: struktur penggajian, mesin kalkulasi batch payroll bulanan (menghubungkan data absensi, lembur, dan kasbon), perhitungan potongan regulasi Indonesia (BPJS Ketenagakerjaan, BPJS Kesehatan, dan PPh 21 skema TER 2024), serta pembuatan dan distribusi Slip Gaji PDF terproteksi.

---

## 1. Ringkasan & Ruang Lingkup Sprint

* **Durasi Target:** 2 Minggu (14 Hari)
* **Kategori Epics:** Mesin Penggajian Bulanan, Regulasi Finansial (BPJS & PPh 21 TER), Pengajuan Kasbon, dan Cetak Dokumen Slip Gaji PDF.
* **Daftar User Stories:**

| Story ID | Judul User Story | Prioritas | Estimasi Poin |
| :--- | :--- | :---: | :---: |
| **US-17** | Pengaturan Struktur Gaji Karyawan (`salary_structures`): Gaji Pokok & Tunjangan | P0 | 5 |
| **US-18** | Eksekusi *Run Payroll Batch* Bulanan: Agregasi Absensi, Lembur, & Potongan Kasbon | P0 | 8 |
| **US-19** | Otomasi Kalkulasi Potongan BPJS (TK & Kesehatan) serta Pajak PPh 21 TER 2024 | P0 | 8 |
| **US-20** | Generator Dokumen Slip Gaji PDF Otomatis (Sekali Klik) dengan QR Signature | P0 | 5 |
| **US-21** | Portal ESS: Riwayat Slip Gaji Karyawan & Unduh File PDF Resmi | P1 | 3 |
| **US-22** | Pengajuan Kasbon/Pinjaman Karyawan (`cash_advances`) dengan Skema Cicilan Gaji | P1 | 5 |

---

## 2. Rekomendasi Paket & Pustaka (Packages Recommendation)

1. **`barryvdh/laravel-dompdf` (Pembuatan Dokumen PDF):**
   * *Peran:* Me-render template Blade HTML/CSS menjadi dokumen PDF Slip Gaji berkualitas cetak (*A4/A5 layout*) dengan cepat dan stabil.
   * *Perintah:* `composer require barryvdh/laravel-dompdf`
2. **`simplesoftwareio/simple-qrcode` (Tanda Tangan Digital QR):**
   * *Peran:* Mencetak kode QR dinamis di pojok bawah slip gaji yang berisi link verifikasi keabsahan dokumen untuk mencegah pemalsuan slip gaji.
   * *Perintah:* `composer require simplesoftwareio/simple-qrcode`
3. **Laravel Database Transactions (`DB::transaction`):**
   * *Peran:* Memastikan proses kalkulasi massal puluhan hingga ratusan karyawan berjalan secara atomik (jika 1 data gagal, seluruh batch di-rollback dengan aman).

---

## 3. Rencana Arsitektur & Struktur Direktori

### A. Struktur Folder Baru yang Akan Disiapkan
```
app/
├── Actions/
│   └── Payroll/
│       ├── RunPayrollBatchAction.php
│       ├── CalculateEmployeePayslipAction.php
│       └── GeneratePayslipPdfAction.php
├── Services/
│   ├── Payroll/
│   │   ├── BpjsCalculatorService.php
│   │   ├── Pph21CalculatorService.php
│   │   └── OvertimeCalculatorService.php
├── Http/
│   ├── Controllers/
│   │   ├── SalaryStructureController.php
│   │   ├── PayrollBatchController.php
│   │   ├── PayslipController.php
│   │   ├── CashAdvanceController.php
│   │   └── Api/
│   │       └── V1/
│   │           ├── PayslipApiController.php
│   │           └── CashAdvanceApiController.php
│   ├── Requests/
│   │   ├── StoreSalaryStructureRequest.php
│   │   ├── RunPayrollBatchRequest.php
│   │   └── StoreCashAdvanceRequest.php
│   └── Resources/
│       └── V1/
│           ├── PayslipResource.php
│           └── CashAdvanceResource.php
```

---

## 4. Rincian Implementasi & Rumus Regulasi Finansial

### A. Formula Lembur (Sesuai PP 35/2021)
* **Dasar Upah Lembur Per Jam:** `Upah_Per_Jam = 1 / 173 * (Gaji_Pokok + Tunjangan_Tetap)`.
* **Kalkulasi Jam Kerja Biasa:**
  * Jam pertama: `1.5 x Upah_Per_Jam`.
  * Jam kedua dan seterusnya: `2.0 x Upah_Per_Jam`.

### B. Formula BPJS Ketenagakerjaan & Kesehatan (Standar Ketenagakerjaan RI)
1. **BPJS Kesehatan:**
   * Ditanggung Perusahaan: `4%` (Batas plafon gaji Rp 12.000.000).
   * Ditanggung Karyawan: `1%` (Dipotong dari gaji).
2. **BPJS Ketenagakerjaan:**
   * **JHT (Hari Tua):** `3.7%` perusahaan, `2%` karyawan.
   * **JP (Pensiun):** `2%` perusahaan, `1%` karyawan (ada batas plafon upah berkala).
   * **JKK (Kecelakaan Kerja):** `0.24%` s.d. `1.74%` (ditanggung perusahaan).
   * **JKM (Kematian):** `0.3%` (ditanggung perusahaan).

### C. Formula PPh 21 TER 2024 (PP 58/2023 & PMK 168/2023)
* Penghitungan PPh 21 bulanan tidak lagi menggunakan rumus rumit tahunan di setiap bulan, melainkan mengacu pada **Tarif Efektif Rata-rata (TER)**:
  * **Kategori A:** PTKP TK/0 (Rp 54 jt), TK/1 (Rp 58.5 jt), K/0 (Rp 58.5 jt).
  * **Kategori B:** PTKP TK/2, TK/3, K/1, K/2.
  * **Kategori C:** PTKP K/3.
* Rumus Bulanan: `PPh_21 = Penghasilan_Bruto_Bulan_Ini * Tarif_TER_Persen`.

### D. Alur Siklus Penggajian (`PayrollBatch`)
```
[Admin HR: Klik Run Batch Periode Bulan X]
                     │
                     ▼
             Status: DRAFT
 (Otomatis hitung data absensi, lembur, kasbon)
                     │
                     ▼
             Status: REVIEW
 (Finance memeriksa rincian & koreksi jika ada anomali)
                     │
                     ▼
            Status: APPROVED
(Kunci batch, generate slip PDF, kirim notifikasi slip)
                     │
                     ▼
              Status: PAID
        (Dana ditransfer ke bank karyawan)
```

### E. Modul Kasbon (*Cash Advance*)
* Karyawan mengajukan nominal kasbon dan pilihan jangka waktu cicilan (misal: 3 bulan).
* Jika disetujui Manager & Finance, sistem membuat jadwal cicilan.
* Saat `RunPayrollBatchAction` berjalan, sistem otomatis mengambil cicilan aktif bulan berjalan dan memasukkannya ke pos potongan gaji (`payslip_items`).

---

## 5. Spesifikasi REST API untuk Aplikasi Mobile

| Method | Endpoint | Deskripsi & Kegunaan Mobile |
| :--- | :--- | :--- |
| `GET` | `/api/v1/payslips` | Daftar riwayat slip gaji bulanan karyawan yang sudah berstatus `APPROVED` / `PAID`. |
| `GET` | `/api/v1/payslips/{id}` | Detail rincian pendapatan kotor, potongan, dan *take-home pay*. |
| `GET` | `/api/v1/payslips/{id}/download` | Mengembalikan file stream PDF slip gaji resmi atau temporary signed URL. |
| `GET` | `/api/v1/cash-advances` | Riwayat pengajuan kasbon dan sisa saldo cicilan berjalan. |
| `POST` | `/api/v1/cash-advances` | Mengajukan permohonan pinjaman baru (nominal, alasan, jumlah tenor bulan). |

---

## 6. Rencana Pengujian (Test Scenarios)

1. `test_payroll_batch_calculates_correct_overtime_pay`: Memastikan jam lembur yang telah diapprove terkalkulasi akurat sesuai pengali PP 35/2021.
2. `test_bpjs_and_pph21_ter_deductions_match_official_rates`: Memastikan nilai potongan BPJS dan persentase tarif PPh 21 TER sesuai dengan kategori PTKP karyawan.
3. `test_cash_advance_installment_is_automatically_deducted_in_payroll`: Memastikan cicilan pinjaman otomatis memotong *Take Home Pay* pada periode batch terkait.
4. `test_unapproved_payroll_batch_slip_cannot_be_viewed_by_employee`: Memastikan slip gaji berstatus `DRAFT` atau `REVIEW` tidak dapat dilihat atau diunduh oleh karyawan biasa di portal ESS/Mobile.
5. `test_pdf_payslip_generation_creates_valid_file`: Memastikan generator Dompdf menghasilkan file PDF valid dengan QR Code yang berfungsi.

---

## 7. Kriteria Selesai (Definition of Done - Sprint 4)

* [ ] Konfigurasi struktur gaji fleksibel (gaji pokok, tunjangan tetap, tunjangan variabel).
* [ ] Batch payroll berhasil memproses data absensi, hari mangkir, lembur, dan potongan kasbon secara otomatis.
* [ ] Potongan BPJS TK, BPJS Kesehatan, dan PPh 21 TER 2024 terkalkulasi dengan benar.
* [ ] Slip Gaji PDF berhasil di-generate secara rapi dan memiliki QR Code verifikasi dokumen.
* [ ] Karyawan dapat melihat dan mengunduh slip gaji mereka secara mandiri via Web dan Mobile REST API.
