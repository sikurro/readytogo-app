# Issue: Error Unknown Column 'category_question.question_id' di Halaman Admin Dashboard

## 1. Analisis Masalah
Saat mengakses halaman `/admin/dashboard`, sistem melempar error SQL:
```text
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'category_question.question_id' in 'field list'
```
Error ini terjadi karena ada pemanggilan relasi *Many-to-Many* (kemungkinan antara model `Category` dan `Question`) yang mengeksekusi query Join ke tabel pivot `category_question`. Namun, tabel pivot tersebut tidak memiliki kolom `question_id` (dan kemungkinan `category_id` juga tidak ada).

Setelah diinvestigasi, file migration untuk tabel pivot ini, yaitu `database/migrations/2026_06_07_030853_create_category_question_table.php`, hanya memuat pendefinisian `$table->id();` dan `$table->timestamps();`. Pengembang sebelumnya lupa menambahkan *foreign key* yang mendefinisikan relasinya.

## 2. Rencana Perbaikan (Planning)
Berikut adalah langkah-langkah detail untuk memperbaiki *bug* ini:

### Langkah 1: Perbarui File Migration
- Buka file `database/migrations/2026_06_07_030853_create_category_question_table.php`.
- Tambahkan kolom *foreign key* untuk `category_id` dan `question_id` di dalam closure `Schema::create`. Pastikan menggunakan atribut `constrained()` dan `onDelete('cascade')` untuk menjaga integritas data.

**Contoh Kode yang Harus Diubah:**
*Sebelum:*
```php
Schema::create('category_question', function (Blueprint $table) {
    $table->id();
    $table->timestamps();
});
```

*Sesudah:*
```php
Schema::create('category_question', function (Blueprint $table) {
    $table->id();
    $table->foreignId('category_id')->constrained()->onDelete('cascade');
    $table->foreignId('question_id')->constrained()->onDelete('cascade');
    $table->timestamps();
});
```

### Langkah 2: Jalankan Ulang Migration
- Karena ini merubah skema struktur database, eksekusi command *refresh migration* di terminal. Disarankan untuk menggunakan `fresh --seed` jika berada di tahap *development* agar data tiruan (*dummy*) langsung terisi kembali dan tidak ada sisa data *corrupt*.
- Buka terminal dan jalankan:
  ```bash
  php artisan migrate:fresh --seed
  ```
- *Catatan: Jika eksekusi tidak dapat menghapus seluruh DB (misal karena constraint), lakukan `php artisan migrate:rollback` berulang kali sampai tabel tersebut drop, lalu migrate ulang.*

### Langkah 3: Verifikasi
- Akses kembali halaman `/admin/dashboard` dan pastikan tidak ada lagi layar *error 500* terkait SQLSTATE tersebut.
- Uji coba menambahkan relasi kategori pada kuis/pertanyaan dari antarmuka Admin untuk memastikan tabel pivot berhasil menyimpan *foreign key* (opsional, sebagai *sanity check*).

## 3. Instruksi Eksekusi
- Silakan terapkan perbaikan secara presisi sesuai instruksi di atas.
- Setelah *bug* ini *resolved* dan *dashboard* kembali normal, lakukan commit dengan pesan jelas (contoh: `fix: add foreign keys to category_question table`).
- Jangan ubah nama file *migration* atau membuat *migration* baru untuk menambah kolom; perbarui saja *migration* yang sudah ada.
