# NexusHRIS - System Architecture & Business Process Flowcharts

Dokumen ini berisi diagram alur logika bisnis (*Business Process Flowcharts*) menyeluruh untuk aplikasi **NexusHRIS**. Semua diagram dirancang menggunakan format **Mermaid** standar sehingga dapat divisualisasikan langsung di VS Code, GitHub, maupun viewer Markdown lainnya.

---

## Daftar Isi Diagram
1. [Flowchart 1: Alur Login & Hak Akses Berdasarkan Peran (RBAC)](#1-alur-login--hak-akses-rbac)
2. [Flowchart 2: Alur Presensi Cerdas (Smart Attendance - GPS & Selfie)](#2-alur-presensi-cerdas-gps--selfie)
3. [Flowchart 3: Alur Pengajuan Cuti & Izin Bertingkat (Leave Approval)](#3-alur-pengajuan-cuti--izin-bertingkat)
4. [Flowchart 4: Alur Pengajuan Lembur (Overtime Workflow)](#4-alur-pengajuan-lembur-overtime)
5. [Flowchart 5: Mesin Otomasi Penggajian Bulanan (Monthly Payroll Engine)](#5-mesin-otomasi-penggajian-bulanan-payroll-engine)
6. [Flowchart 6: Alur Klaim Biaya (Reimbursement) & Kasbon](#6-alur-klaim-biaya-reimbursement--kasbon)
7. [Flowchart 7: Siklus Hidup Karyawan (Employee Lifecycle: Onboarding hingga Offboarding)](#7-siklus-hidup-karyawan-employee-lifecycle)

---

## 1. Alur Login & Hak Akses (RBAC)

Alur penentuan hak akses pengguna saat autentikasi masuk ke sistem.

```mermaid
flowchart TD
    Start(["Mulai: Pengguna Membuka Web"]) --> LoginUI["Tampilkan Halaman Login"]
    LoginUI --> Submit["Input Email & Password"]
    Submit --> CheckCred{"Kredensial Valid?"}
    
    CheckCred -- "Tidak" --> ErrLogin["Tampilkan Pesan Error / Kredensial Salah"] --> LoginUI
    CheckCred -- "Ya" --> CheckActive{"Status Akun Aktif?"}
    
    CheckActive -- "Tidak" --> Deactivated["Tolak Akses: Akun Dinonaktifkan"] --> EndFail(["Selesai"])
    CheckActive -- "Ya" --> LogAudit["Catat Login ke activity_logs"]
    
    LogAudit --> RoleCheck{"Cek Role Pengguna"}
    
    RoleCheck -- "Super Admin" --> DashAdmin["Akses Dashboard Master: Kelola Cabang, Pengguna, Audit Trail, & Backup"]
    RoleCheck -- "HR Manager / Staff" --> DashHR["Akses Dashboard HR: Master Karyawan, Presensi, Cuti, Payroll, & Laporan"]
    RoleCheck -- "Line Manager" --> DashManager["Akses Dashboard Tim: Approval Cuti, Lembur, Klaim, & Jadwal Shift"]
    RoleCheck -- "Employee (Karyawan)" --> DashESS["Akses Portal Mandiri (ESS): Absen Masuk/Pulang, Cuti, Slip Gaji, & Profil"]

    DashAdmin --> Work(["Bekerja Sesuai Peran"])
    DashHR --> Work
    DashManager --> Work
    DashESS --> Work
```

---

## 2. Alur Presensi Cerdas (GPS & Selfie)

Alur absensi masuk (*Clock-In*) dengan validasi geolokasi radius kantor dan foto wajah.

```mermaid
flowchart TD
    StartAbsen(["Karyawan Buka Fitur Absensi"]) --> GetLoc["Dapatkan Koordinat GPS Karyawan (Lat, Long)"]
    GetLoc --> CheckWorkType{"Tipe Kerja Hari Ini?"}
    
    CheckWorkType -- "WFH (Work From Home)" --> CaptureSelfie["Ambil Foto Selfie via Kamera"]
    
    CheckWorkType -- "WFO (Work From Office)" --> CalcDistance["Hitung Jarak (Haversine Formula) ke Kantor Cabang"]
    CalcDistance --> CheckRadius{"Jarak <= Radius Toleransi (misal 50m)?"}
    
    CheckRadius -- "Di Luar Radius" --> OutOfRadius["Tolak Absensi: Berada di Luar Area Kantor"] --> EndFail(["Absensi Gagal"])
    CheckRadius -- "Dalam Radius" --> CaptureSelfie
    
    CaptureSelfie --> GetShift["Ambil Jadwal Shift Hari Ini"]
    GetShift --> CheckTime{"Waktu Sekarang vs Jam Mulai Shift"}
    
    CheckTime -- "<= Jam Masuk + Toleransi" --> StatOnTime["Set Status: PRESENT (Tepat Waktu)"]
    CheckTime -- "> Jam Masuk + Toleransi" --> StatLate["Hitung Menit Keterlambatan<br/>Set Status: LATE (Terlambat)"]
    
    StatOnTime --> SaveRecord["Simpan Data ke Tabel attendances:<br/>- Jam Masuk<br/>- File Selfie Path<br/>- Koordinat GPS<br/>- Status Kehadiran"]
    StatLate --> SaveRecord
    
    SaveRecord --> SuccessNotice["Tampilkan Konfirmasi Berhasil Absen"] --> Done(["Selesai"])
```

---

## 3. Alur Pengajuan Cuti & Izin Bertingkat

Alur pengajuan cuti karyawan dengan verifikasi kuota dan persetujuan multi-level.

```mermaid
flowchart TD
    StartCuti(["Karyawan Mengajukan Cuti"]) --> FormCuti["Pilih Tipe Cuti, Tanggal Mulai, Tanggal Selesai, & Alasan"]
    FormCuti --> CheckType{"Jenis Cuti?"}
    
    CheckType -- "Izin Sakit" --> UploadSurat["Wajib Upload Surat Keterangan Dokter"] --> SubmitRequest
    CheckType -- "Cuti Tahunan" --> CheckQuota{"Sisa Kuota Cuti Cukup?"}
    
    CheckQuota -- "Habis / Tidak Cukup" --> QuotaReject["Peringatan: Kuota Cuti Tahunan Tidak Mencukupi"] --> FormCuti
    CheckQuota -- "Cukup" --> SubmitRequest["Simpan Pengajuan (Status: PENDING)"]
    
    SubmitRequest --> NotifyManager["Kirim Notifikasi ke Manager Langsung"]
    NotifyManager --> ManagerReview{"Review oleh Manager"}
    
    ManagerReview -- "Ditolak" --> MgrReject["Update Status: REJECTED<br/>Input Alasan Penolakan"] --> NotifyEmployee["Notifikasi Penolakan ke Karyawan"] --> EndCuti(["Selesai"])
    
    ManagerReview -- "Disetujui" --> CheckNeedHR{"Perlu Approval Final HR?"}
    
    CheckNeedHR -- "Ya" --> HRReview{"Review oleh HR Admin"}
    HRReview -- "Ditolak" --> HRReject["Update Status: REJECTED"] --> NotifyEmployee
    HRReview -- "Disetujui" --> FinalApproved
    
    CheckNeedHR -- "Tidak" --> FinalApproved["Update Status: APPROVED"]
    
    FinalApproved --> DeductQuota["Potong Saldo Kuota di leave_balances"]
    DeductQuota --> CalendarUpdate["Tandai Jadwal di Kalender Kehadiran"]
    CalendarUpdate --> NotifySuccess["Kirim Notifikasi Persetujuan ke Karyawan"] --> EndSuccess(["Pengajuan Selesai"])
```

---

## 4. Alur Pengajuan Lembur (Overtime)

Alur verifikasi lembur kerja yang akan otomatis dikonversikan ke komponen upah payroll.

```mermaid
flowchart TD
    StartOT(["Karyawan Input Form Lembur"]) --> FormOT["Pilih Tanggal, Jam Mulai, Jam Selesai, & Deskripsi Pekerjaan"]
    FormOT --> CalcHours["Hitung Total Estimasi Jam Lembur"]
    CalcHours --> SubmitOT["Simpan Pengajuan Lembur (Status: PENDING)"]
    
    SubmitOT --> NotifySpv["Notifikasi ke Supervisor / Manager"]
    NotifySpv --> SpvAction{"Keputusan Supervisor"}
    
    SpvAction -- "Ditolak" --> OTReject["Status: REJECTED (Disertai Alasan)"] --> EndOT(["Pengajuan Ditolak"])
    SpvAction -- "Disetujui" --> CheckActualAtt{"Karyawan Melakukan Clock-Out Aktual Lembur?"}
    
    CheckActualAtt -- "Tidak Hadir" --> VoidOT["Batalkan Lembur: Karyawan Tidak Hadir"] --> EndOT
    CheckActualAtt -- "Hadir" --> OTApproved["Status: APPROVED (Simpan Total Jam Terverifikasi)"]
    
    OTApproved --> IntegratePayroll["Data Siap Ditarik ke Perhitungan Payroll Bulanan"] --> FinishOT(["Selesai"])
```

---

## 5. Mesin Otomasi Penggajian Bulanan (Payroll Engine)

Siklus pemrosesan gaji karyawan secara massal setiap akhir periode *cut-off*.

```mermaid
flowchart TD
    StartPayroll(["HR Memulai Periode Payroll Bulanan"]) --> SetBatch["Tentukan Tanggal Cut-off (misal: 21 Bln Lalu s/d 20 Bln Ini)"]
    SetBatch --> CreateBatch["Buat Record di payroll_batches (Status: DRAFT)"]
    
    CreateBatch --> LoopEmployees["Looping Semua Karyawan Aktif"]
    
    LoopEmployees --> FetchBase["Ambil Gaji Pokok & Tunjangan Tetap dari salary_structures"]
    FetchBase --> FetchAtt["Kalkulasi Kehadiran dari Tabel attendances:<br/>- Total Hari Masuk<br/>- Total Hari Terlambat / Mangkir"]
    FetchAtt --> FetchOT["Tarik Total Jam Lembur APPROVED dari Tabel overtimes"]
    FetchOT --> FetchCashAdv["Cek Tagihan Cicilan Kasbon Aktif di cash_advances"]
    
    FetchCashAdv --> FormulaGross["Hitung Pendapatan Kotor (Gross):<br/>Gaji Pokok + Tunjangan + Upah Lembur"]
    FormulaGross --> FormulaDeduct["Hitung Potongan (Deductions):<br/>- BPJS Ketenagakerjaan & Kesehatan<br/>- Estimasi Pajak PPh 21<br/>- Potongan Mangkir/Telat<br/>- Potongan Cicilan Kasbon"]
    FormulaDeduct --> FormulaNet["Hitung Gaji Bersih (Net Salary):<br/>Total Gross - Total Deductions"]
    
    FormulaNet --> SavePayslip["Simpan Data ke payslips & payslip_items"]
    SavePayslip --> CheckMore{"Masih Ada Karyawan?"}
    
    CheckMore -- "Ya" --> LoopEmployees
    CheckMore -- "Tidak" --> BatchReview["HR & Finance Melakukan Verifikasi Rekapitulasi"]
    
    BatchReview --> ApprovalFinance{"Approval Pembayaran?"}
    ApprovalFinance -- "Ada Revisi" --> EditPayslip["Koreksi Manual Komponen Tertentu"] --> BatchReview
    ApprovalFinance -- "Disetujui" --> CloseBatch["Update payroll_batches (Status: PAID)"]
    
    CloseBatch --> ExportBank["Export File Rekap Transfer Bank (BCA / Mandiri / CSV)"]
    CloseBatch --> GenPDF["Generate Slip Gaji PDF Otomatis"]
    GenPDF --> QueueEmail["Antrian Background Job (Queue): Kirim Slip Gaji ke Email Karyawan"]
    
    ExportBank --> EndPayroll(["Proses Penggajian Selesai"])
    QueueEmail --> EndPayroll
```

---

## 6. Alur Klaim Biaya (Reimbursement) & Kasbon

Alur klaim pengeluaran operasional kerja dan pinjaman dana darurat.

```mermaid
flowchart TD
    StartClaim(["Karyawan Mengajukan Klaim Biaya"]) --> InputClaim["Input Kategori, Nominal, Tanggal, & Deskripsi"]
    InputClaim --> UploadReceipt["Upload Foto Struk / Kuitansi Pembayaran Asli"]
    UploadReceipt --> SubmitClaim["Submit Klaim (Status: PENDING)"]
    
    SubmitClaim --> MgrReview{"Review Atasan Langsung"}
    MgrReview -- "Ditolak" --> ClaimRejected["Status: REJECTED"] --> EndClaim(["Selesai"])
    
    MgrReview -- "Disetujui" --> FinReview{"Verifikasi Tim Keuangan (Finance)"}
    FinReview -- "Bukti Tidak Valid" --> ClaimRejected
    
    FinReview -- "Valid & Sesuai Budget" --> ClaimApproved["Status: APPROVED"]
    ClaimApproved --> PayoutChoice{"Metode Pencairan"}
    
    PayoutChoice -- "Transfer Langsung" --> DirectPay["Transfer ke Rekening Karyawan & Upload Bukti Bayar"]
    PayoutChoice -- "Gabung ke Payroll" --> AddToPayrollItem["Ditambahkan ke Komponen Slip Gaji Bulan Berjalan"]
    
    DirectPay --> MarkDisbursed["Update Status: DISBURSED"] --> DoneClaim(["Selesai"])
    AddToPayrollItem --> MarkDisbursed
```

---

## 7. Siklus Hidup Karyawan (Employee Lifecycle)

Alur lengkap sejak onboarding hingga serah terima aset saat offboarding.

```mermaid
flowchart TD
    subgraph Onboarding ["1. Fase Masuk (Onboarding)"]
        A1["Karyawan Baru Diterima"] --> A2["HR Input Biodata & Buat Akun Sistem"]
        A2 --> A3["Upload Berkas (KTP, NPWP, Kontrak, Foto)"]
        A3 --> A4["Penetapan Gaji, Shift Kerja, & Kuota Cuti Awal"]
        A4 --> A5["Serah Terima Aset (Laptop, ID Card, Kendaraan) -> asset_assignments"]
    end

    subgraph ActivePhase ["2. Fase Aktif (Active Employment)"]
        A5 --> B1["Karyawan Aktif Bekerja"]
        B1 --> B2["Presensi Harian, Cuti, & Lembur Rutin"]
        B2 --> B3["Penerimaan Gaji Bulanan Otomatis"]
        B3 --> B4{"Ada Evaluasi / Pelanggaran?"}
        B4 -- "Pelanggaran" --> B5["Penerbitan Surat Peringatan (SP 1, SP 2, SP 3)"] --> B1
        B4 -- "Kinerja Bagus" --> B6["Promosi / Penyesuaian Gaji -> career_histories"] --> B1
        B4 -- "Normal" --> B1
    end

    subgraph Offboarding ["3. Fase Keluar (Offboarding)"]
        B1 --> C1{"Pemicu Berhenti (Resign / Habis Kontrak / PHK)"}
        C1 --> C2["HR Inisiasi Form Exit Clearance"]
        C2 --> C3["Pengembalian Seluruh Aset Kantor (Cek Kondisi Fisik)"]
        C3 --> C4["Kalkulasi Hak Akhir (Sisa Gaji, Kompensasi PKWT/Pesangon)"]
        C4 --> C5["Penonaktifan Akun Login (users.is_active = FALSE)"]
        C5 --> C6["Penerbitan Surat Pengalaman Kerja / Paklaring"]
    end
    
    C6 --> EndLifecycle(["Karyawan Resmi Non-Aktif"])
```
