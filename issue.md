# Issue: Halaman Ringkasan/Rekap Setelah Pengerjaan Kuis Harian

## 1. Analisis Masalah
Saat ini, setelah user/petugas menyelesaikan kuis harian, backend (`QuizController@storeAttempt`) langsung mengarahkan user ke halaman klasemen (`/quiz/leaderboard` atau `/quiz/history`). 
Sesuai kebutuhan baru, alur ini perlu diubah. User seharusnya diarahkan ke **halaman rekap/ringkasan pengerjaan kuis** terlebih dahulu. Halaman rekap ini bertugas menampilkan informasi performa kuis yang baru saja diselesaikan, serta menyajikan pesan selebrasi/motivasi secara dinamis berdasarkan performa jawaban benarnya.

## 2. Rencana Perbaikan (Planning)
Berikut adalah langkah-langkah detail untuk diimplementasikan oleh Eksekutor:

### Langkah 1: Buat Route Baru untuk Ringkasan Kuis (`routes/web.php`)
- Tambahkan route GET untuk menampilkan halaman ringkasan.
- Untuk mengakomodasi mode normal (mencatat percobaan) dan mode simulasi/demo, buat struktur route sebagai berikut:
  - Route Normal: `Route::get('/quiz/attempts/{attempt}/summary', [QuizController::class, 'summary'])->name('quiz.summary');`
  - Route Demo: `Route::get('/quiz/{quiz}/demo-summary', [QuizController::class, 'demoSummary'])->name('quiz.demo-summary');`

### Langkah 2: Tambahkan Method Handler di Backend (`QuizController.php`)
- **Method `summary(QuizAttempt $attempt)`**:
  - Panggil relasi kuis: `$attempt->load('quiz');`
  - Hitung total soal (dari `$attempt->quiz->daily_question_limit` jika kuis harian, atau total soal aktual jika event kuis).
  - Hitung jawaban salah: `jawaban_salah = total_soal - jawaban_benar`.
  - Teruskan data tersebut ke tampilan Inertia.

- **Method `demoSummary(Request $request, Quiz $quiz)`**:
  - Ambil parameter kuis (`score`, `correct_answers`, `time_ms`) dari URL (query string).
  - Hitung jawaban salah dengan logika yang sama seperti method `summary`.
  - Teruskan ke tampilan Inertia yang sama dengan flag khusus penanda mode demo.

- **Modifikasi `storeAttempt`**:
  - Setelah rekaman `QuizAttempt` berhasil dibuat, ubah `redirect` menjadi ke route `quiz.summary` dengan melempar parameter ID attempt yang baru dibuat.
  - Khusus mode simulasi/demo (jika ada flag `is_demo = true`), ubah `redirect` menuju route `quiz.demo-summary` dan teruskan parameter hasil pengerjaan di URL (tanpa menyimpan apa pun ke database).

### Langkah 3: Pembuatan UI Vue Ringkasan (`Summary.vue`)
- Buat file baru: `resources/js/Pages/Petugas/Quiz/Summary.vue`
- Terapkan layout yang sejalan dengan tema gelap (*dark theme*) aplikasi.
- **Informasi yang Wajib Ditampilkan**:
  1. **Skor Akhir** (Tampilkan dengan teks atau lencana besar).
  2. **Jawaban Benar** (Misalnya disertai ikon ceklis).
  3. **Jawaban Salah** (Misalnya disertai ikon silang).
  4. **Waktu Pengerjaan** (Format `time_ms` menjadi teks yang mudah dibaca, contoh: `1 menit 15 detik`).
- **Logika Ucapan / Selebrasi (Wajib Mengikuti Aturan Berikut)**:
  - Hitung persentase *jawaban benar*: `(jawaban_benar / total_soal) * 100`.
  - Jika persentase *jawaban benar* tinggi / 100%, tampilkan pesan selebrasi: `"Selamat Anda mendapatkan Nilai [skor]"`. (Contoh jika nilainya 100: `"Selamat Anda mendapatkan Nilai 100"`).
  - Jika **persentase jawaban benar berada di bawah 60%**, tampilkan pesan penyemangat: `"Semangat! Anda mendapatkan Nilai [skor]. Coba lagi besok untuk hasil lebih baik!"`
- **Aksi / Navigasi Tombol**:
  - Sediakan dua tombol aksi utama di bagian bawah layar persis dengan teks berikut:
    1. Tombol **"Kembali ke Halaman Kuis"** (mengarahkan ke `/quiz`).
    2. Tombol **"Lihat Klasemen"** (mengarahkan ke `/quiz/leaderboard`).
    *(Catatan: Tombol "Lihat Klasemen" dapat disembunyikan jika user bermain di mode demo).*

### Langkah 4: Verifikasi & Pengujian
- Jalankan `npm run dev` atau `npm run build` untuk memproses aset Vue yang baru.
- Lakukan pengecekan pengerjaan simulasi/demo dan pastikan redirect masuk ke halaman ringkasan yang baru dibuat.
- Lakukan pengujian di mana jawaban benar < 60% dan pastikan pesan yang muncul sesuai (contoh: "Semangat! Anda mendapatkan Nilai...").

## 3. Instruksi Eksekusi
- Harap patuhi detail penyusunan elemen di `Summary.vue` seperti skor, waktu, jumlah benar, salah, pesan bersyarat, dan penamaan teks tombol aksi.
- Lakukan *commit* terpisah untuk pembuatan rute, backend controller, dan komponen frontend jika memungkinkan.
