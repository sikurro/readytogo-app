# Epic: Milestone 1 - Fondasi Proyek & Setup (Fase Persiapan)

## Deskripsi
Issue ini berfokus secara eksklusif pada **Milestone 1** aplikasi K3 Maritim "Ready To GO (R2G)". Tahap ini adalah fase fondasi. **Jangan melanjutkan ke fitur spesifik (seperti kuis atau pelaporan insiden) sebelum seluruh tugas dalam issue ini selesai dan direview.**

Target Pengguna Dokumen: Junior Programmer / AI Agent pengembang tahap awal.

---

## 📋 Task List & Instruksi Detail

### 1. Inisialisasi Proyek Laravel
- [ ] Install Laravel versi terbaru di dalam direktori `readytogo-app`.
- [ ] Konfigurasi file `.env`:
  - Sesuaikan koneksi database (`DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
  - Ubah nama aplikasi: `APP_NAME="Ready To GO"`.
- [ ] Pastikan *application key* ter-generate dengan benar (`php artisan key:generate`).

### 2. Instalasi Vue.js, Inertia.js, & Tailwind CSS
- [ ] Install Inertia.js server-side adapter melalui Composer: `composer require inertiajs/inertia-laravel`.
- [ ] Setup *middleware* Inertia: jalankan `php artisan inertia:middleware`. Daftarkan class middleware tersebut di bootstrap (`bootstrap/app.php` atau `app/Http/Kernel.php` tergantung versi Laravel).
- [ ] Install dependensi front-end: `npm install vue @inertiajs/vue3 @vitejs/plugin-vue`.
- [ ] Install dan inisialisasi Tailwind CSS: `npm install -D tailwindcss postcss autoprefixer` lalu `npx tailwindcss init -p`.
- [ ] Konfigurasikan `tailwind.config.js` di bagian `content` agar memindai file di `resources/**/*.blade.php`, `resources/**/*.js`, dan `resources/**/*.vue`.
- [ ] Tambahkan *directives* Tailwind di `resources/css/app.css`:
  ```css
  @tailwind base;
  @tailwind components;
  @tailwind utilities;
  ```
- [ ] Update `vite.config.js` untuk memasukkan plugin Vue dan mengarahkan input *build* ke `resources/css/app.css` & `resources/js/app.js`.

### 3. Setup Struktur Database Fundamental (Tahap 1)
- [ ] Buat migration untuk tabel `roles` (`id`, `name`, `created_at`, `updated_at`).
- [ ] Update migration bawaan tabel `users` untuk menambahkan field:
  - `role_id` (foreign key referensi ke `roles.id`)
  - `avatar` (string, nullable)
  - `position` (string, nullable)
  - `status_fit` (boolean, default: true)
- [ ] Buat model `Role` dan update model `User`.
- [ ] Definisikan relasi Eloquent:
  - Di model `User`: relasi `belongsTo(Role::class)`
  - Di model `Role`: relasi `hasMany(User::class)`
- [ ] Buat Database Seeder dasar:
  - `RoleSeeder`: Insert role "Admin" dan "Petugas".
  - `UserSeeder`: Buat setidaknya 1 akun dummy untuk Admin (role admin) dan 1 akun dummy untuk Petugas (role petugas).

### 4. Setup Authentication Dasar
- [ ] Buat sistem autentikasi (Login dan Logout). Disarankan menggunakan *starter kit* **Laravel Breeze** (pilih stack Vue with Inertia) untuk mempercepat proses. Jika menggunakan Breeze, pastikan file registrasi (jika tidak diperlukan) dinonaktifkan atau hapus routes register agar pembuatan akun hanya bisa dari sistem seeder/admin.
- [ ] Jika tidak menggunakan Breeze, buat `AuthController` dengan method `login` dan `logout` secara manual menggunakan *session authentication*.
- [ ] Lindungi route utama aplikasi dengan middleware `auth`.

### 5. Pembuatan Struktur Layouting Frontend (Vue Components)
- [ ] Buat file *root template* `resources/views/app.blade.php` dengan directive `@inertia` dan Vite directive.
- [ ] Buat file `resources/js/app.js` sebagai *entry point* Inertia, yang me-render halaman Vue.
- [ ] Buat dua *Layout Components* utama di dalam direktori `resources/js/Layouts/`:
  - `MobileAppLayout.vue`: Layout ini khusus untuk "Petugas". Harus memiliki *Bottom Navigation Bar* karena aplikasi ini berkonsep *Mobile-First*.
  - `AdminDashboardLayout.vue`: Layout ini khusus untuk "Admin". Harus memiliki *Sidebar Navigation* (atau navbar desktop) karena admin akan mengakses dari layar lebih besar.
- [ ] Buat *dummy pages* / *placeholder*:
  - Buat `Pages/Petugas/Dashboard.vue` yang di-wrap dengan `MobileAppLayout`.
  - Buat `Pages/Admin/Dashboard.vue` yang di-wrap dengan `AdminDashboardLayout`.
- [ ] Buat *Route redirect/logic* di Laravel: setelah user login, arahkan Admin ke dashboard Admin, dan Petugas ke dashboard Petugas.

---

## ✅ Aturan Verifikasi Milestone 1
Sebelum tahap ini dianggap Selesai (Done) dan pindah ke modul lain, pastikan hal-hal berikut berjalan dengan baik:
1. Proses kompilasi frontend (`npm run dev`) dan server backend (`php artisan serve`) berjalan tanpa error.
2. Otentikasi berfungsi: User dapat login menggunakan data dari seeder.
3. *Role-based redirect* berfungsi: Login sebagai Admin mengarah ke halaman ber-layout Sidebar. Login sebagai Petugas mengarah ke halaman ber-layout Bottom Navigation.
4. Data dummy di tabel `users` dan `roles` telah ter-seed dengan benar.

> **STOP:** Jangan mulai mengerjakan Milestone 2 (Kuis, Fatigue Check, dll) sebelum Milestone 1 direview dan disetujui.
