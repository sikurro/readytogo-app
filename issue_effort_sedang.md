# Issue: Perbaikan Arsitektur, Keamanan & Frontend (Effort Sedang)

## Deskripsi Issue
Kumpulan perbaikan berprioritas sedang-tinggi yang membutuhkan effort implementasi sedang (estimasi total: 6–10 jam). Task ini melibatkan refactoring arsitektural dan perubahan yang tersebar di beberapa file.

---

## Task 1: Validasi Skor Kuis di Server-Side

### Konteks
Saat ini skor kuis (`score`, `correct_answers`) dikirim langsung dari browser client. User bisa memanipulasi skor dengan mengubah request. Ini adalah celah keamanan kritis di fitur inti aplikasi.

**File utama:** `app/Http/Controllers/Petugas/QuizController.php`

### Langkah Implementasi

#### 1.1 Modifikasi form request di method `storeAttempt()` 
**File:** `app/Http/Controllers/Petugas/QuizController.php` (method `storeAttempt`, line 146–187)

Ubah validasi dari:
```php
$request->validate([
    'score' => 'required|integer',
    'correct_answers' => 'required|integer',
    'time_ms' => 'required|integer',
    'is_demo' => 'nullable|boolean',
]);
```

Menjadi:
```php
$request->validate([
    'answers' => 'required|array',
    'answers.*' => 'required|integer|exists:answers,id',
    'time_ms' => 'required|integer',
    'is_demo' => 'nullable|boolean',
]);
```

#### 1.2 Hitung skor di server
Setelah validasi, tambahkan logika penghitungan skor di server:

```php
// Ambil jawaban yang dikirim user
$submittedAnswerIds = $request->input('answers'); // array answer_id per question

// Hitung jumlah jawaban benar
$correctCount = \App\Models\Answer::whereIn('id', $submittedAnswerIds)
    ->where('is_correct', true)
    ->count();

// Tentukan total soal
if ($quiz->is_daily_quiz) {
    $totalQuestions = $quiz->daily_question_limit ?: 10;
} else {
    $totalQuestions = $quiz->questions()->count();
}

// Hitung skor (misalnya: 10 poin per jawaban benar)
$scorePerQuestion = 10;
$score = $correctCount * $scorePerQuestion;
```

#### 1.3 Gunakan skor yang dihitung server untuk menyimpan attempt
```php
$attempt = QuizAttempt::create([
    'user_id' => Auth::id(),
    'quiz_id' => $quiz->id,
    'score' => $score,                // dari kalkulasi server
    'correct_answers' => $correctCount, // dari kalkulasi server
    'time_ms' => $request->time_ms,
    'month_year' => date('Y-m'),
]);
```

#### 1.4 Update Demo flow
Untuk mode demo (`is_demo = true`), skor masih bisa dihitung client-side karena hasilnya tidak disimpan. Tapi idealnya tetap dihitung server. Minimal pastikan demo flow juga mengirim `answers`:

```php
if ($request->input('is_demo')) {
    return redirect()->route('quiz.demo-summary', [
        'quiz' => $quiz->id,
        'score' => $score,
        'correct_answers' => $correctCount,
        'time_ms' => $request->time_ms
    ]);
}
```

#### 1.5 Modifikasi Frontend — Kirim Array Jawaban
**File:** `resources/js/Pages/Petugas/Quiz/Play.vue`

Ubah payload submit quiz dari:
```javascript
// SEBELUM (client mengirim skor)
router.post(route('quiz.store', quiz.id), {
    score: calculatedScore,
    correct_answers: correctCount,
    time_ms: elapsedTime,
    is_demo: isDemo,
});
```

Menjadi:
```javascript
// SESUDAH (client mengirim jawaban, server menghitung skor)
router.post(route('quiz.store', quiz.id), {
    answers: selectedAnswerIds, // array: [answerId1, answerId2, ...]
    time_ms: elapsedTime,
    is_demo: isDemo,
});
```

> **PENTING:** Variable `selectedAnswerIds` harus merupakan array berisi ID dari jawaban (`answers.id`) yang dipilih user untuk setiap pertanyaan. Pastikan state ini dikelola di komponen Vue saat user mengklik pilihan jawaban.

### Verifikasi
1. Main kuis harian sebagai Petugas → Pastikan skor yang tersimpan sesuai dengan jawaban yang dipilih.
2. Coba manipulasi request via DevTools/Postman dengan `answers` palsu → Pastikan hasilnya tetap dihitung berdasarkan database.
3. Main kuis dalam mode demo → Pastikan tetap berfungsi.

---

## Task 2: Buat Form Request Classes

### Konteks
Semua validasi saat ini dilakukan inline di controller. Ini membuat controller bloated dan validasi sulit di-reuse.

### Langkah Implementasi

#### 2.1 Buat Form Request untuk Incident
Jalankan:
```bash
php artisan make:request StoreIncidentRequest
```

**File:** `app/Http/Requests/StoreIncidentRequest.php`
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Semua user login bisa membuat laporan
    }

    public function rules(): array
    {
        return [
            'category' => 'required|in:unsafe_condition,unsafe_act,near_miss,positive_observation',
            'severity' => 'required|in:low,medium,high',
            'description' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'image' => 'nullable|image|max:5120',
        ];
    }
}
```

Lalu ubah `IncidentController::store()` dari:
```php
public function store(Request $request)
{
    $validated = $request->validate([...]);
```
Menjadi:
```php
use App\Http\Requests\StoreIncidentRequest;

public function store(StoreIncidentRequest $request)
{
    $validated = $request->validated();
```

#### 2.2 Buat Form Request untuk Quiz (Admin)
Jalankan:
```bash
php artisan make:request Admin/StoreQuizRequest
```

**File:** `app/Http/Requests/Admin/StoreQuizRequest.php`
```php
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Sudah dilindungi middleware admin
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'theme' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'is_daily_quiz' => 'boolean',
            'daily_question_limit' => 'nullable|integer|min:1',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
        ];
    }
}
```

Terapkan di `Admin\QuizController::store()` dan `update()`:
```php
use App\Http\Requests\Admin\StoreQuizRequest;

public function store(StoreQuizRequest $request)
{
    Quiz::create($request->validated());
    // ...
}

public function update(StoreQuizRequest $request, Quiz $quiz)
{
    $quiz->update($request->validated());
    // ...
}
```

#### 2.3 Buat Form Request untuk User (Admin)
Jalankan:
```bash
php artisan make:request Admin/StoreUserRequest
php artisan make:request Admin/UpdateUserRequest
```

**File:** `app/Http/Requests/Admin/StoreUserRequest.php`
```php
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'nip' => 'required|string|max:50|unique:users,nip',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'position' => 'nullable|string|max:255',
            'location_id' => 'nullable|exists:locations,id',
            'status_fit' => 'required|boolean',
            'avatar' => 'nullable|image|max:2048',
        ];
    }
}
```

**File:** `app/Http/Requests/Admin/UpdateUserRequest.php`
```php
<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;
        return [
            'name' => 'required|string|max:255',
            'nip' => 'required|string|max:50|unique:users,nip,' . $userId,
            'email' => 'required|string|email|max:255|unique:users,email,' . $userId,
            'role_id' => 'required|exists:roles,id',
            'position' => 'nullable|string|max:255',
            'location_id' => 'nullable|exists:locations,id',
            'status_fit' => 'required|boolean',
            'avatar' => 'nullable|image|max:2048',
            'remove_avatar' => 'nullable|boolean',
        ];
    }
}
```

Terapkan di `Admin\UserController::store()` dan `update()`.

#### 2.4 Buat Form Request untuk Fatigue Check
Jalankan:
```bash
php artisan make:request StoreFatigueCheckRequest
php artisan make:request ProcessQuestionnaireRequest
```

**File:** `app/Http/Requests/StoreFatigueCheckRequest.php`
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFatigueCheckRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'questionnaire_status' => 'required|boolean',
            'reaction_time_ms' => 'required|integer',
        ];
    }
}
```

**File:** `app/Http/Requests/ProcessQuestionnaireRequest.php`
```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProcessQuestionnaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|boolean',
            'answers' => 'required|array',
        ];
    }
}
```

Terapkan di `FatigueCheckController::store()` dan `processQuestionnaire()`.

### Verifikasi
1. Test seluruh form di aplikasi: Tambah user, tambah kuis, buat insiden, isi kuesioner fatigue → Semuanya harus tetap berfungsi.
2. Kirim request tanpa field required → Pastikan masih mendapatkan error validasi yang benar.
3. Jalankan `php artisan test`.

---

## Task 3: Pisahkan IncidentController (Admin vs User)

### Konteks
`IncidentController` saat ini menangani 2 concern berbeda: user-facing (buat laporan) dan admin-facing (kelola insiden, dashboard, export). Ini perlu dipisah agar lebih mudah di-maintain.

### Langkah Implementasi

#### 3.1 Buat controller baru
**Buat file:** `app/Http/Controllers/Admin/IncidentController.php`

Pindahkan method-method berikut dari `app/Http/Controllers/IncidentController.php` ke controller baru:
- `adminIndex()` → rename menjadi `index()`
- `updateStatus()`
- `dashboard()`
- `adminExport()` → rename menjadi `export()`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IncidentController extends Controller
{
    public function index(Request $request)
    {
        // ... isi dari method adminIndex() yang lama ...
        // Hapus pengecekan isAdmin() karena sudah ada middleware
    }

    public function updateStatus(Request $request, Incident $incident)
    {
        // ... isi dari method updateStatus() yang lama ...
        // Hapus pengecekan isAdmin()
    }

    public function dashboard(Request $request)
    {
        // ... isi dari method dashboard() yang lama ...
        // Hapus pengecekan isAdmin()
    }

    public function export(Request $request)
    {
        // ... isi dari method adminExport() yang lama ...
        // Hapus pengecekan isAdmin()
    }
}
```

#### 3.2 Pindahkan juga method notification ke tempat yang tepat
Method `getUnreadNotifications()` dan `markNotificationsAsRead()` di `IncidentController` sebenarnya tidak spesifik ke incident. Idealnya dipindah ke `NotificationController` baru, tapi untuk saat ini biarkan di `IncidentController` user-facing.

#### 3.3 Bersihkan controller lama
**File:** `app/Http/Controllers/IncidentController.php`

Hapus method-method yang sudah dipindahkan (`adminIndex`, `updateStatus`, `dashboard`, `adminExport`). Controller ini sekarang hanya berisi:
- `index()` — list incident user
- `create()` — form buat insiden
- `store()` — simpan insiden
- `getUnreadNotifications()`
- `markNotificationsAsRead()`

#### 3.4 Update routes
**File:** `routes/web.php`

Ubah rute admin incidents (dalam admin group, line 100–104) dari:
```php
Route::get('incidents/dashboard', [App\Http\Controllers\IncidentController::class, 'dashboard'])->name('incidents.dashboard');
Route::get('incidents/export', [App\Http\Controllers\IncidentController::class, 'adminExport'])->name('incidents.export');
Route::get('incidents', [App\Http\Controllers\IncidentController::class, 'adminIndex'])->name('incidents.index');
Route::put('incidents/{incident}/status', [App\Http\Controllers\IncidentController::class, 'updateStatus'])->name('incidents.update-status');
```

Menjadi:
```php
Route::get('incidents/dashboard', [App\Http\Controllers\Admin\IncidentController::class, 'dashboard'])->name('incidents.dashboard');
Route::get('incidents/export', [App\Http\Controllers\Admin\IncidentController::class, 'export'])->name('incidents.export');
Route::get('incidents', [App\Http\Controllers\Admin\IncidentController::class, 'index'])->name('incidents.index');
Route::put('incidents/{incident}/status', [App\Http\Controllers\Admin\IncidentController::class, 'updateStatus'])->name('incidents.update-status');
```

### Verifikasi
1. Jalankan `php artisan route:list | grep incident` → Pastikan semua rute masih terdaftar dan mengarah ke controller yang benar.
2. Buka halaman user Laporan Insiden → Pastikan berfungsi.
3. Buka halaman admin Laporan Insiden + Dashboard Insiden → Pastikan berfungsi.
4. Jalankan `php artisan test --filter=IncidentReportTest`.

---

## Task 4: Pecah Admin/Dashboard.vue (62KB)

### Konteks
File `resources/js/Pages/Admin/Dashboard.vue` berukuran 62 KB — terlalu besar untuk satu komponen Vue. Ini mempersulit maintenance dan memperlambat hot-reload saat development.

### Langkah Implementasi

#### 4.1 Identifikasi section yang bisa dipecah
Buka `resources/js/Pages/Admin/Dashboard.vue` dan identifikasi section-section utama (biasanya berdasarkan `<div>` wrapper utama). Umumnya berisi:
- Header / stat cards (fatigue hari ini, quiz hari ini)
- Chart fatigue bulanan
- Chart quiz trend 30 hari
- Pie chart (quiz benar/salah)
- Leaderboard mini table
- Incident summary cards
- Incident latest list
- Detail modal/dialog

#### 4.2 Buat komponen-komponen terpisah
Buat direktori: `resources/js/Components/Admin/Dashboard/`

Buat file-file berikut (sesuaikan nama & isi berdasarkan isi Dashboard.vue aktual):

| Komponen Baru | Isi |
|---|---|
| `StatCards.vue` | 4-6 kartu statistik utama (Total User, Fatigue Fit, Quiz Taken, dsb.) |
| `FatigueMonthlyChart.vue` | Chart area/line fatigue bulanan |
| `QuizTrendChart.vue` | Chart line quiz trend 30 hari |
| `QuizPieChart.vue` | Pie chart benar/salah 30 hari |
| `LeaderboardMini.vue` | Tabel top 10 leaderboard |
| `IncidentSummary.vue` | Card incident summary (open/investigating/closed) |
| `LatestIncidents.vue` | Tabel incident terbaru |
| `FatigueDetailModal.vue` | Modal detail fatigue user (jika ada) |

#### 4.3 Refactor Dashboard.vue
**File:** `resources/js/Pages/Admin/Dashboard.vue`

Extract setiap section ke komponen terpisah. Komponen utama menjadi:
```vue
<template>
  <AdminDashboardLayout>
    <Head title="Admin Dashboard" />

    <div class="grid gap-6 ...">
      <StatCards :stats="stats" @click-stat="handleStatClick" />

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <FatigueMonthlyChart :data="fatigueMonthly" />
        <QuizTrendChart :data="quizTrend" />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <QuizPieChart :data="quiz30Days" />
        <LeaderboardMini :leaderboard="top10Leaderboard" />
        <IncidentSummary :data="incidentData" />
      </div>

      <LatestIncidents :incidents="latestIncidents" />
    </div>

    <FatigueDetailModal v-model:show="showFatigueModal" ... />
  </AdminDashboardLayout>
</template>
```

#### Aturan saat memecah:
1. **Props:** Setiap komponen menerima data via props (bukan props drilling berkepanjangan).
2. **Emits:** Jika komponen perlu berkomunikasi ke parent (misalnya klik stat card untuk buka modal), gunakan `emit`.
3. **Chart instance:** Inisialisasi chart tetap di dalam masing-masing komponen chart, bukan di parent.
4. **Reactive state:** State yang hanya digunakan oleh 1 komponen harus ada di dalam komponen tersebut, bukan di parent.

### Verifikasi
1. Buka halaman Admin Dashboard → Pastikan semua card, chart, tabel, dan modal berfungsi identik dengan sebelumnya.
2. Jalankan `npm run build` → Pastikan tidak ada error build.
3. Periksa ukuran file Dashboard.vue setelah refactor — target: di bawah 10 KB.
