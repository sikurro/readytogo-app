<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SimulationSeeder extends Seeder
{
    public function run(): void
    {
        // Bersihkan data attempts lama agar tidak menumpuk
        DB::table('quiz_attempts')->truncate();

        // Setup Kuis Harian Master
        $dailyQuiz = Quiz::where('is_daily_quiz', 1)->first();
        if (!$dailyQuiz) {
            $dailyQuiz = Quiz::create([
                'title' => 'Master Kuis Harian',
                'theme' => 'K3 Umum',
                'duration_minutes' => 10,
                'is_active' => true,
                'is_daily_quiz' => true,
                'daily_question_limit' => 5,
            ]);
        }

        // Setup 2 Event Quizzes
        $event1 = Quiz::where('title', 'Lomba Cerdas Cermat Bulan K3')->first() ?? Quiz::create([
            'title' => 'Lomba Cerdas Cermat Bulan K3',
            'theme' => 'Bulan K3 Nasional',
            'duration_minutes' => 30,
            'is_active' => true,
            'is_daily_quiz' => false,
        ]);

        $event2 = Quiz::where('title', 'Kuis Spesial Hari Buruh')->first() ?? Quiz::create([
            'title' => 'Kuis Spesial Hari Buruh',
            'theme' => 'Hak dan Keselamatan Pekerja',
            'duration_minutes' => 20,
            'is_active' => true,
            'is_daily_quiz' => false,
        ]);

        $petugasRole = \App\Models\Role::where('name', 'Petugas')->first();
        $location = \App\Models\Location::first();
        $currentPetugasCount = User::where('role_id', $petugasRole->id)->count();

        if ($currentPetugasCount < 18) {
            $needed = 18 - $currentPetugasCount;
            for ($i = 1; $i <= $needed; $i++) {
                User::create([
                    'name' => 'Petugas Simulasi ' . $i,
                    'email' => 'simulasi' . $i . uniqid() . '@r2g.com',
                    'nip' => 'SIM' . rand(10000, 99999),
                    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                    'role_id' => $petugasRole->id,
                    'location_id' => $location ? $location->id : null,
                    'position' => 'Staff K3',
                    'status_fit' => 1,
                ]);
            }
        }

        $users = User::where('role_id', $petugasRole->id)->take(18)->get();

        $startDate = Carbon::create(2026, 3, 1);
        $endDate = Carbon::now();

        // 1. Generate Daily Quiz Attempts (Mar 2026 - Today)
        $currentDate = $startDate->copy();
        
        $dailyAttemptsToInsert = [];
        while ($currentDate->lte($endDate)) {
            foreach ($users as $user) {
                // Randomly decide if user took the quiz today (e.g. 70% chance)
                if (rand(1, 100) <= 70) {
                    $correct = rand(2, 5); // Max 5 questions
                    $score = $correct * 20; // 20 pts per question
                    $timeMs = rand(15000, 120000); // 15s to 2mins
                    
                    $dailyAttemptsToInsert[] = [
                        'user_id' => $user->id,
                        'quiz_id' => $dailyQuiz->id,
                        'score' => $score,
                        'correct_answers' => $correct,
                        'time_ms' => $timeMs,
                        'month_year' => $currentDate->format('Y-m'),
                        'created_at' => $currentDate->copy()->setHour(rand(8, 17))->setMinute(rand(0, 59)),
                        'updated_at' => $currentDate->copy()->setHour(rand(8, 17))->setMinute(rand(0, 59)),
                    ];
                }
            }
            $currentDate->addDay();
        }

        // Chunk insert to avoid memory issues
        foreach (array_chunk($dailyAttemptsToInsert, 500) as $chunk) {
            QuizAttempt::insert($chunk);
        }

        // 2. Generate Event Quiz Attempts
        $eventAttemptsToInsert = [];
        
        // Event 1 Attempts (took place in April)
        $event1Date = Carbon::create(2026, 4, 15);
        foreach ($users as $user) {
            if (rand(1, 100) <= 90) { // 90% participation
                $correct = rand(10, 20); // assume 20 questions
                $score = $correct * 5;
                $timeMs = rand(300000, 900000); // 5 to 15 mins
                
                $eventAttemptsToInsert[] = [
                    'user_id' => $user->id,
                    'quiz_id' => $event1->id,
                    'score' => $score,
                    'correct_answers' => $correct,
                    'time_ms' => $timeMs,
                    'month_year' => $event1Date->format('Y-m'),
                    'created_at' => $event1Date->copy()->setHour(rand(9, 11))->setMinute(rand(0, 59)),
                    'updated_at' => $event1Date->copy()->setHour(rand(9, 11))->setMinute(rand(0, 59)),
                ];
            }
        }

        // Event 2 Attempts (took place in May)
        $event2Date = Carbon::create(2026, 5, 1);
        foreach ($users as $user) {
            if (rand(1, 100) <= 85) { // 85% participation
                $correct = rand(8, 15); // assume 15 questions
                $score = $correct * 6; // slightly different scoring for variation
                $timeMs = rand(200000, 600000); 
                
                $eventAttemptsToInsert[] = [
                    'user_id' => $user->id,
                    'quiz_id' => $event2->id,
                    'score' => $score,
                    'correct_answers' => $correct,
                    'time_ms' => $timeMs,
                    'month_year' => $event2Date->format('Y-m'),
                    'created_at' => $event2Date->copy()->setHour(rand(13, 16))->setMinute(rand(0, 59)),
                    'updated_at' => $event2Date->copy()->setHour(rand(13, 16))->setMinute(rand(0, 59)),
                ];
            }
        }

        QuizAttempt::insert($eventAttemptsToInsert);

        $this->command->info('Simulasi data kuis (harian dan event) berhasil di-generate.');
    }
}
