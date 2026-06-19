# Issue: Perbaikan Keamanan, Performa & Kualitas Kode (Effort Kecil)

## Deskripsi Issue
Kumpulan perbaikan berprioritas tinggi yang membutuhkan effort implementasi kecil (estimasi total: 2–4 jam). Setiap task berdiri sendiri dan dapat dikerjakan secara independen oleh satu developer.

---

## Task 1: Buat Middleware Admin & Terapkan di Route Group

### Konteks
Saat ini seluruh rute admin di `routes/web.php` (line 68–105) hanya dilindungi oleh middleware `auth` + `verified`. Artinya user biasa (Petugas) yang sudah login bisa mengakses URL admin. Otorisasi admin dilakukan manual di masing-masing controller, tapi beberapa controller (`CategoryController`, `LocationController`, `FatigueQuestionController`) **tidak melakukan pengecekan admin sama sekali**.

### Langkah Implementasi

#### 1.1 Buat file middleware baru
**Buat file:** `app/Http/Middleware/EnsureUserIsAdmin.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya admin yang dapat mengakses halaman ini.');
        }

        return $next($request);
    }
}
```

#### 1.2 Daftarkan middleware di Kernel
**Modifikasi file:** `app/Http/Kernel.php`

Tambahkan entry baru di array `$middlewareAliases`:
```php
protected $middlewareAliases = [
    // ... existing aliases
    'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
];
```

#### 1.3 Terapkan middleware pada route group admin
**Modifikasi file:** `routes/web.php` (line 68)

Ubah dari:
```php
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
```
Menjadi:
```php
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
```

#### 1.4 Hapus pengecekan manual `isAdmin()` yang redundan
Setelah middleware diterapkan, hapus semua blok kode `if (!$request->user()->isAdmin()) abort(403);` atau `if (!$request->user()->isAdmin()) return ...` dari controller-controller berikut:

| File | Method | Line |
|---|---|---|
| `app/Http/Controllers/Admin/QuizController.php` | `history()` | 189–191 |
| `app/Http/Controllers/Admin/QuizController.php` | `exportHistory()` | 245–247 |
| `app/Http/Controllers/Admin/LeaderboardController.php` | `dailyIndex()` | 139–141 |
| `app/Http/Controllers/Admin/LeaderboardController.php` | `eventIndex()` | 237–239 |
| `app/Http/Controllers/Admin/LeaderboardController.php` | `exportDaily()` | 330 |
| `app/Http/Controllers/Admin/LeaderboardController.php` | `exportDailyPdf()` | 342 |
| `app/Http/Controllers/Admin/LeaderboardController.php` | `exportEvent()` | 427 |

> **Catatan:** Jangan hapus pengecekan `isAdmin()` di `DashboardController` dan `IncidentController` karena controller tersebut juga memiliki method yang digunakan oleh user biasa (bukan hanya admin). Method-method admin di controller tersebut (yang berada di bawah route admin group) bisa dihapus pengecekannya, tapi method yang di-route di luar admin group harus tetap mengecek manual.

### Verifikasi
1. Login sebagai user Petugas, akses URL `/admin/dashboard` → Harus mendapat response 403.
2. Login sebagai Admin, akses URL `/admin/dashboard` → Harus bisa diakses normal.
3. Jalankan test: `php artisan test --filter=IncidentReportTest`

---

## Task 2: Ganti `$request->all()` menjadi `$request->validated()`

### Konteks
Di `Admin\QuizController`, method `store()` dan `update()` menggunakan `$request->all()` untuk mass assignment. Ini berbahaya karena user bisa menyisipkan field tak terduga (misalnya `id`, `created_at`).

### Langkah Implementasi

**Modifikasi file:** `app/Http/Controllers/Admin/QuizController.php`

#### 2.1 Method `store()` (line 99)
Ubah dari:
```php
Quiz::create($request->all());
```
Menjadi:
```php
Quiz::create($request->validated());
```

#### 2.2 Method `update()` (line 124)
Ubah dari:
```php
$quiz->update($request->all());
```
Menjadi:
```php
$quiz->update($request->validated());
```

### Verifikasi
1. Buka halaman Admin > Kelola Kuis > Tambah Kuis Baru, isi form, simpan → Harus berhasil.
2. Edit kuis yang sudah ada, ubah judul, simpan → Harus berhasil diperbarui.

---

## Task 3: Tambahkan Database Indexes

### Konteks
Beberapa tabel yang sering di-query untuk dashboard, leaderboard, dan filter belum memiliki index. Ini menyebabkan full table scan pada query yang sering dijalankan.

### Langkah Implementasi

#### 3.1 Buat migration baru
Jalankan perintah:
```bash
php artisan make:migration add_performance_indexes --table=quiz_attempts
```

#### 3.2 Isi migration
**Modifikasi file migration yang baru dibuat:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // quiz_attempts indexes
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->index('month_year', 'idx_qa_month_year');
            $table->index(['user_id', 'quiz_id', 'created_at'], 'idx_qa_user_quiz_date');
        });

        // fatigue_checks indexes
        Schema::table('fatigue_checks', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'idx_fc_user_date');
        });

        // incidents indexes
        Schema::table('incidents', function (Blueprint $table) {
            $table->index('status', 'idx_incidents_status');
            $table->index('category', 'idx_incidents_category');
            $table->index('severity', 'idx_incidents_severity');
            $table->index(['status', 'severity'], 'idx_incidents_status_severity');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropIndex('idx_qa_month_year');
            $table->dropIndex('idx_qa_user_quiz_date');
        });

        Schema::table('fatigue_checks', function (Blueprint $table) {
            $table->dropIndex('idx_fc_user_date');
        });

        Schema::table('incidents', function (Blueprint $table) {
            $table->dropIndex('idx_incidents_status');
            $table->dropIndex('idx_incidents_category');
            $table->dropIndex('idx_incidents_severity');
            $table->dropIndex('idx_incidents_status_severity');
        });
    }
};
```

#### 3.3 Jalankan migration
```bash
php artisan migrate
```

### Verifikasi
1. Pastikan migration berhasil: `php artisan migrate:status`
2. Buka admin dashboard → Pastikan tetap berfungsi normal.
3. Opsional: Jalankan `EXPLAIN` pada query dashboard untuk memastikan index digunakan.

---

## Task 4: Optimasi Query Incident Dashboard (6→1 Query)

### Konteks
Di `IncidentController::dashboard()`, trend 6 bulan menggunakan loop yang menjalankan 6 query `COUNT` terpisah.

### Langkah Implementasi

**Modifikasi file:** `app/Http/Controllers/IncidentController.php` method `dashboard()`

Ubah blok kode tren (sekitar line 186–196) dari:

```php
$trendLabels = [];
$trendCounts = [];
$monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
for ($i = 5; $i >= 0; $i--) {
    $month = now()->subMonths($i);
    $trendLabels[] = $monthNames[$month->month - 1] . ' ' . $month->year;
    $trendCounts[] = Incident::whereMonth('created_at', $month->month)
        ->whereYear('created_at', $month->year)
        ->count();
}
```

Menjadi:

```php
$monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$startMonth = now()->subMonths(5)->startOfMonth();

$trendRaw = Incident::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_key, COUNT(*) as count")
    ->where('created_at', '>=', $startMonth)
    ->groupBy('month_key')
    ->orderBy('month_key')
    ->pluck('count', 'month_key');

$trendLabels = [];
$trendCounts = [];
for ($i = 5; $i >= 0; $i--) {
    $m = now()->subMonths($i);
    $key = $m->format('Y-m');
    $trendLabels[] = $monthNames[$m->month - 1] . ' ' . $m->year;
    $trendCounts[] = $trendRaw->get($key, 0);
}
```

Juga optimasi 4 query komposisi terpisah (line 199–204) menjadi 1 query:

Ubah dari:
```php
$composition = [
    'unsafe_condition' => Incident::where('category', 'unsafe_condition')->count(),
    'unsafe_act' => Incident::where('category', 'unsafe_act')->count(),
    'near_miss' => Incident::where('category', 'near_miss')->count(),
    'positive_observation' => Incident::where('category', 'positive_observation')->count(),
];
```

Menjadi:
```php
$compositionRaw = Incident::selectRaw("category, COUNT(*) as count")
    ->groupBy('category')
    ->pluck('count', 'category');

$composition = [
    'unsafe_condition' => $compositionRaw->get('unsafe_condition', 0),
    'unsafe_act' => $compositionRaw->get('unsafe_act', 0),
    'near_miss' => $compositionRaw->get('near_miss', 0),
    'positive_observation' => $compositionRaw->get('positive_observation', 0),
];
```

### Verifikasi
1. Buka halaman Admin > Dashboard Insiden → Pastikan chart tren dan komposisi masih menampilkan data dengan benar.
2. Periksa jumlah query di Laravel Debugbar (jika tersedia) untuk memastikan pengurangan query.

---

## Task 5: Perbaiki Double Query `allIncidents`

### Konteks
Di `IncidentController::adminIndex()`, variabel `$allIncidents` memuat **seluruh** tabel incidents tanpa limit, yang akan semakin lambat seiring pertumbuhan data.

### Langkah Implementasi

**Modifikasi file:** `app/Http/Controllers/IncidentController.php` method `adminIndex()`

Ubah dari (sekitar line 96–104):
```php
// All incidents for summary widget and global map markers (unfiltered)
$allIncidents = Incident::with('user')->latest()->get();

// Paginated incidents for the table list
$incidents = $query->latest()->paginate(10)->withQueryString();

return Inertia::render('Admin/Incidents/Index', [
    'incidents' => $incidents,
    'allIncidents' => $allIncidents,
    'filters' => $request->only(['status', 'category', 'severity']),
    ...
]);
```

Menjadi:
```php
// Summary stats (aggregate, bukan load seluruh data)
$summaryStats = [
    'total' => Incident::count(),
    'open' => Incident::where('status', 'open')->count(),
    'investigating' => Incident::where('status', 'investigating')->count(),
    'closed' => Incident::where('status', 'closed')->count(),
];

// Map markers: hanya field yg dibutuhkan + hanya yang punya koordinat
$mapIncidents = Incident::select('id', 'category', 'severity', 'status', 'latitude', 'longitude', 'created_at')
    ->whereNotNull('latitude')
    ->whereNotNull('longitude')
    ->latest()
    ->limit(200)
    ->get();

// Paginated incidents for the table list
$incidents = $query->latest()->paginate(10)->withQueryString();

return Inertia::render('Admin/Incidents/Index', [
    'incidents' => $incidents,
    'summaryStats' => $summaryStats,
    'mapIncidents' => $mapIncidents,
    'filters' => $request->only(['status', 'category', 'severity']),
    ...
]);
```

> **PENTING:** Setelah mengubah backend, perlu juga menyesuaikan frontend (`resources/js/Pages/Admin/Incidents/Index.vue`) untuk menggunakan `summaryStats` dan `mapIncidents` sebagai pengganti `allIncidents`. Periksa props yang digunakan di komponen Vue dan sesuaikan.

### Verifikasi
1. Buka halaman Admin > Laporan Insiden → Pastikan tabel, filter, summary widget, dan peta berfungsi normal.

---

## Task 6: Kurangi Frekuensi Notification Polling

### Konteks
Notification polling saat ini dijalankan setiap 10 detik, menghasilkan 6 request per menit per user yang membuka admin panel.

### Langkah Implementasi

**Modifikasi file:** `resources/js/Layouts/AdminDashboardLayout.vue`

Cari baris (sekitar line 91):
```javascript
pollingInterval = setInterval(fetchNotifications, 10000);
```

Ubah menjadi:
```javascript
pollingInterval = setInterval(fetchNotifications, 30000); // Polling setiap 30 detik
```

### Verifikasi
1. Buka admin panel, buka DevTools > Network tab → Pastikan request `/notifications/unread` muncul setiap ~30 detik, bukan setiap 10 detik.

---

## Task 7: Tambahkan `$casts` ke Model Incident

### Konteks
Model `Incident` tidak memiliki `$casts` untuk kolom `resolved_at` (datetime) dan koordinat (decimal).

### Langkah Implementasi

**Modifikasi file:** `app/Models/Incident.php`

Tambahkan property `$casts` di dalam class Incident (setelah `$fillable`):

```php
protected $casts = [
    'resolved_at' => 'datetime',
    'latitude' => 'decimal:8',
    'longitude' => 'decimal:8',
];
```

### Verifikasi
1. Buka halaman insiden (baik user maupun admin) → Pastikan data tampil normal.
2. Pastikan `resolved_at` sudah otomatis menjadi objek Carbon di backend (bisa dicek di Tinker: `Incident::first()->resolved_at`).

---

## Task 8: Rapikan Import & Code Style di `routes/web.php`

### Konteks
Import `DashboardController` berada di tengah file (line 29). Beberapa controller menggunakan FQCN inline (e.g. `\App\Http\Controllers\FatigueCheckController::class`).

### Langkah Implementasi

**Modifikasi file:** `routes/web.php`

1. Pindahkan `use App\Http\Controllers\DashboardController;` ke bagian atas file (bersama import lainnya, setelah line 7).
2. Tambahkan import untuk controller-controller yang masih menggunakan FQCN inline:
   ```php
   use App\Http\Controllers\FatigueCheckController;
   use App\Http\Controllers\IncidentController;
   use App\Http\Controllers\Admin\CategoryController;
   use App\Http\Controllers\Admin\LocationController;
   use App\Http\Controllers\Admin\QuizController as AdminQuizController;
   use App\Http\Controllers\Admin\QuestionController;
   use App\Http\Controllers\Admin\LeaderboardController;
   use App\Http\Controllers\Admin\UserController;
   use App\Http\Controllers\Admin\FatigueCheckController as AdminFatigueCheckController;
   use App\Http\Controllers\Admin\FatigueQuestionController;
   ```
3. Ganti semua FQCN inline di body file menjadi nama class yang sudah di-import.
4. Hapus baris kosong berlebih.

### Verifikasi
1. Jalankan `php artisan route:list` → Pastikan tidak ada error dan semua rute tetap terdaftar.
2. Jalankan `php artisan test` → Pastikan semua test tetap passed.
