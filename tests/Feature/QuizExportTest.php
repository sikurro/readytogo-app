<?php

namespace Tests\Feature;

use App\Exports\QuizHistoryExport;
use App\Models\Role;
use App\Models\User;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class QuizExportTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $petugasUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $adminRole = Role::create(['name' => 'Admin']);
        $petugasRole = Role::create(['name' => 'Petugas']);

        // Create admin
        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->id,
        ]);

        // Create petugas
        $this->petugasUser = User::factory()->create([
            'role_id' => $petugasRole->id,
        ]);
    }

    public function test_non_admin_cannot_access_export(): void
    {
        $response = $this->actingAs($this->petugasUser)
            ->get(route('admin.quiz.history.export'));

        $response->assertStatus(403);
    }

    public function test_admin_can_export_quiz_history(): void
    {
        Excel::fake();
        $now = \Illuminate\Support\Carbon::create(2026, 6, 7, 10, 0, 0);
        \Illuminate\Support\Carbon::setTestNow($now);

        // Create a quiz
        $quiz = Quiz::create([
            'title' => 'Kuis Dummy',
            'theme' => 'Medis',
            'duration_minutes' => 15,
            'is_active' => true,
        ]);

        // Create a quiz attempt
        QuizAttempt::create([
            'user_id' => $this->petugasUser->id,
            'quiz_id' => $quiz->id,
            'score' => 85,
            'correct_answers' => 8,
            'time_ms' => 45000,
            'month_year' => '06-2026',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.quiz.history.export', [
                'search' => $this->petugasUser->name,
                'month' => '06-2026',
            ]));

        $response->assertStatus(200);

        $expectedFilename = 'riwayat-kuis-06-2026-' . $now->format('Ymd_His') . '.xlsx';
        Excel::assertDownloaded($expectedFilename, function (QuizHistoryExport $export) {
            return true;
        });

        // Clean up Carbon mock
        \Illuminate\Support\Carbon::setTestNow();
    }
}
