# Issue: Tautan Menu dan Redesain UI/UX Modul Fatigue Check

## Deskripsi Masalah
1. **Akses Menu**: Saat ini fitur Fatigue Check sudah diimplementasikan (kuesioner dan tes reaksi), namun tautan (link) untuk mengakses halaman `/fatigue/questionnaire` belum tersedia di dashboard petugas (`Petugas/Dashboard.vue`), sehingga petugas tidak bisa memulai proses.
2. **Inkonsistensi Desain**: Halaman-halaman Fatigue Check (`Questionnaire.vue`, `ReactionTest.vue`, dan `Result.vue`) saat ini masih menggunakan `AuthenticatedLayout` bawaan Laravel Breeze dengan tema terang (putih/abu-abu). Desain ini tidak konsisten dengan desain utama aplikasi (seperti di `Petugas/Dashboard.vue`) yang menggunakan `MobileAppLayout` dengan tema gelap modern (dark slate, gradient, glassmorphism, dan aksen warna amber/indigo yang touch-friendly).

## Tujuan (Goals)
1. Menghubungkan tombol "Mulai Fatigue Check" di Dashboard Petugas agar mengarah ke halaman kuesioner.
2. Mengubah layout dan gaya visual seluruh halaman modul Fatigue Check agar selaras dengan tema UI/UX modern yang ada di aplikasi.

---

## Rencana Implementasi Terperinci (Implementation Plan)

### Tahap 1: Menautkan Akses pada Dashboard Petugas
**File yang diubah**: `resources/js/Pages/Petugas/Dashboard.vue`

1. **Ubah tag button menjadi Link Inertia**:
   - Cari elemen `<button>` dengan teks "Mulai Fatigue Check" (sekitar baris 102).
   - Ubah tag `<button>` menjadi komponen `<Link>` dari `@inertiajs/vue3`.
   - Tambahkan atribut `href` yang mengarah ke route fatigue: `:href="route('fatigue.questionnaire')"`.
   - Pertahankan seluruh class CSS bawaannya agar styling tombol tetap sama.

### Tahap 2: Redesain Halaman Kuesioner (Questionnaire)
**File yang diubah**: `resources/js/Pages/Fatigue/Questionnaire.vue`

1. **Ubah Layout Component**: 
   - Ganti `AuthenticatedLayout` menjadi `MobileAppLayout` (sesuaikan import-nya `import MobileAppLayout from '@/Layouts/MobileAppLayout.vue'`).
2. **Penyesuaian Background & Typography**:
   - Hapus class wrapper bawaan Breeze (`bg-white`, `text-gray-900`, `sm:rounded-lg`, dll).
   - Gunakan tema gelap: `bg-slate-900`, teks `text-slate-200`, dan border `border-slate-800` (mengikuti pola desain dashboard).
3. **Styling Form/Input**:
   - Ubah container setiap pertanyaan menjadi card dengan `bg-slate-800/50`, `border-slate-700/50`, `rounded-xl`, dan `backdrop-blur`.
   - Ubah warna teks pertanyaan menjadi `text-slate-200`.
   - Sesuaikan radio button agar lebih menonjol di tema gelap, misalnya mengganti warna teks opsi ("Ya"/"Tidak") menjadi `text-slate-300` dan warna aksen input menjadi `text-amber-500` atau emerald/red yang sesuai dengan tema gelap.
4. **Styling Tombol**:
   - Ganti `PrimaryButton` bawaan dengan tombol custom berbentuk gradient `bg-gradient-to-r from-amber-500 to-orange-500` dengan font `text-slate-950 font-extrabold`.

### Tahap 3: Redesain Halaman Tes Reaksi (Reaction Test)
**File yang diubah**: `resources/js/Pages/Fatigue/ReactionTest.vue`

1. **Ubah Layout Component**: Ganti `AuthenticatedLayout` dengan `MobileAppLayout`.
2. **Penyesuaian Styling Keseluruhan**:
   - Gunakan layouting terpusat di layar mobile.
   - Hapus kotak putih bawaan Breeze.
3. **Styling Elemen Tes Reaksi (Lampu/Box)**:
   - Area tes reaksi harus terlihat modern. Misalnya, tambahkan efek glow/shadow-lg pada kotak lampu.
   - Status "Tunggu" (Merah): `bg-red-500 shadow-[0_0_20px_rgba(239,68,68,0.6)]`
   - Status "Klik" (Hijau): `bg-emerald-500 shadow-[0_0_30px_rgba(16,185,129,0.8)]`
   - Tombol instruksi dan hasil parsial (ms) harus disesuaikan ke font yang lebih tebal (`font-black`) dengan warna cerah di atas background `slate`.
4. **Layout Percobaan (Attempts)**:
   - Buat list riwayat percobaan (attempt 1, 2, 3) dalam bentuk pills atau list items transparan `bg-slate-800/40 border border-slate-700` dengan warna teks putih/amber.

### Tahap 4: Redesain Halaman Hasil (Result)
**File yang diubah**: `resources/js/Pages/Fatigue/Result.vue`

1. **Ubah Layout Component**: Ganti `AuthenticatedLayout` dengan `MobileAppLayout`.
2. **Styling Kartu Hasil (Fit/Unfit)**:
   - Buat agar kartu hasil sangat mencolok sebagai feedback instan bagi user.
   - **Fit to Work (Aman)**: Gunakan gradient `from-emerald-900 to-slate-900`, icon checkmark besar hijau (`text-emerald-400`), dan border `border-emerald-500/30`.
   - **Unfit (Tidak Aman)**: Gunakan gradient `from-red-900 to-slate-900`, icon warning merah (`text-red-400`), dan border `border-red-500/30`.
3. **Ringkasan Waktu Reaksi**:
   - Tampilkan Rata-rata Waktu Reaksi dalam typography yang besar dan jelas (misalnya `text-4xl font-black text-amber-500`).
4. **Tombol "Kembali ke Dashboard"**:
   - Gunakan tombol gradient ala MobileApp (amber/orange) yang full-width, tebal, dan memiliki shadow `shadow-amber-500/20`.

---

## Langkah Eksekusi (Untuk Junior Programmer/AI)
1. **Branching**: Buat branch baru untuk pengerjaan issue ini (contoh: `feature/fatigue-redesign`).
2. **Eksekusi Tahap 1**: Lakukan binding button Dashboard ke route kuesioner.
3. **Eksekusi Tahap 2, 3, & 4**: Terapkan redesain pada ketiga file Vue secara bertahap. Cek ulang di browser setiap kali satu file selesai diubah untuk memastikan layout `MobileAppLayout` berjalan baik dan tidak ada style yang pecah.
4. **Verifikasi**:
   - Login sebagai Petugas lapangan.
   - Klik menu "Mulai Fatigue Check" dari dashboard.
   - Lakukan kuesioner, pastikan tampilan gelap (dark mode) berjalan.
   - Lakukan tes reaksi, pastikan tombol hijau/merah responsif dan secara visual bagus.
   - Lihat halaman Result dan pastikan indikator Fit/Unfit menonjol.
   - Kembali ke dashboard.
5. Jika semua sudah sesuai kriteria, commit hasil kerja dan siapkan untuk direview.
