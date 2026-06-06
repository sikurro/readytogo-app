# Ready To GO (R2G)

## Tentang Proyek
**Ready To GO (R2G)** adalah Aplikasi Manajemen K3 (Keselamatan dan Kesehatan Kerja) Kepanduan Maritim Pelabuhan.
Aplikasi berbasis *Mobile-First Web Application (PWA Ready)* ini bertujuan untuk memudahkan petugas dalam melakukan pengecekan fisik (Fatigue Check) dan pengetahuan K3 (Kuis Harian) sebelum turun ke lapangan, serta menyediakan sarana pelaporan bahaya/insiden secara cepat dan akurat. Aplikasi dirancang agar sangat responsif, memiliki antarmuka yang mudah digunakan di luar ruangan, dan ringan.

## Fitur Utama
- **Otentikasi & Role (RBAC):** Memisahkan akses antara Admin (manajemen, monitoring) dan Petugas (operasional).
- **Modul Kuis (Game Edukasi K3):** Kuis harian interaktif untuk melatih pengetahuan K3 sebelum bertugas. Dilengkapi dengan leaderboard.
- **Modul Tes Kesiapan Kerja (Fatigue Check):** Tes reaksi warna interaktif untuk mengukur tingkat kebugaran (Fit to Work) petugas.
- **Modul Pelaporan Insiden (Incident Reporting):** Pelaporan bahaya cepat disertai foto (dikompresi di sisi klien) dan titik lokasi GPS.
- **Manajemen Laporan (Admin Dashboard):** Admin dapat memantau sebaran insiden di peta (Map View), mengubah status penyelesaian, dan mengekspor data laporan ke format Excel.

## Technology Stack
- **Backend:** [Laravel](https://laravel.com)
- **Frontend:** [Vue.js](https://vuejs.org) dengan [Inertia.js](https://inertiajs.com)
- **Styling:** [Tailwind CSS](https://tailwindcss.com)

### Requirements Server & Pengembangan
- PHP >= 8.1
- Composer
- Node.js & NPM
- MySQL / MariaDB

## Instalasi dan Setup

1. **Clone repository ini**
   ```bash
   git clone https://github.com/sikurro/readytogo-app.git
   cd readytogo-app
   ```

2. **Install dependency PHP (Composer)**
   ```bash
   composer install
   ```

3. **Install dependency JavaScript (NPM)**
   ```bash
   npm install --legacy-peer-deps
   ```

4. **Konfigurasi Environment**
   Salin `.env.example` ke `.env` lalu sesuaikan konfigurasi database.
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Migrasi dan Seeding Database**
   ```bash
   php artisan migrate --seed
   ```

6. **Jalankan Aplikasi**
   Buka 2 terminal terpisah:
   - Terminal 1 (Backend): `php artisan serve`
   - Terminal 2 (Frontend): `npm run dev`

   Aplikasi dapat diakses melalui `http://localhost:8000`.

## Role & Akses Default (Development)
Saat proses *seeding*, beberapa akun default akan dibuat. Anda dapat menggunakan akun tersebut untuk masuk:
- **Admin:** `admin@example.com` / `password`
- **Petugas:** `petugas@example.com` / `password`

## License
Aplikasi ini dikembangkan untuk keperluan spesifik dan bersifat tertutup. Silakan merujuk pada ketentuan yang berlaku di organisasi terkait.
