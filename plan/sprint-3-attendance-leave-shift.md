# Sprint 3 Plan: Presensi Cerdas, Shift & Manajemen Cuti/Izin

Dokumen ini memuat rencana implementasi teknis untuk **Sprint 3** NexusHRIS. Sprint ini merupakan modul operasional harian paling penting: absensi berbasis lokasi GPS (Haversine Geofencing) & foto selfie, master jadwal shift kerja, deteksi keterlambatan otomatis, alur pengajuan & persetujuan cuti bertingkat (*Leave Approval*), serta pengajuan lembur (*Overtime*).

---

## 1. Ringkasan & Ruang Lingkup Sprint

* **Durasi Target:** 2 Minggu (14 Hari)
* **Kategori Epics:** Presensi Cerdas (GPS + Selfie), Jadwal Shift, Manajemen Saldo Cuti & Izin, Approval Berjenjang, dan Surat Perintah Lembur.
* **Daftar User Stories:**

| Story ID | Judul User Story | Prioritas | Estimasi Poin |
| :--- | :--- | :---: | :---: |
| **US-11** | Master Shift Kerja (`shifts`): Jam Masuk, Jam Pulang, & Toleransi Keterlambatan (menit) | P0 | 3 |
| **US-12** | Absensi Harian (Clock-In & Clock-Out) dengan Verifikasi GPS & Foto Selfie Kamera | P0 | 8 |
| **US-13** | Otomasi Status Kehadiran (`ON_TIME`, `LATE`, `EARLY_LEAVE`) & Hitung Menit Terlambat | P0 | 5 |
| **US-14** | Pengajuan Cuti Tahunan & Izin Sakit (Upload Surat Dokter) & Cek Sisa Saldo Cuti | P0 | 5 |
| **US-15** | Alur Persetujuan Cuti Bertingkat (*Line Manager* & *HR Admin*) dengan Catatan Alasan | P0 | 5 |
| **US-16** | Pengajuan & Verifikasi Surat Perintah Lembur (*Overtime*) oleh Atasan Langsung | P1 | 5 |

---

## 2. Rekomendasi Paket & Pustaka (Packages Recommendation)

1. **Backend Haversine Geolocation Engine:**
   * *Peran:* Algoritma matematis murni di PHP untuk menghitung jarak akurat dalam satuan meter antara koordinat GPS pengguna dan koordinat kantor cabang. Tidak membutuhkan paket pihak ketiga eksternal, cukup implementasikan Service murni di `App\Services\GeoLocationService`.
2. **`intervention/image` (Kompresi Foto Selfie Absensi):**
   * *Peran:* Mengompresi foto selfie absensi (misal ke ukuran 640x480 px, kualitas 70%) agar tidak menghabiskan bandwidth mobile saat upload dan menghemat kapasitas server.
3. **Laravel Notification System (Database + Mail):**
   * *Peran:* Mengirimkan notifikasi langsung ke dashboard atasan saat ada pengajuan cuti/lembur baru dan mengirimkan email konfirmasi ke karyawan saat cuti disetujui/ditolak.

---

## 3. Rencana Arsitektur & Struktur Direktori

### A. Struktur Folder Baru yang Akan Disiapkan
```
app/
├── Actions/
│   ├── Attendance/
│   │   ├── ClockInAction.php
│   │   └── ClockOutAction.php
│   ├── Leave/
│   │   ├── SubmitLeaveRequestAction.php
│   │   └── ApproveLeaveAction.php
│   └── Overtime/
│       └── SubmitOvertimeAction.php
├── Services/
│   ├── GeoLocationService.php
│   └── AttendanceCalculationService.php
├── Http/
│   ├── Controllers/
│   │   ├── ShiftController.php
│   │   ├── AttendanceController.php
│   │   ├── LeaveRequestController.php
│   │   ├── OvertimeController.php
│   │   └── Api/
│   │       └── V1/
│   │           ├── AttendanceApiController.php
│   │           ├── LeaveApiController.php
│   │           └── OvertimeApiController.php
│   ├── Requests/
│   │   ├── ClockInRequest.php
│   │   ├── ClockOutRequest.php
│   │   ├── StoreLeaveRequest.php
│   │   └── StoreOvertimeRequest.php
│   └── Resources/
│       └── V1/
│           ├── AttendanceResource.php
│           ├── ShiftResource.php
│           ├── LeaveRequestResource.php
│           └── LeaveBalanceResource.php
```

---

## 4. Rincian Implementasi Logika Bisnis

### US-11 & US-13: Shift Kerja & Kalkulasi Keterlambatan Otomatis
* **Atribut Shift:** `start_time` (08:00), `end_time` (17:00), `late_tolerance_minutes` (15 menit).
* **Rumus Evaluasi Kehadiran:**
  * Waktu Maksimal Masuk Tepat Waktu = `start_time + late_tolerance_minutes`.
  * Jika `clock_in_time <= 08:15` $\rightarrow$ Status: `ON_TIME`, `late_minutes = 0`.
  * Jika `clock_in_time > 08:15` $\rightarrow$ Status: `LATE`, `late_minutes = clock_in_time - start_time` (dihitung dari jam masuk resmi, bukan dari toleransi).
  * Jika `clock_out_time < end_time` $\rightarrow$ Status: `EARLY_LEAVE`, `early_leave_minutes = end_time - clock_out_time`.

### US-12: Presensi Cerdas (Formula Haversine & Foto Selfie)
* **Logika Backend Geofencing (`GeoLocationService`):**
  ```php
  public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
  {
      $earthRadius = 6371000; // Dalam meter
      $latFrom = deg2rad($lat1);
      $lonFrom = deg2rad($lon1);
      $latTo   = deg2rad($lat2);
      $lonTo   = deg2rad($lon2);

      $latDelta = $latTo - $latFrom;
      $lonDelta = $lonTo - $lonFrom;

      $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
          cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

      return $angle * $earthRadius; // Jarak dalam meter
  }
  ```
* **Alur Validasi Absensi:**
  1. Validasi apakah karyawan sudah absen masuk hari ini. Jika sudah, tolak dengan error 422 (*"Anda sudah melakukan Clock-In hari ini"*).
  2. Jika tipe kerja `WFO`: Ambil koordinat kantor cabang karyawan. Hitung jarak via Haversine. Jika `jarak > branch.radius_meters`, tolak absensi dengan pesan: *"Anda berada di luar radius kantor ({jarak} m dari batas {radius} m)"*.
  3. Validasi foto selfie (wajib dilampirkan, format image/jpeg/png). Kompres foto dan simpan ke `storage/app/private/attendances/{YYYYMM}/{employee_id}_in.jpg`.
  4. Simpan record absensi dengan status yang telah dikalkulasi.

### US-14 & US-15: Manajemen Cuti & Approval Bertingkat
* **Struktur Alur Persetujuan (State Machine):**
  ```
  Pengajuan Karyawan ──▶ Status: PENDING (Level 1: Line Manager)
                               │
               ┌───────────────┴───────────────┐
               ▼                               ▼
       Manager REJECT                  Manager APPROVE
  (Status: REJECTED, Selesai)   (Status: APPROVED_MANAGER)
                                               │
                                               ▼ (Level 2: HR Admin)
                               ┌───────────────┴───────────────┐
                               ▼                               ▼
                           HR REJECT                       HR APPROVE
                      (Status: REJECTED)              (Status: APPROVED_FINAL)
                                                               │
                                                               ▼
                                                     Potong Kuota Saldo Cuti
                                                     (leave_balances.used += n)
  ```
* **Aturan Kuota Cuti:**
  * Sebelum pengajuan disimpan, sistem memeriksa `leave_balances.remaining_days`. Jika jumlah hari permohonan > sisa cuti, tolak pengajuan.
  * Cuti Sakit yang menyertakan surat dokter tidak memotong kuota cuti tahunan (tipe cuti terpisah di `leave_types`).

### US-16: Pengajuan Surat Lembur (*Overtime*)
* **Aturan Lembur:**
  * Karyawan mengajukan lembur dengan input: tanggal, jam mulai, jam selesai, dan rincian pekerjaan yang dikerjakan.
  * Hanya jam lembur berstatus `APPROVED` oleh atasan yang akan ditarik ke dalam kalkulasi payroll di Sprint 4.

---

## 5. Spesifikasi REST API untuk Aplikasi Mobile

| Method | Endpoint | Deskripsi & Payload Utama |
| :--- | :--- | :--- |
| `GET` | `/api/v1/attendance/today` | Status absensi karyawan hari ini (sudah clock-in/out atau belum). |
| `POST` | `/api/v1/attendance/clock-in` | Body (multipart): `latitude`, `longitude`, `work_type` (`WFO`/`WFH`), `selfie_image`. |
| `POST` | `/api/v1/attendance/clock-out` | Body (multipart): `latitude`, `longitude`, `selfie_image`. |
| `GET` | `/api/v1/attendance/history` | Riwayat absensi bulanan (filter `month` & `year`). |
| `GET` | `/api/v1/leaves/balances` | Informasi kuota cuti tahunan, cuti terpakai, dan sisa cuti. |
| `POST` | `/api/v1/leaves` | Mengajukan cuti (body: `leave_type_id`, `start_date`, `end_date`, `reason`, `attachment`). |
| `GET` | `/api/v1/leaves/approvals` | Daftar cuti bawahan yang menunggu approval (khusus role Manager & HR). |
| `POST` | `/api/v1/leaves/{id}/approve` | Body: `status` (`APPROVED`/`REJECTED`), `notes`. |
| `POST` | `/api/v1/overtimes` | Mengajukan lembur (body: `date`, `start_time`, `end_time`, `reason`). |

---

## 6. Rencana Pengujian (Test Scenarios)

1. `test_wfo_clock_in_outside_branch_radius_is_rejected`: Memastikan absensi WFO di luar radius cabang ditolak dengan kode 422 dan pesan jarak.
2. `test_clock_in_after_tolerance_marked_as_late_with_correct_minutes`: Memastikan absen lewat dari batas toleransi otomatis berstatus `LATE` dengan kalkulasi menit keterlambatan yang akurat.
3. `test_leave_request_deducts_balance_only_after_final_approval`: Memastikan saldo cuti belum terpotong saat masih `PENDING` dan baru terpotong saat `APPROVED_FINAL`.
4. `test_employee_cannot_request_leave_exceeding_remaining_balance`: Memastikan pengajuan melebihi sisa cuti ditolak oleh sistem.
5. `test_api_clock_in_flow_with_image_upload`: Memastikan endpoint mobile API berhasil menerima payload multipart dan menyimpan record presensi.

---

## 7. Kriteria Selesai (Definition of Done - Sprint 3)

* [ ] Formula Haversine berhasil memblokir absensi WFO di luar radius kantor cabang yang ditentukan.
* [ ] Foto selfie absensi berhasil disimpan ke storage privat dan terhubung dengan record presensi.
* [ ] Jam keterlambatan terhitung otomatis dan akurat hingga hitungan menit.
* [ ] Alur approval cuti bertingkat (Line Manager $\rightarrow$ HR) berjalan mulus disertai pencatatan riwayat di tabel `leave_approvals`.
* [ ] Seluruh endpoint REST API presensi, cuti, dan lembur berfungsi dan siap diintegrasikan ke aplikasi mobile.
