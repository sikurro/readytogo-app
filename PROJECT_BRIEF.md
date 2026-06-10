# Project Brief: Ready To GO (R2G)

## 1. Informasi Proyek
- **Nama Proyek:** Ready To GO (R2G)
- **Tema:** Aplikasi Manajemen K3 (Keselamatan dan Kesehatan Kerja) Kepanduan Maritim Pelabuhan
- **Platform:** Mobile-First Web Application (PWA Ready)
- **Tujuan Utama:** Memudahkan petugas dalam melakukan pengecekan fisik dan pengetahuan K3 sebelum turun ke lapangan, serta menyediakan sarana pelaporan bahaya/insiden secara cepat dan akurat. Aplikasi dirancang agar sangat responsif, memiliki antarmuka yang mudah digunakan di luar ruangan (interaktif), dan ringan.

## 2. Technology Stack
- **Backend:** Laravel
- **Frontend:** Vue.js dengan Inertia.js
- **Styling:** Tailwind CSS
- **Library Tambahan yang Disarankan:**
  - `Pusher` atau `Laravel WebSockets` / `Laravel Reverb`: Untuk fitur notifikasi realtime saat ada pelaporan insiden baru.
  - `browser-image-compression` (npm): Untuk memenuhi kebutuhan kompresi foto di sisi client/browser sebelum dikirim ke server.
  - `Maatwebsite/Laravel-Excel`: Untuk fitur export data leaderboard, fatigue check, dan laporan insiden ke dalam format Excel.
  - `Leaflet.js` atau `Google Maps API`: Untuk memvisualisasikan laporan insiden ke dalam bentuk peta (Admin Dashboard).

## 3. Fitur Utama & Logika Bisnis

#### A. Otentikasi & Role (RBAC)
- **Role Admin:** Memiliki hak akses penuh. Dapat mengelola data pengguna/user (Registrasi petugas bersifat tertutup dan dikelola langsung oleh Admin), mengatur kuis, memantau laporan insiden, mengubah status, melihat peta insiden, dan melakukan export data.
- **Role Petugas:** Mengakses fitur-fitur operasional (Kuis harian, Fatigue Check, dan Lapor Insiden). Tidak ada fitur registrasi mandiri/terbuka untuk publik demi keamanan internal.

### B. Modul Kuis (Game Edukasi K3 / Daily Quiz)
- Gaya bermain interaktif seperti Kahoot.
- **Tema Kuis:** Spesifik (misal: APAR, Evakuasi, Alat Keselamatan Diri) dengan durasi 5-10 menit.
- **Aturan:** Maksimal dimainkan 1 kali sehari per user.
- **UI/UX:** Full-screen, minim distraksi, dilengkapi timer yang jelas (default: 15 detik per soal). Tersedia Mode Latihan sebelum bermain di kuiz resmi.
- **Leaderboard:** 
  - Akumulasi skor berdasarkan jawaban benar dan kecepatan menjawab.
  - Data di-reset (filter view) otomatis setiap tanggal 1 setiap bulannya (data lama tetap tersimpan di database).
  - Admin dapat mengunduh Leaderboard dalam format Excel.

### C. Modul Tes Kesiapan Kerja (Fatigue Check) / Fit to Work
- Petugas wajib melakukan tes ini sebelum mulai bertugas.
- **Mekanisme Tes:** Layar berwarna merah akan berubah menjadi hijau dalam waktu acak. User harus secepat mungkin menyentuh/mengklik layar.
- **Aturan Validasi:** 
  - Respons < 400ms = **"Fit to Work"** (Bugar).
  - Respons > 400ms = **"Fatigue"** (Tidak Bugar).
- Admin dapat mengunduh laporan hasil Fatigue Check per hari atau periode tertentu.

### D. Modul Pelaporan Insiden (Incident Reporting)
- Petugas dapat membuat laporan bahaya dengan cepat.
- **Formulir Laporan:** Jenis Insiden/Kategori, Lokasi (berupa teks & titik koordinat GPS manual/otomatis), Deskripsi Singkat, dan Bukti Foto.
- **Teknis Penting:** Bukti foto WAJIB dikompresi di sisi client (< 1MB) sebelum di-upload ke server untuk menghemat bandwidth.
- **Realtime:** Notifikasi segera muncul di perangkat Admin/PIC terkait begitu laporan dikirim.

### E. Manajemen Laporan Admin (Admin Reporting Dashboard)
- Admin memiliki kemampuan untuk melihat sebaran laporan insiden dalam bentuk Peta (Map) maupun Daftar (List).
- **Manajemen Status:** Admin dapat mengubah status laporan menjadi *Pending*, *In Progress*, atau *Solved* serta memberikan tanggapan/komentar.
- Fitur Export seluruh/sebagian data laporan insiden ke format Excel.

### F. Halaman Utama (Home Page)
- **Dashboard Personal:** Menampilkan profil pengguna (Foto, Nama, Jabatan, Tanggal) dan Status Terkini (Fit/Fatigue).
- **Widget Dinamis:** Akan menyesuaikan dengan fitur yang ada (contoh: Shortcut Lapor Insiden Cepat, Status Kuis Hari Ini).

---

## 4. Arahan UI/UX (Mobile-First)
- **Desain Clean & Kontras Tinggi:** Prioritaskan keterbacaan di area outdoor yang terkena sinar matahari (High Contrast, font yang tebal).
- **Elemen Interaktif:** Tombol harus berukuran besar (touch-friendly) untuk menghindari salah tekan (fat-finger problem). 
- **Feedback Visual:** Pastikan setiap interaksi memiliki feedback (misalnya efek *ripple* atau *scale-down* ketika tombol ditekan).

---

## 5. Struktur Database (Usulan Tahap Awal)
 
1. `users` (id, name, email, password, role_id, avatar, position, status_fit, created_at)
2. `roles` (id, name)
3. `quizzes` (id, title, theme, duration_minutes, is_active, created_at)
4. `questions` (id, quiz_id, question_text, timer_seconds)
5. `answers` (id, question_id, answer_text, is_correct)
6. `quiz_attempts` (id, user_id, quiz_id, score, time_ms, month_year, created_at)
7. `fatigue_checks` (id, user_id, reaction_time_ms, result_status, created_at)
8. `incident_categories` (id, name)
9. `incident_reports` (id, user_id, category_id, location_text, latitude, longitude, description, photo_path, status, created_at)
10. `incident_comments` (id, report_id, user_id, comment, created_at)

---

## 6. Routing (Usulan)

**A. Front-End (Petugas)**
- `GET /` -> Menampilkan `Home/Index`
- `GET /fatigue-check` -> Menampilkan area tes
- `POST /fatigue-check` -> Menyimpan hasil tes
- `GET /quiz` -> Menampilkan list/lobby kuis harian
- `GET /quiz/play` -> Halaman bermain full screen
- `GET /quiz/leaderboard` -> Klasemen kuis
- `GET /report/create` -> Menampilkan formulir insiden
- `POST /report/store` -> Menyimpan insiden dengan foto terkompresi

**B. Back-End (Admin)**
- `GET /admin/dashboard` -> Statistik umum
- `GET /admin/quizzes` -> CRUD kuis dan soal
- `GET /admin/leaderboard/export` -> Endpoint export excel leaderboard
- `GET /admin/fatigue-reports` -> Laporan fatigue (list & export)
- `GET /admin/incidents` -> Menampilkan laporan insiden (List/Peta)
- `PUT /admin/incidents/{id}/status` -> Endpoint update status laporan (Pending/Proses/Selesai)
- `GET /admin/users` -> Halaman manajemen user (List & Search)
- `GET /admin/users/create` -> Form tambah user baru
- `POST /admin/users` -> Simpan user baru (Registrasi Tertutup)
- `GET /admin/users/{id}/edit` -> Form edit user
- `PUT /admin/users/{id}` -> Update data user
- `DELETE /admin/users/{id}` -> Hapus / nonaktifkan user

---

## 7. Komponen Vue.js (Arsitektur Inertia)

Pemisinan logic akan dilakukan secara modular di dalam `resources/js/Pages` dan `resources/js/Components`.

**Layouts:**
- `MobileAppLayout.vue` -> Layout dengan Bottom Navigation untuk Petugas.
- `AdminDashboardLayout.vue` -> Layout dengan Sidebar Menu untuk Admin.

**Reusable Components (`/Components`):**
- `TouchButton.vue` -> Tombol interaktif untuk mobile.
- `QuizTimerBar.vue` -> Progress bar untuk durasi soal kuis (animasi mengecil).
- `ImageCompressorUploader.vue` -> Komponen khusus yang membungkus input file dengan logic kompresi `browser-image-compression`.
- `FatigueTestArea.vue` -> Area layar yang menangani logic merah-ke-hijau secara acak.
- `InteractiveMap.vue` -> Komponen LeafletJS untuk input lokasi dan Admin View.

**Views (`/Pages`):**
- `Home.vue`
- `Fatigue/Test.vue`
- `Quiz/Play.vue`
- `Incident/Form.vue`
- `Admin/Incident/Manager.vue`
- `Admin/User/Index.vue`
- `Admin/User/Create.vue`
- `Admin/User/Edit.vue`
