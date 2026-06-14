# Perbaikan Menampilkan Status Bugar (K3) Sesuai Fatigue Check Hari Ini di Dashboard Petugas

## Deskripsi Issue
Pada laman `/dashboard` untuk Petugas, bagian widget "Status Bugar (K3)" saat ini membaca data dari state `auth.user.status_fit`. Padahal, status ini seharusnya mencerminkan hasil dari tes kebugaran (Fatigue Check) terbaru yang dilakukan pada **hari yang sama**. 
- Jika Petugas belum melakukan Fatigue Check pada hari tersebut, status harus menunjukkan "Belum Tes" / "Belum Cek". 
- Jika sudah, maka harus menunjukkan status "FIT" atau "UNFIT" dari hasil tes tersebut.

## Tahapan Perbaikan (Implementation Plan)

Berikut adalah tahapan detail perbaikan yang perlu dieksekusi:

### 1. Update Route Dashboard (`routes/web.php`)
Di dalam `routes/web.php`, cari route `/dashboard` yang me-render view `Petugas/Dashboard`.
- Tambahkan logika untuk mengambil hasil Fatigue Check terbaru (latest) milik user yang login (`$request->user()->fatigueChecks()`) yang dilakukan **pada hari ini** menggunakan kondisi tanggal.
- Buat variabel baru, misalnya `$statusBugarHariIni`, dengan aturan:
  - Jika tidak ada data Fatigue Check di hari ini, nilainya adalah `null`.
  - Jika ada data, nilainya adalah properti status kebugaran dari objek tes tersebut (ambil nilai dari kolom `is_fit` atau properti yang relevan yang bernilai `boolean`).
- Lempar variabel ini sebagai prop ke komponen Inertia:
```php
$latestFatigueCheckToday = $request->user()->fatigueChecks()
    ->whereDate('created_at', now()->toDateString())
    ->latest()
    ->first();

$statusBugarHariIni = $latestFatigueCheckToday ? $latestFatigueCheckToday->is_fit : null;

return Inertia::render('Petugas/Dashboard', [
    'activeEventQuiz' => $activeEventQuiz,
    'statusBugarHariIni' => $statusBugarHariIni,
]);
```

### 2. Update Vue Component (`resources/js/Pages/Petugas/Dashboard.vue`)
- Tambahkan properti `statusBugarHariIni` ke dalam pendefinisian prop `defineProps` (jangan hapus prop lain yang sudah ada):
```js
defineProps({
    auth: Object,
    activeEventQuiz: Object,
    statusBugarHariIni: [Boolean, null],
});
```
- Cari blok kode HTML/template untuk widget "Status Bugar (K3)". Ubah referensi kondisi pengecekan dari properti `auth.user.status_fit` menjadi menggunakan prop `statusBugarHariIni`.
```vue
<!-- Status Fit Widget -->
<div class="bg-slate-900/60 backdrop-blur border border-slate-800 rounded-xl p-4 flex flex-col justify-between">
    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Status Bugar (K3)</span>
    <div v-if="statusBugarHariIni === null" class="mt-2 flex items-center gap-2">
        <span class="h-3 w-3 rounded-full bg-amber-500 animate-pulse"></span>
        <span class="text-xs font-bold text-amber-500 uppercase tracking-wider">Belum Tes</span>
    </div>
    <div v-else-if="statusBugarHariIni === true" class="mt-2 flex items-center gap-2">
        <span class="h-3 w-3 rounded-full bg-emerald-500 shadow-[0_0_10px_rgba(16,185,129,0.5)]"></span>
        <span class="text-xs font-extrabold text-emerald-400 uppercase tracking-wider">FIT</span>
    </div>
    <div v-else class="mt-2 flex items-center gap-2">
        <span class="h-3 w-3 rounded-full bg-rose-500 shadow-[0_0_10px_rgba(239,68,68,0.5)]"></span>
        <span class="text-xs font-extrabold text-rose-455 text-rose-400 uppercase tracking-wider">UNFIT</span>
    </div>
</div>
```

### 3. Testing & Validasi
- Jalankan test suite (`php artisan test`) untuk memastikan route dashboard tidak error.
- Jalankan aplikasi (`npm run dev`) dan buka dashboard sebagai akun Petugas.
- Pastikan untuk hari di mana Petugas belum melakukan Fatigue Check, dashboard menampilkan widget dengan keterangan "BELUM TES".
- Lakukan pengisian form Fatigue Check. Setelah kembali ke dashboard, pastikan widget berubah menjadi "FIT" atau "UNFIT" sesuai dengan hasil tes yang disubmit hari itu.
