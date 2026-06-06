# Roadmap Proyek: Ready To GO (R2G)

Dokumen ini berisi *timeline* dan tahapan pengembangan (roadmap) tingkat tinggi untuk memastikan penyelesaian aplikasi R2G tepat waktu, sesuai dengan *tech stack* (Laravel, Vue, Inertia, Tailwind).

---

## 🚩 Milestone 1: Fondasi Proyek & Setup (Fase Persiapan)
**Fokus:** Menyiapkan lingkungan pengembangan, database, dan arsitektur awal.

- **Inisialisasi Proyek:** Setup Laravel, Vue.js, Inertia.js, dan Tailwind CSS.
- **Skema Database (Tahap 1):** Migrations untuk fitur fundamental (`users`, `roles`).
- **Autentikasi & Otorisasi:** Sistem Login dan pengaturan hak akses (RBAC) bagi Admin dan Petugas.
- **Layouting:** Pembuatan struktur komponen Vue (Layout Mobile & Layout Admin).

---

## 🚩 Milestone 2: Fitur Operasional Utama (Fase Pembangunan K3)
**Fokus:** Mengembangkan modul-modul yang berinteraksi langsung dengan Petugas di lapangan.

- **Fatigue Check (Fit to Work):**
  - Pembuatan UI tes reaksi visual (merah/hijau).
  - Logic sistem validasi *delay* respons (< 400ms = Fit).
- **Modul Pelaporan Insiden:**
  - Form UI dengan GPS / input manual.
  - Kompresi foto pada sisi client (< 1MB) untuk meminimalkan beban *upload*.
  - Penyimpanan data laporan ke server (lokasi, foto, jenis).
- **Modul Kuis / Edukasi:**
  - Antarmuka permainan *full-screen* dengan *timer* dinamis per soal.
  - soal yang ditampilkan bisa berupa teks ataupun gambar (nanti akan ada fitur input gambar)
  - Logic penghitungan skor dan penyimpanan ke riwayat (attempts).

---

## 🚩 Milestone 3: Manajemen Admin & Back-Office
**Fokus:** Memberikan panel kendali (dashboard) bagi Admin untuk mengelola data operasional.

- **Dashboard Admin:** 
  - Manajemen kuis (CRUD soal dan kuiz).
  - Pantauan laporan insiden (List View & Map View).
- **Manajemen Insiden:** 
  - Admin dapat merespons, memberi komentar, dan memperbarui status insiden (*Pending, In Progress, Solved*).
- **Leaderboard & Rangkuman Data:**
  - Halaman klasemen kuis yang otomatis menyeleksi data per bulan.
- **Fitur Export (Excel):**
  - Implementasi *export* data ke Excel untuk: Leaderboard, Hasil Fatigue Check, dan Histori Insiden.

---

## 🚩 Milestone 4: Integrasi & Penyempurnaan UI/UX
**Fokus:** Merapikan aplikasi menjadi satu kesatuan dan mengoptimalkan performa.

- **Halaman Utama (Home):**
  - Penyesuaian profil, widget status terkini, dan akses cepat ke fitur (Kuis/Lapor/Fatigue).
- **Notifikasi Realtime (Opsional Tahap 1):**
  - Integrasi Websocket/Pusher untuk notifikasi pelaporan insiden baru kepada Admin.
- **Optimasi Mobile-First:**
  - *Styling* tingkat lanjut (Tailwind), pengujian rasio kontras, *touch area* ukuran besar, dan animasi responsif (transisi Inertia).

---

## 🚩 Milestone 5: Testing & Deployment (Go-Live)
**Fokus:** Pengujian lapangan dan rilis ke production.

- **UAT (User Acceptance Testing):** Pengujian aplikasi oleh petugas di lapangan.
- **Bug Fixing:** Mengatasi isu *edge-case* (seperti gagal upload jaringan lambat, respons layar kurang peka).
- **Production Setup:** Konfigurasi server production, domain, SSL, dan optimalisasi *build* frontend.
- **Rilis V1.0:** Peluncuran resmi *Ready To GO (R2G)*.
