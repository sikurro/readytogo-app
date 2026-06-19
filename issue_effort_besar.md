# Issue: Refactoring Besar — DashboardController, Konsolidasi Chart & Test Coverage

## Deskripsi Issue
Kumpulan perbaikan berskala besar yang membutuhkan effort implementasi tinggi (estimasi total: 15–25 jam). Task ini melibatkan refactoring arsitektural mendalam yang berdampak pada banyak file.

> **PERINGATAN:** Task-task ini memiliki dependensi dan risiko regresi yang lebih tinggi. Disarankan dikerjakan setelah task effort kecil dan sedang selesai, agar fondasi (middleware, Form Request, indexes) sudah tersedia.

---

## Task 1: Refactor DashboardController — Hapus Duplikasi & Service Layer

### Konteks
`DashboardController.php` memiliki 473 baris dengan duplikasi query masif antara `adminIndex()` dan `chartData()`. Banyak kalkulasi yang seharusnya di-database malah dilakukan di PHP loop, menyebabkan performa buruk dan kode sulit di-maintain.

**File utama:** `app/Http/Controllers/DashboardController.php`

### Pra-syarat
- ✅ Task effort kecil "Database Indexes" sudah selesai
- ✅ Task effort kecil "Admin Middleware" sudah selesai

### Langkah Implementasi

#### 1.1 Buat Service Classes
Buat direktori `app/Services/` dan buat 3 service class:

**File baru:** `app/Services/FatigueStatsService.php`
```php
<?php

namespace App\Services;

use App\Models\FatigueCheck;
use App\Models\User;
use Illuminate\Support\Collection;

class FatigueStatsService
{
    /**
     * Hitung total user Petugas.
     */
    public function getTotalPetugasCount(): int
    {
        return User::whereHas('role', fn($q) => $q->where('name', 'Petugas'))->count();
    }

    /**
     * Dapatkan ringkasan fatigue hari ini (fit, unfit, not tested).
     */
    public function getTodaySummary(): array
    {
        $totalUsers = $this->getTotalPetugasCount();

        $latestChecksToday = FatigueCheck::whereDate('created_at', today())
            ->whereIn('id', function ($query) {
                $query->selectRaw('max(id)')
                    ->from('fatigue_checks')
                    ->whereDate('created_at', today())
                    ->groupBy('user_id');
            })->get();

        $tested = $latestChecksToday->count();
        $fit = $latestChecksToday->where('is_fit', true)->count();
        $unfit = $latestChecksToday->where('is_fit', false)->count();
        $notTested = max(0, $totalUsers - $tested);

        return compact('totalUsers', 'tested', 'fit', 'unfit', 'notTested');
    }

    /**
     * Data chart fatigue bulanan (tanggal 1–akhir bulan).
     * Menggunakan aggregate query, bukan load semua data ke memory.
     */
    public function getMonthlyChartData(): array
    {
        $totalUsers = $this->getTotalPetugasCount();
        $daysInMonth = now()->daysInMonth;

        // Aggregate langsung di DB: group by day, hitung fit/unfit/total
        $dailyStats = FatigueCheck::selectRaw("
                DAY(created_at) as day_num,
                SUM(CASE WHEN is_fit = 1 THEN 1 ELSE 0 END) as fit_count,
                SUM(CASE WHEN is_fit = 0 THEN 1 ELSE 0 END) as unfit_count,
                COUNT(*) as total_count,
                COUNT(DISTINCT user_id) as tested_users
            ")
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->groupByRaw('DAY(created_at)')
            ->get()
            ->keyBy('day_num');

        $labels = [];
        $fit = [];
        $unfit = [];
        $total = [];
        $notTested = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dayStr = str_pad($day, 2, '0', STR_PAD_LEFT);
            $labels[] = $dayStr;

            $stat = $dailyStats->get($day);
            $fit[] = $stat ? (int)$stat->fit_count : 0;
            $unfit[] = $stat ? (int)$stat->unfit_count : 0;
            $total[] = $stat ? (int)$stat->total_count : 0;
            $notTested[] = $stat ? max(0, $totalUsers - (int)$stat->tested_users) : $totalUsers;
        }

        return compact('labels', 'fit', 'unfit', 'total', 'notTested');
    }
}
```

**File baru:** `app/Services/QuizStatsService.php`
```php
<?php

namespace App\Services;

use App\Models\QuizAttempt;
use App\Models\User;

class QuizStatsService
{
    /**
     * Jumlah user Petugas yang sudah/belum mengerjakan kuis harian hari ini.
     */
    public function getTodayParticipation(int $totalUsers): array
    {
        $taken = QuizAttempt::whereDate('created_at', today())
            ->whereHas('quiz', fn($q) => $q->where('is_daily_quiz', true))
            ->distinct('user_id')
            ->count('user_id');

        return [
            'taken' => $taken,
            'notTaken' => max(0, $totalUsers - $taken),
        ];
    }

    /**
     * Data trend quiz 30 hari terakhir.
     * Menggunakan aggregate query per hari.
     */
    public function get30DaysTrend(): array
    {
        $startDate = now()->subDays(29)->startOfDay();
        $endDate = now()->endOfDay();

        $dailyStats = QuizAttempt::selectRaw("
                DATE(created_at) as attempt_date,
                AVG(score) as avg_score,
                SUM(correct_answers) as total_correct,
                COUNT(*) as total_attempts
            ")
            ->whereHas('quiz', fn($q) => $q->where('is_daily_quiz', 1))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupByRaw('DATE(created_at)')
            ->get()
            ->keyBy('attempt_date');

        $labels = [];
        $avgScore = [];
        $totalAttempts = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dateStr = $date->format('Y-m-d');
            $labels[] = $date->format('d-m');

            $stat = $dailyStats->get($dateStr);
            $avgScore[] = $stat ? round((float)$stat->avg_score, 2) : 0;
            $totalAttempts[] = $stat ? (int)$stat->total_attempts : 0;
        }

        return compact('labels', 'avgScore', 'totalAttempts');
    }

    /**
     * Data pie chart 30 hari (jawaban benar vs salah).
     */
    public function get30DaysPieChart(): array
    {
        $startDate = now()->subDays(29)->startOfDay();

        $result = QuizAttempt::selectRaw("SUM(correct_answers) as total_correct")
            ->whereHas('quiz', fn($q) => $q->where('is_daily_quiz', 1))
            ->where('created_at', '>=', $startDate)
            ->first();

        $totalCorrect = (int)($result->total_correct ?? 0);

        // Catatan: total_questions masih membutuhkan join ke quiz.daily_question_limit
        // Untuk saat ini, gunakan pendekatan yang sudah ada tapi dengan eager loading
        $attempts = QuizAttempt::with('quiz:id,daily_question_limit')
            ->whereHas('quiz', fn($q) => $q->where('is_daily_quiz', 1))
            ->where('created_at', '>=', $startDate)
            ->select('id', 'quiz_id', 'correct_answers')
            ->get();

        $totalQuestions = $attempts->sum(function ($attempt) {
            return $attempt->quiz ? ($attempt->quiz->daily_question_limit ?: 10) : 10;
        });

        $totalWrong = max(0, $totalQuestions - $totalCorrect);

        return [
            'correct' => $totalCorrect,
            'wrong' => $totalWrong,
        ];
    }

    /**
     * Top 10 leaderboard bulanan.
     */
    public function getTop10Leaderboard(string $month): \Illuminate\Database\Eloquent\Collection
    {
        return User::whereHas('role', fn($q) => $q->where('name', 'Petugas'))
            ->with(['location'])
            ->withSum(['quizAttempts' => function ($q) use ($month) {
                $q->where('month_year', $month)
                  ->whereHas('quiz', fn($qQuiz) => $qQuiz->where('is_daily_quiz', true));
            }], 'score')
            ->whereHas('quizAttempts', function ($q) use ($month) {
                $q->where('month_year', $month)
                  ->whereHas('quiz', fn($qQuiz) => $qQuiz->where('is_daily_quiz', true));
            })
            ->orderByDesc('quiz_attempts_sum_score')
            ->take(10)
            ->get();
    }
}
```

**File baru:** `app/Services/IncidentStatsService.php`
```php
<?php

namespace App\Services;

use App\Models\Incident;

class IncidentStatsService
{
    /**
     * Statistik insiden berdasarkan status.
     */
    public function getStatusCounts(): array
    {
        $counts = Incident::selectRaw("
                status, COUNT(*) as count
            ")
            ->groupBy('status')
            ->pluck('count', 'status');

        return [
            'total' => $counts->sum(),
            'open' => $counts->get('open', 0),
            'investigating' => $counts->get('investigating', 0),
            'closed' => $counts->get('closed', 0),
        ];
    }

    /**
     * 5 insiden terbaru.
     */
    public function getLatestIncidents(int $limit = 5)
    {
        return Incident::with('user:id,name')
            ->latest()
            ->take($limit)
            ->get();
    }
}
```

#### 1.2 Refactor DashboardController
**Modifikasi file:** `app/Http/Controllers/DashboardController.php`

Refactor `adminIndex()` dan `chartData()` agar menggunakan Service classes:

```php
<?php

namespace App\Http\Controllers;

use App\Services\FatigueStatsService;
use App\Services\QuizStatsService;
use App\Services\IncidentStatsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Quiz;
use App\Models\FatigueCheck;

class DashboardController extends Controller
{
    public function __construct(
        private FatigueStatsService $fatigueStats,
        private QuizStatsService $quizStats,
        private IncidentStatsService $incidentStats,
    ) {}

    /**
     * Petugas Dashboard (tidak berubah signifikan).
     */
    public function index(Request $request)
    {
        // ... (isi method index tetap sama seperti sekarang) ...
    }

    /**
     * Admin Dashboard — Data awal halaman.
     */
    public function adminIndex(Request $request)
    {
        $fatigueSummary = $this->fatigueStats->getTodaySummary();
        $currentMonth = date('Y-m');

        $quizToday = $this->quizStats->getTodayParticipation($fatigueSummary['totalUsers']);
        $quiz30Days = $this->quizStats->get30DaysPieChart();
        $top10 = $this->quizStats->getTop10Leaderboard($currentMonth);
        $latestIncidents = $this->incidentStats->getLatestIncidents();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalUsers' => $fatigueSummary['totalUsers'],
                'testedFatigueToday' => $fatigueSummary['tested'],
                'fitToday' => $fatigueSummary['fit'],
                'unfitToday' => $fatigueSummary['unfit'],
                'notTestedFatigueToday' => $fatigueSummary['notTested'],
                'quizTakenToday' => $quizToday['taken'],
                'quizNotTakenToday' => $quizToday['notTaken'],
                'quiz30DaysCorrect' => $quiz30Days['correct'],
                'quiz30DaysWrong' => $quiz30Days['wrong'],
            ],
            'top10Leaderboard' => $top10,
            'latestIncidents' => $latestIncidents,
        ]);
    }

    /**
     * Chart data endpoint — Data untuk lazy-loaded charts.
     */
    public function chartData(Request $request)
    {
        $fatigueSummary = $this->fatigueStats->getTodaySummary();
        $fatigueMonthly = $this->fatigueStats->getMonthlyChartData();

        $currentMonth = date('Y-m');
        $quizTrend = $this->quizStats->get30DaysTrend();
        $quiz30Days = $this->quizStats->get30DaysPieChart();
        $quizToday = $this->quizStats->getTodayParticipation($fatigueSummary['totalUsers']);
        $top10 = $this->quizStats->getTop10Leaderboard($currentMonth);
        $incidentData = $this->incidentStats->getStatusCounts();
        $latestIncidents = $this->incidentStats->getLatestIncidents();

        return response()->json([
            'fatigueToday' => [
                'fit' => $fatigueSummary['fit'],
                'unfit' => $fatigueSummary['unfit'],
                'notTested' => $fatigueSummary['notTested'],
            ],
            'fatigueMonthly' => $fatigueMonthly,
            'quizTrend' => $quizTrend,
            'quiz30Days' => $quiz30Days,
            'quizToday' => $quizToday,
            'incidentData' => $incidentData,
            'latestIncidents' => $latestIncidents,
            'top10Leaderboard' => $top10,
        ]);
    }

    /**
     * Fatigue details (tetap sama).
     */
    public function fatigueDetails(Request $request)
    {
        // ... (tetap sama) ...
    }

    /**
     * Incident details (tetap sama).
     */
    public function incidentDetails(Request $request)
    {
        // ... (tetap sama) ...
    }
}
```

#### 1.3 Pastikan response format kompatibel
Sebelum mengubah, bandingkan response JSON lama vs baru. Key-key JSON harus IDENTIK agar frontend tidak rusak:

| Key | Lama | Baru | Compatible? |
|---|---|---|---|
| `fatigueMonthly.labels` | Array `['01','02',...]` | Array `['01','02',...]` | ✅ |
| `fatigueMonthly.fit` | Array `[0,0,...]` | Array `[0,0,...]` | ✅ |
| `fatigueMonthly.notTested` | Array `[n,n,...]` | Array `[n,n,...]` | ✅ |
| `quizTrend.avgAccuracy` | Array `[x,x,...]` | **PERLU TAMBAH** | ⚠️ |

> **PENTING:** Periksa apakah frontend menggunakan key `avgAccuracy` dari `quizTrend`. Jika ya, tambahkan ke `QuizStatsService::get30DaysTrend()` return value.

### Verifikasi
1. Buka Admin Dashboard → Pastikan semua kartu statistik menampilkan angka yang benar.
2. Pastikan semua chart memuat data (fatigue monthly, quiz trend, pie chart).
3. Klik kartu fatigue → Modal detail harus masih berfungsi.
4. Bandingkan response JSON `/admin/dashboard/chart-data` sebelum dan sesudah refactor — harus identik.
5. Jalankan `php artisan test`.

---

## Task 2: Konsolidasi Library Chart (Chart.js + ApexCharts → Satu Library)

### Konteks
Project menggunakan DUA library chart: `chart.js` (+ `vue-chartjs` + `chartjs-plugin-datalabels`) dan `apexcharts` (+ `vue3-apexcharts`). Ini menambah bundle size ~1MB yang tidak perlu.

### Pra-syarat
- ✅ Task effort sedang "Pecah Dashboard.vue" sudah selesai (agar lebih mudah migrasi per komponen)

### Langkah Implementasi

#### 2.1 Audit penggunaan chart library
Sebelum memutuskan library mana yang dipertahankan, identifikasi semua file yang menggunakan masing-masing library.

Cari penggunaan Chart.js:
```bash
grep -rl "vue-chartjs\|chart.js\|chartjs-plugin" resources/js/ --include="*.vue" --include="*.js"
```

Cari penggunaan ApexCharts:
```bash
grep -rl "apexcharts\|vue3-apexcharts\|VueApexCharts" resources/js/ --include="*.vue" --include="*.js"
```

#### 2.2 Pilih library yang dipertahankan
**Rekomendasi: Pertahankan ApexCharts** karena:
- Sudah digunakan untuk chart-chart yang lebih baru
- API lebih mudah untuk membuat chart interaktif
- Built-in responsive dan tema gelap

#### 2.3 Migrasi komponen Chart.js ke ApexCharts
Untuk setiap komponen yang menggunakan Chart.js, lakukan migrasi:

**Pola umum migrasi:**

Dari (Chart.js via vue-chartjs):
```vue
<script setup>
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend } from 'chart.js'
ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)
</script>

<template>
  <Bar :data="chartData" :options="chartOptions" />
</template>
```

Menjadi (ApexCharts):
```vue
<script setup>
import VueApexCharts from 'vue3-apexcharts'
</script>

<template>
  <VueApexCharts type="bar" :options="chartOptions" :series="series" height="350" />
</template>
```

**Mapping tipe chart:**

| Chart.js | ApexCharts | Catatan |
|---|---|---|
| `<Bar>` | `type="bar"` | |
| `<Line>` | `type="line"` | |
| `<Pie>` | `type="pie"` | |
| `<Doughnut>` | `type="donut"` | |

**Mapping konfigurasi:**

| Chart.js (options) | ApexCharts (options) |
|---|---|
| `scales.y.beginAtZero` | `yaxis.min: 0` |
| `plugins.legend.display` | `legend.show` |
| `plugins.title.text` | `title.text` |
| `plugins.tooltip` | `tooltip` |
| `responsive: true` | `chart.responsive: true` (default) |

**Mapping data:**

| Chart.js | ApexCharts |
|---|---|
| `data.labels: [...]` | `options.xaxis.categories: [...]` |
| `data.datasets: [{ data: [...], label: '...' }]` | `series: [{ data: [...], name: '...' }]` |
| `data.datasets[0].backgroundColor` | `options.colors` atau `options.fill.colors` |

#### 2.4 Untuk setiap file yang menggunakan Chart.js:
1. Import `VueApexCharts` sebagai pengganti komponen `vue-chartjs`
2. Konversi `chartData` (format Chart.js) → `series` + `options` (format ApexCharts)
3. Ganti template `<Bar :data :options />` → `<VueApexCharts type="bar" :options :series />`
4. Hapus registrasi Chart.js `ChartJS.register(...)`
5. Test secara visual bahwa chart tampil identik

#### 2.5 Hapus dependency Chart.js
Setelah semua migrasi selesai:
```bash
npm uninstall chart.js vue-chartjs chartjs-plugin-datalabels
```

#### 2.6 Verifikasi bundle size
```bash
npm run build
```

Bandingkan output build (size chunks) sebelum dan sesudah migrasi. Target pengurangan: ~300-500 KB.

### Verifikasi
1. Buka **SEMUA halaman** yang memiliki chart:
   - Admin Dashboard (fatigue chart, quiz chart, pie chart)
   - Admin Leaderboard Daily (progress chart, pie chart)
   - Admin Leaderboard Event
   - Admin Incident Dashboard (trend chart, composition chart)
   - Petugas Quiz Summary (jika ada chart)
2. Pastikan setiap chart tampil dengan data yang benar.
3. Pastikan interaksi chart (hover tooltip, klik legend) masih berfungsi.
4. Jalankan `npm run build` → Tidak boleh ada error.

---

## Task 3: Tambahkan Test Coverage untuk Fitur Kritis

### Konteks
Saat ini hanya ada 4 test file. Fitur-fitur kritis seperti quiz flow, fatigue check, admin access control, dan leaderboard belum memiliki automated test.

### Pra-syarat
- ✅ Task effort kecil "Admin Middleware" sudah selesai
- ✅ Task effort sedang "Validasi Skor Server-Side" sudah selesai (untuk test scoring)

### Langkah Implementasi

#### 3.1 Test Admin Access Control
**Buat file:** `tests/Feature/AdminAccessControlTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(); // atau setup roles manual
    }

    /** @test */
    public function petugas_cannot_access_admin_dashboard()
    {
        $petugas = User::factory()->create([
            'role_id' => Role::where('name', 'Petugas')->first()->id,
        ]);

        $response = $this->actingAs($petugas)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_admin_dashboard()
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', 'Admin')->first()->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
    }

    /** @test */
    public function petugas_cannot_access_admin_users()
    {
        $petugas = User::factory()->create([
            'role_id' => Role::where('name', 'Petugas')->first()->id,
        ]);

        $response = $this->actingAs($petugas)->get('/admin/users');
        $response->assertStatus(403);
    }

    /** @test */
    public function petugas_cannot_access_admin_quiz_history()
    {
        $petugas = User::factory()->create([
            'role_id' => Role::where('name', 'Petugas')->first()->id,
        ]);

        $response = $this->actingAs($petugas)->get('/admin/quiz/history');
        $response->assertStatus(403);
    }

    /** @test */
    public function guest_is_redirected_to_login()
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');
    }
}
```

#### 3.2 Test Quiz Flow
**Buat file:** `tests/Feature/QuizFlowTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;
use App\Models\QuizAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $petugas;
    private Quiz $dailyQuiz;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->petugas = User::factory()->create([
            'role_id' => Role::where('name', 'Petugas')->first()->id,
        ]);

        $this->dailyQuiz = Quiz::factory()->create([
            'is_active' => true,
            'is_daily_quiz' => true,
            'daily_question_limit' => 5,
            'duration_minutes' => 10,
        ]);

        // Buat 5 soal dengan jawaban
        for ($i = 0; $i < 5; $i++) {
            $question = Question::factory()->create();
            Answer::factory()->create(['question_id' => $question->id, 'is_correct' => true]);
            Answer::factory()->create(['question_id' => $question->id, 'is_correct' => false]);
        }
    }

    /** @test */
    public function petugas_can_access_quiz_page()
    {
        $response = $this->actingAs($this->petugas)->get('/quiz');
        $response->assertStatus(200);
    }

    /** @test */
    public function petugas_can_play_daily_quiz()
    {
        $response = $this->actingAs($this->petugas)
            ->get(route('quiz.play', $this->dailyQuiz));
        $response->assertStatus(200);
    }

    /** @test */
    public function petugas_cannot_play_inactive_quiz()
    {
        $this->dailyQuiz->update(['is_active' => false]);

        $response = $this->actingAs($this->petugas)
            ->get(route('quiz.play', $this->dailyQuiz));
        $response->assertRedirect(route('quiz.index'));
    }

    /** @test */
    public function petugas_cannot_play_quiz_twice_same_day()
    {
        // Simulasi sudah main hari ini
        QuizAttempt::create([
            'user_id' => $this->petugas->id,
            'quiz_id' => $this->dailyQuiz->id,
            'score' => 50,
            'correct_answers' => 5,
            'time_ms' => 30000,
            'month_year' => date('Y-m'),
        ]);

        $response = $this->actingAs($this->petugas)
            ->get(route('quiz.play', $this->dailyQuiz));
        $response->assertRedirect(route('quiz.index'));
    }

    /** @test */
    public function quiz_attempt_is_stored_correctly()
    {
        // Catatan: Sesuaikan test ini setelah implementasi server-side scoring
        // Test ini memvalidasi bahwa attempt tersimpan di database
        
        $correctAnswer = Answer::where('is_correct', true)->first();
        
        $response = $this->actingAs($this->petugas)
            ->post(route('quiz.store', $this->dailyQuiz), [
                'answers' => [$correctAnswer->id],
                'time_ms' => 25000,
            ]);

        $this->assertDatabaseHas('quiz_attempts', [
            'user_id' => $this->petugas->id,
            'quiz_id' => $this->dailyQuiz->id,
        ]);
    }
}
```

#### 3.3 Test Fatigue Check Flow
**Buat file:** `tests/Feature/FatigueCheckFlowTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\FatigueQuestion;
use App\Models\FatigueCheck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FatigueCheckFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $petugas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->petugas = User::factory()->create([
            'role_id' => Role::where('name', 'Petugas')->first()->id,
        ]);

        // Buat pertanyaan fatigue aktif
        FatigueQuestion::factory()->count(3)->create(['is_active' => true]);
    }

    /** @test */
    public function petugas_can_access_fatigue_hub()
    {
        $response = $this->actingAs($this->petugas)->get('/fatigue/hub');
        $response->assertStatus(200);
    }

    /** @test */
    public function petugas_can_access_questionnaire()
    {
        $response = $this->actingAs($this->petugas)->get('/fatigue/questionnaire');
        $response->assertStatus(200);
    }

    /** @test */
    public function petugas_can_submit_questionnaire()
    {
        $questions = FatigueQuestion::where('is_active', true)->get();
        $answers = [];
        foreach ($questions as $q) {
            $answers[$q->id] = 'ya';
        }

        $response = $this->actingAs($this->petugas)
            ->post('/fatigue/questionnaire', [
                'status' => true,
                'answers' => $answers,
            ]);

        $response->assertRedirect(route('fatigue.test'));
    }

    /** @test */
    public function reaction_test_requires_questionnaire_session()
    {
        // Tanpa mengisi kuesioner dulu
        $response = $this->actingAs($this->petugas)->get('/fatigue/reaction-test');
        $response->assertRedirect(route('fatigue.questionnaire'));
    }

    /** @test */
    public function fatigue_check_stores_result_correctly()
    {
        // Simulasi session kuesioner
        $this->actingAs($this->petugas)
            ->withSession(['fatigue_questionnaire' => [
                'status' => true,
                'answers' => [1 => 'ya', 2 => 'ya', 3 => 'ya'],
            ]])
            ->post('/fatigue/store', [
                'questionnaire_status' => true,
                'reaction_time_ms' => 350,
            ]);

        $this->assertDatabaseHas('fatigue_checks', [
            'user_id' => $this->petugas->id,
            'is_fit' => true,
            'reaction_time_ms' => 350,
        ]);
    }

    /** @test */
    public function high_reaction_time_marks_as_unfit()
    {
        $this->actingAs($this->petugas)
            ->withSession(['fatigue_questionnaire' => [
                'status' => true,
                'answers' => [1 => 'ya'],
            ]])
            ->post('/fatigue/store', [
                'questionnaire_status' => true,
                'reaction_time_ms' => 800, // > 500ms threshold
            ]);

        $this->assertDatabaseHas('fatigue_checks', [
            'user_id' => $this->petugas->id,
            'is_fit' => false, // Harus unfit karena reaction_time > 500ms
        ]);
    }

    /** @test */
    public function user_status_fit_is_updated_after_check()
    {
        $this->actingAs($this->petugas)
            ->withSession(['fatigue_questionnaire' => [
                'status' => true,
                'answers' => [1 => 'ya'],
            ]])
            ->post('/fatigue/store', [
                'questionnaire_status' => true,
                'reaction_time_ms' => 350,
            ]);

        $this->petugas->refresh();
        $this->assertTrue($this->petugas->status_fit);
    }
}
```

#### 3.4 Test Leaderboard Calculation
**Buat file:** `tests/Feature/LeaderboardTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaderboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** @test */
    public function leaderboard_orders_by_total_score_descending()
    {
        $quiz = Quiz::factory()->create(['is_daily_quiz' => true, 'is_active' => true]);
        $petugasRole = Role::where('name', 'Petugas')->first();

        $user1 = User::factory()->create(['role_id' => $petugasRole->id]);
        $user2 = User::factory()->create(['role_id' => $petugasRole->id]);

        // User 1: total 150 poin
        QuizAttempt::create(['user_id' => $user1->id, 'quiz_id' => $quiz->id, 'score' => 100, 'correct_answers' => 10, 'time_ms' => 30000, 'month_year' => date('Y-m')]);
        QuizAttempt::create(['user_id' => $user1->id, 'quiz_id' => $quiz->id, 'score' => 50, 'correct_answers' => 5, 'time_ms' => 30000, 'month_year' => date('Y-m')]);

        // User 2: total 200 poin
        QuizAttempt::create(['user_id' => $user2->id, 'quiz_id' => $quiz->id, 'score' => 200, 'correct_answers' => 20, 'time_ms' => 25000, 'month_year' => date('Y-m')]);

        $response = $this->actingAs($user1)->get('/quiz/leaderboard');
        $response->assertStatus(200);

        // Verifikasi user2 di atas user1 (skor lebih tinggi)
        $leaderboard = $response->original->getData()['page']['props']['leaderboard'];
        $this->assertEquals($user2->id, $leaderboard[0]['user_id']);
        $this->assertEquals($user1->id, $leaderboard[1]['user_id']);
    }

    /** @test */
    public function admin_daily_leaderboard_page_loads()
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', 'Admin')->first()->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/leaderboard/daily');
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_event_leaderboard_page_loads()
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', 'Admin')->first()->id,
        ]);

        $response = $this->actingAs($admin)->get('/admin/leaderboard/event');
        $response->assertStatus(200);
    }
}
```

#### 3.5 Pastikan Factory tersedia
Jika belum ada, buat factory untuk model yang dibutuhkan:

```bash
php artisan make:factory QuizFactory --model=Quiz
php artisan make:factory QuestionFactory --model=Question
php artisan make:factory AnswerFactory --model=Answer
php artisan make:factory FatigueQuestionFactory --model=FatigueQuestion
```

Isi masing-masing factory dengan data dummy yang valid:

**`database/factories/QuizFactory.php`:**
```php
public function definition(): array
{
    return [
        'title' => fake()->sentence(3),
        'theme' => fake()->word(),
        'duration_minutes' => fake()->numberBetween(5, 30),
        'is_active' => true,
        'is_daily_quiz' => true,
        'daily_question_limit' => 10,
    ];
}
```

**`database/factories/QuestionFactory.php`:**
```php
public function definition(): array
{
    return [
        'question' => fake()->sentence() . '?',
        'risk_level' => fake()->randomElement(['low', 'medium', 'high']),
    ];
}
```

**`database/factories/AnswerFactory.php`:**
```php
public function definition(): array
{
    return [
        'question_id' => Question::factory(),
        'answer' => fake()->sentence(3),
        'is_correct' => false,
    ];
}
```

**`database/factories/FatigueQuestionFactory.php`:**
```php
public function definition(): array
{
    return [
        'question' => fake()->sentence() . '?',
        'safe_answer' => 'ya',
        'is_active' => true,
    ];
}
```

### Verifikasi
Jalankan seluruh test suite:
```bash
php artisan test
```

Target: Semua test PASSED, coverage minimal untuk:
- [x] Admin access control (4 test)
- [x] Quiz flow (5 test)
- [x] Fatigue check flow (7 test)
- [x] Leaderboard (3 test)
- [x] Existing tests tetap pass (4 test)

Total target: **~23 test** (dari sebelumnya ~4 test)
