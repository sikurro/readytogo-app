# Issue: Redesain Tampilan Halaman Summary Kuis dan List Kuis Petugas

Halaman ringkasan (summary) kuis dan daftar kuis (`/quiz`) petugas saat ini kurang proporsional dan tidak konsisten dengan desain gelap (dark theme) aplikasi Ready To GO yang modern dan premium.

Tugas Anda adalah memodifikasi halaman-halaman tersebut agar terlihat lebih proporsional, estetis, dan memiliki micro-interaction yang memuaskan.

---

## 1. Perbaikan Halaman Summary Kuis (`resources/js/Pages/Petugas/Quiz/Summary.vue`)

### Masalah Desain Saat Ini:
- Terdapat pembungkus luar bertumpuk (`bg-gray-900 min-h-screen py-12`) di dalam `MobileAppLayout` yang merusak margin, padding, dan konsistensi warna latar belakang.
- Tampilan skor terlalu kecil, kurang menonjol, dan bentuk pil vertikalnya kurang proporsional.
- Tombol aksi utama ("Kembali ke Halaman Kuis" dan "Lihat Klasemen") polos tanpa ikon serta tidak memiliki efek interaksi yang dinamis.

### Instruksi Perubahan:
1. **Struktur Container**:
   - Hapus pembungkus `py-12 bg-gray-900 min-h-screen text-white` beserta selector turunannya yang tidak perlu.
   - Biarkan elemen langsung dibungkus di bawah `MobileAppLayout` agar membaur dengan latar belakang `bg-slate-950` bawaan layout.
2. **Desain Ulang Kartu Selebrasi**:
   - Gunakan gaya glassmorphism: `bg-slate-900/80 backdrop-blur border border-slate-800 rounded-3xl p-6 shadow-xl`.
   - Atur pesan selebrasi agar memiliki ukuran font yang proporsional (`text-2xl` sampai `text-3xl`) dengan gradasi teks yang indah.
3. **Desain Ulang Badge Skor (Score Card)**:
   - Buat representasi skor menjadi sangat menonjol dan memukau. Gunakan lingkaran besar yang memiliki efek glow atau gradasi melingkar (ring) yang tebal.
   - Tampilkan label "SKOR ANDA" dengan huruf kecil kapital tebal (`text-[11px] tracking-wider text-slate-400 font-bold`).
   - Perbesar angka skor menjadi `text-5xl font-black text-amber-500`.
4. **Desain Ulang Tombol Aksi**:
   - Tambahkan SVG Icon pada masing-masing tombol:
     - **Kembali ke Halaman Kuis**: Tambahkan ikon panah kiri atau ikon kuis (buku/list).
     - **Lihat Klasemen**: Tambahkan ikon piala (trophy).
   - Berikan transisi halus (`transition-all active:scale-95 duration-200`) agar tombol terasa interaktif saat ditekan.

---

## 2. Perbaikan Halaman List Kuis Petugas (`resources/js/Pages/Petugas/Quiz/Index.vue`)

### Masalah Desain Saat Ini:
- Menggunakan latar belakang putih (`bg-white`) yang sangat kontras dan tidak cocok dengan tema gelap aplikasi Ready To GO (`bg-slate-950`).
- Kartu kuis terlihat kuno dengan border abu-abu standar.
- Tombol aksi tidak seragam dan kurang menarik.

### Instruksi Perubahan:
1. **Tema Gelap Penuh**:
   - Ubah container utama dari `bg-white shadow-sm sm:rounded-lg p-6` menjadi transparan/glassmorphic tanpa background putih.
   - Gunakan tipografi berwarna terang (`text-slate-100` untuk judul kuis, `text-slate-400` untuk sub-informasi).
2. **Desain Ulang Kartu Kuis**:
   - Setiap kartu kuis menggunakan styling: `bg-slate-900 border border-slate-800 rounded-2xl p-5 shadow-xl hover:border-slate-700/80 transition-all relative overflow-hidden`.
   - Badge tipe kuis ("KUIS HARIAN" / "EVENT QUIZ"):
     - Kuis Harian: Gunakan gradasi `bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-black px-3 py-1 text-[10px] tracking-widest uppercase rounded-bl-xl`.
     - Event Quiz: Gunakan gradasi `bg-gradient-to-r from-purple-500 to-indigo-500 text-white font-black px-3 py-1 text-[10px] tracking-widest uppercase rounded-bl-xl`.
3. **Desain Ulang Tombol Aksi**:
   - Tombol **Mulai Kuis**: Ubah menjadi tombol utama berwarna kuning/amber gradasi yang kontras (`bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950 font-extrabold`), mirip dengan tombol Fatigue Check.
   - Tombol **Simulasi Kuis (Demo)**: Ubah menjadi tombol outline (`border border-slate-700 hover:bg-slate-800 text-slate-300 font-bold`).
   - Tombol **Selesai Dikerjakan**: Berikan warna redup yang elegan (`bg-slate-800 text-slate-500 border border-slate-800/50 cursor-not-allowed`).
4. **Desain Ulang Tombol Navigasi Bawah**:
   - Ubah tombol "Lihat Klasemen" dan "Lihat Riwayat Kuis Saya" menjadi bar aksi modern dengan ikon SVG di sebelah kiri dan ikon panah chevron kanan di sebelah kanan.
   - Gunakan layout flexbox dengan `bg-slate-900 border border-slate-800 hover:bg-slate-800/80 text-slate-200 font-bold p-4 rounded-xl`.

---

## 3. Rencana Verifikasi

- Pastikan tidak ada duplikasi background ganda atau scrollbar horizontal pada tampilan mobile.
- Verifikasi bahwa semua data dinamis (skor, jumlah jawaban benar/salah, waktu pengerjaan) masih ter-render dengan benar.
- Uji navigasi tombol-tombol agar mengarah ke rute yang tepat tanpa ada error JavaScript.
