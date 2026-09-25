# Sprint 1 Plan: Fondasi Sistem, Database, Hak Akses & REST API Baseline

Dokumen ini memuat rencana implementasi teknis untuk **Sprint 1** NexusHRIS. Sprint ini berfokus pada pembangunan fondasi dasar sistem: migrasi dan seeder database, sistem otentikasi (Web + REST API Mobile), pembatasan peran (*Role-Based Access Control / RBAC*), dan pencatatan riwayat audit (*Audit Trail*).

---

## 1. Ringkasan & Ruang Lingkup Sprint

* **Durasi Target:** 2 Minggu (14 Hari)
* **Kategori Epics:** Fondasi Aplikasi, Autentikasi Pengguna, Manajemen Hak Akses, Audit Logging, & Mobile API Baseline.
* **Daftar User Stories:**

| Story ID | Judul User Story | Prioritas | Estimasi Poin |
| :--- | :--- | :---: | :---: |
| **US-01** | Instalasi Dependensi, Konfigurasi Lingkungan (`.env`), & Integrasi UI (Tailwind/Livewire) | P0 | 3 |
| **US-02** | Eksekusi Migrasi Database Relasional & Seeder Data Awal Lengkap | P0 | 5 |
| **US-03** | Autentikasi Login & Logout Pengguna (Web Session & REST API Token) | P0 | 3 |
| **US-04** | Implementasi RBAC 4 Tingkatan Peran (`super_admin`, `hr_admin`, `manager`, `employee`) | P0 | 5 |
| **US-05** | Sistem Pencatatan Log Aktivitas (*Audit Trail*) ke Tabel `activity_logs` | P1 | 3 |

---

## 2. Rekomendasi Paket & Pustaka (Packages Recommendation)

Untuk memastikan fondasi kokoh dan sesuai dengan standar Laravel modern:

1. **`laravel/sanctum` (Wajib untuk Mobile API):**
   * *Peran:* Menyediakan autentikasi berbasis *Personal Access Token* (Bearer Token) untuk aplikasi mobile (Flutter/React Native) dan autentikasi berbasis cookie untuk web SPA.
   * *Perintah:* `php artisan install:api`
2. **`spatie/laravel-permission` (Wajib untuk RBAC):**
   * *Peran:* Mengelola *Roles* & *Permissions* dengan caching otomatis dan integrasi Blade directive (`@role`, `@can`) serta middleware API (`role:super_admin`, `permission:manage_employees`).
   * *Perintah:* `composer require spatie/laravel-permission`
3. **`spatie/laravel-activitylog` (Direkomendasikan untuk Audit Trail):**
   * *Peran:* Secara otomatis mendeteksi model event (`created`, `updated`, `deleted`) dan mencatat data sebelum/sesudah ke dalam tabel log tanpa harus menulis manual di setiap controller.
4. **`laravel/pint` (Sudah Terpasang):**
   * *Peran:* Linter dan formatter kode otomatis sebelum melakukan commit: `vendor/bin/pint --format agent`.

---

## 3. Rencana Arsitektur & Struktur Direktori

### A. Struktur Folder Baru yang Akan Disiapkan
```
app/
├── Actions/
│   └── Auth/
│       ├── AuthenticateUserAction.php
│       └── CreateApiTokenAction.php
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   ├── LoginController.php
│   │   │   └── LogoutController.php
│   │   └── Api/
│   │       ├── BaseApiController.php
│   │       └── V1/
│   │           └── AuthController.php
│   ├── Requests/
│   │   └── Auth/
│   │       └── LoginRequest.php
│   ├── Resources/
│   │   └── V1/
│   │       └── UserResource.php
│   └── Traits/
│       └── ApiResponseTrait.php
```

### B. Standardisasi Response REST API (Unified Envelope)
Buat trait [ApiResponseTrait](file:///c:/laragon/www/NexusHRIS/app/Http/Traits/ApiResponseTrait.php):
```php
namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponseTrait
{
    public function sendSuccess(mixed $data = null, string $message = 'Success', int $code = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    public function sendError(string $message = 'Error', mixed $errors = null, int $code = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }
}
```

---

## 4. Rincian Implementasi Per User Story

### US-01: Setup Environment & Dependensi
* **Langkah Kerja:**
  1. Pastikan file `.env` terkonfigurasi dengan database MySQL (`db_nexus_hris`).
  2. Setup frontend: Tailwind CSS dan aset bundling (`npm run build`).
  3. Konfigurasi `AppServiceProvider` untuk mengaktifkan strict mode:
     ```php
     Model::shouldBeStrict(! $this->app->isProduction());
     ```

### US-02: Migrasi Database & Seeder Lengkap
* **Langkah Kerja:**
  1. Jalankan `php artisan migrate:fresh`.
  2. Buat Seeder Khusus:
     * `RoleAndPermissionSeeder`: Mendaftarkan peran (`super_admin`, `hr_admin`, `manager`, `employee`) dan izin dasarnya.
     * `UserSeeder`: Membuat user default untuk tiap peran dengan kredensial pengujian yang terdokumentasi (contoh: `admin@nexus.test`, `password`).
     * `DatabaseSeeder`: Memanggil seluruh seeder secara berurutan.

### US-03: Autentikasi Pengguna (Web & Mobile API)
* **Alur Logika Bisnis:**
  * Validasi input format email dan password.
  * Cek kredensial via `Auth::attempt()`.
  * **Pemeriksaan Akun Aktif:** Jika `users.is_active == false`, batalkan proses login dan kembalikan pesan *"Akun Anda telah dinonaktifkan, hubungi administrator"*.
  * Jika lolos, catat aktivitas login ke `activity_logs`.
* **Implementasi Mobile REST API (`/api/v1/auth`):**
  * `POST /api/v1/auth/login`: Menerima email, password, `device_name`. Mengembalikan `token` (Sanctum plain text token) dan `UserResource`.
  * `POST /api/v1/auth/logout`: Revoke token aktif saat ini (`$request->user()->currentAccessToken()->delete()`).
  * `GET /api/v1/auth/me`: Mengembalikan data user login saat ini beserta role dan izin yang dimiliki.

### US-04: Sistem Hak Akses (RBAC 4 Tingkatan)
* **Matriks Hak Akses Default:**
  * `super_admin`: Akses penuh konfigurasi sistem, kelola role, cabang, audit log.
  * `hr_admin`: Kelola data karyawan, shift, persetujuan cuti akhir, pemrosesan payroll.
  * `manager`: Persetujuan cuti tingkat 1 (*Line Manager*), persetujuan lembur timnya, monitoring absensi anggota departemen.
  * `employee`: Portal mandiri (*ESS*): Clock-in/out, ajukan cuti/lembur/klaim, lihat slip gaji pribadi.
* **Proteksi Route:**
  * Di Web: Menggunakan middleware Spatie `middleware(['role:super_admin|hr_admin'])`.
  * Di API: `middleware(['auth:sanctum', 'role:employee'])`.

### US-05: Audit Trail & Activity Logging
* **Langkah Kerja:**
  * Membuat helper / listener untuk mencatat aksi penting:
    * `user_login`, `user_logout`, `update_profile`, `create_employee`, `delete_data`.
  * Format penyimpanan pada `activity_logs`:
    * `user_id`, `action`, `module`, `description`, `ip_address`, `user_agent`.

---

## 5. Rencana Pengujian (Test Scenarios)

Implementasikan pengujian menggunakan Pest atau PHPUnit (`tests/Feature/AuthTest.php`):
1. `test_user_can_login_with_valid_credentials_via_web`: Memastikan user valid berhasil login dan diredirect ke dashboard sesuai perannya.
2. `test_inactive_user_cannot_login`: Memastikan user dengan `is_active = 0` ditolak dengan response error 403 / peringatan.
3. `test_api_login_returns_valid_sanctum_token`: Memastikan endpoint `POST /api/v1/auth/login` mengembalikan struktur response standar dengan Bearer token.
4. `test_unauthenticated_request_to_protected_api_is_rejected`: Memastikan akses ke `/api/v1/auth/me` tanpa token mengembalikan 401 Unauthorized.
5. `test_activity_log_is_recorded_after_login`: Memastikan data login tercatat di tabel `activity_logs`.

---

## 6. Kriteria Selesai (Definition of Done - Sprint 1)

* [ ] Database migration berjalan 100% tanpa error pada database bersih.
* [ ] Seeder berhasil mengisi 4 akun pengujian untuk masing-masing role (`super_admin`, `hr_admin`, `manager`, `employee`).
* [ ] Rute login Web berhasil mengarahkan pengguna ke dashboard sesuai perannya.
* [ ] Endpoint REST API `POST /api/v1/auth/login` berhasil mengembalikan token Sanctum dan response seragam.
* [ ] Pengguna nonaktif (`is_active = false`) gagal login baik di Web maupun API.
* [ ] Seluruh file kode melewati linter Laravel Pint tanpa komplain.
