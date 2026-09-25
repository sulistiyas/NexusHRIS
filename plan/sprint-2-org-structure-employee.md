# Sprint 2 Plan: Struktur Organisasi & Master Data Karyawan

Dokumen ini memuat rencana implementasi teknis untuk **Sprint 2** NexusHRIS. Fokus utama sprint ini adalah membangun fondasi struktur organisasi (kantor cabang dengan titik koordinat GPS & radius absensi, departemen, dan jabatan), manajemen siklus onboarding karyawan baru (termasuk auto-generate akun user dan nomor induk), pengarsipan berkas dokumen digital, serta portal profil mandiri (*Employee Self-Service / ESS*).

---

## 1. Ringkasan & Ruang Lingkup Sprint

* **Durasi Target:** 2 Minggu (14 Hari)
* **Kategori Epics:** Master Data Perusahaan, Geofencing Kantor Cabang, Siklus Hidup Karyawan, Pengarsipan Dokumen, dan Portal ESS Profil.
* **Daftar User Stories:**

| Story ID | Judul User Story | Prioritas | Estimasi Poin |
| :--- | :--- | :---: | :---: |
| **US-06** | Manajemen Kantor Cabang (`branches`) lengkap dengan Latitude, Longitude, & Radius Absensi (meter) | P0 | 3 |
| **US-07** | Manajemen Departemen (`departments`) & Jabatan (`designations`) berbasis hierarki | P0 | 3 |
| **US-08** | Input Karyawan Baru (`employees`), Auto-generate Akun (`users`), & Relasi Atasan (`manager_id`) | P0 | 8 |
| **US-09** | Manajemen Berkas Digital Karyawan (`employee_documents`): KTP, NPWP, Ijazah, Kontrak PDF | P1 | 5 |
| **US-10** | Portal Profil Mandiri (ESS): Melihat & Memperbarui Profil Pribadi serta Kontak Darurat | P1 | 3 |

---

## 2. Rekomendasi Paket & Pustaka (Packages Recommendation)

1. **`intervention/image` (Manipulasi & Kompresi Gambar):**
   * *Peran:* Menangani kompresi dan *resizing* otomatis foto profil (avatar) karyawan sebelum disimpan ke storage privat agar menghemat kuota disk dan mempercepat *load time* aplikasi mobile/web.
   * *Perintah:* `composer require intervention/image`
2. **`Leaflet.js` & `OpenStreetMap` (Komponen Peta Frontend Tanpa Biaya API):**
   * *Peran:* Digunakan pada form tambah/edit cabang kantor untuk memilih titik koordinat secara visual melalui interaksi klik di peta serta menampilkan lingkaran radius geofence absensi.
3. **`laravel/serializable-closure` / Mail Notification Queue:**
   * *Peran:* Mengirimkan email aktivasi dan kredensial sementara kepada karyawan baru secara *asynchronous* melalui antrean background (*Queue*).

---

## 3. Rencana Arsitektur & Struktur Direktori

### A. Struktur Folder Baru yang Akan Disiapkan
```
app/
├── Actions/
│   └── Employee/
│       ├── CreateEmployeeAction.php
│       ├── GenerateEmployeeCodeAction.php
│       └── UpdateProfilePictureAction.php
├── Http/
│   ├── Controllers/
│   │   ├── BranchController.php
│   │   ├── DepartmentController.php
│   │   ├── DesignationController.php
│   │   ├── EmployeeController.php
│   │   ├── EmployeeDocumentController.php
│   │   └── Api/
│   │       └── V1/
│   │           ├── ProfileController.php
│   │           └── BranchApiController.php
│   ├── Requests/
│   │   ├── StoreBranchRequest.php
│   │   ├── StoreEmployeeRequest.php
│   │   ├── UpdateEmployeeRequest.php
│   │   └── UploadDocumentRequest.php
│   └── Resources/
│       └── V1/
│           ├── BranchResource.php
│           ├── DepartmentResource.php
│           ├── EmployeeResource.php
│           └── EmergencyContactResource.php
```

---

## 4. Rincian Implementasi Per User Story

### US-06: Manajemen Kantor Cabang & Geofencing GPS
* **Field Utama:** `name`, `address`, `latitude`, `longitude`, `radius_meters`, `is_active`.
* **Fitur UI:**
  * Integrasi peta interaktif (*Leaflet.js*): Saat admin menggeser pin peta atau mengetik koordinat, lingkaran radius absensi (misal 50 meter) otomatis tergambar di atas peta.
* **REST API Endpoint:**
  * `GET /api/v1/branches`: Daftar cabang aktif beserta koordinat & radius (dibutuhkan aplikasi mobile untuk sinkronisasi lokasi kantor terdekat).

### US-07: Departemen & Jabatan (*Designations*)
* **Relasi Hierarki:**
  * `Department` berelasi dengan `Branch` dan bisa memiliki parent department.
  * `Designation` memiliki relasi `department_id` dan tingkatan level jabatan (misal: 1=Staff, 2=Supervisor, 3=Manager, 4=Director).

### US-08: Onboarding Karyawan Baru & Auto-Generation Akun User
* **Alur Logika Transaksional (`CreateEmployeeAction`):**
  1. Mulai `DB::beginTransaction()`.
  2. **Generate NIP Otomatis:** Format standar: `NX-{YYYYMM}-{4_DIGIT_INCREMENT}` (contoh: `NX-202610-0001`).
  3. **Validasi Keunikan:** `employee_code` dan `nik` (KTP) wajib unik di database.
  4. **Otomatis Buat Akun Pengguna:**
     * Generate password acak 8 karakter atau temporary pin.
     * Buat data di tabel `users` (`name`, `email`, `password_hash`, `is_active = true`).
     * Berikan role Spatie sesuai jabatan (misal: role `employee` atau `manager`).
  5. **Simpan Data Karyawan:** Buat record di `employees` dengan foreign key `user_id`, `branch_id`, `department_id`, `designation_id`, `manager_id`.
  6. **Kirim Email Notifikasi:** Dispatch job `SendEmployeeWelcomeEmailJob` berisi instruksi login awal.
  7. Lakukan `DB::commit()`.

### US-09: Manajemen Berkas Digital Karyawan (`employee_documents`)
* **Jenis Berkas:** KTP, NPWP, Ijazah Terakhir, Kontrak Kerja, Pakta Integritas (Maksimal 5MB, format PDF / JPEG / PNG).
* **Aturan Keamanan Storage:**
  * File disimpan di disk privat: `storage/app/private/documents/{employee_id}/`.
  * **Larangan:** Jangan gunakan `asset('storage/...')` publik. Akses unduh berkas wajib melalui route terautentikasi dengan verifikasi Policy:
    ```php
    Gate::authorize('view', $employeeDocument);
    ```

### US-10: Portal Mandiri Profil Karyawan (ESS)
* **Kebutuhan Web & REST API:**
  * Karyawan dapat melihat ringkasan profil, data kepegawaian (NIP, tanggal bergabung, cabang, jabatan), dan atasan langsung.
  * Karyawan dapat memperbarui informasi pribadi (alamat domisili, nomor telepon, kontak darurat).
  * **REST API Endpoints Mobile:**
    * `GET  /api/v1/profile`: Mendapatkan data profil lengkap via `EmployeeResource`.
    * `PUT  /api/v1/profile`: Memperbarui data kontak dan alamat.
    * `POST /api/v1/profile/avatar`: Mengunggah foto profil baru (otomatis di-resize ke 400x400 px dan dikompresi).
    * `GET  /api/v1/profile/emergency-contacts`: Melihat daftar kontak darurat.
    * `POST /api/v1/profile/emergency-contacts`: Menambah/mengedit kontak darurat.

---

## 5. Rencana Pengujian (Test Scenarios)

1. `test_admin_can_create_branch_with_valid_coordinates`: Memastikan cabang kantor dengan koordinat lat/long dan radius berhasil tersimpan.
2. `test_employee_creation_automatically_generates_user_and_role`: Memastikan input karyawan berhasil membuat user baru dan menghubungkan role `employee`.
3. `test_duplicate_nik_or_nip_is_rejected`: Memastikan validasi database dan form request menolak NIK / NIP duplikat dengan error 422.
4. `test_employee_can_only_access_their_own_documents`: Memastikan Karyawan A tidak bisa mengunduh berkas dokumen milik Karyawan B (403 Forbidden).
5. `test_api_profile_returns_correct_employee_resource`: Memastikan endpoint `GET /api/v1/profile` dengan Bearer token mengembalikan data karyawan terformat lengkap.

---

## 6. Kriteria Selesai (Definition of Done - Sprint 2)

* [ ] CRUD Cabang Kantor berfungsi dengan pemetaan koordinat GPS dan visual radius.
* [ ] Master Departemen dan Jabatan terintegrasi dengan struktur relasi cabang.
* [ ] Form Tambah Karyawan berhasil membuat NIP otomatis dan meng-generate akun user aktif.
* [ ] Berkas dokumen karyawan tersimpan aman di storage privat dan terproteksi oleh Policy.
* [ ] Karyawan dapat melihat dan mengupdate profil serta kontak darurat via Web ESS dan REST API.
* [ ] Seluruh endpoint REST API Sprint 2 telah diuji dan mengembalikan response seragam.
