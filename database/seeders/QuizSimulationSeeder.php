<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class QuizSimulationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'Admin')->first();
        $petugasRole = Role::where('name', 'Petugas')->first();

        // 1. Pastikan Admin tetap ada
        if (!User::where('email', 'admin@r2g.com')->exists()) {
            User::create([
                'name' => 'Administrator K3',
                'nip' => '000000',
                'email' => 'admin@r2g.com',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'position' => 'Super Admin K3',
                'status_fit' => true,
            ]);
        }

        // 2. Bersihkan data quiz_attempts dan user non-admin
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        QuizAttempt::truncate();
        User::where('role_id', '!=', $adminRole->id)->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 3. Pastikan minimal ada 1 quiz
        $quiz = Quiz::first();
        if (!$quiz) {
            $this->call(QuizSeeder::class);
            $quiz = Quiz::first();
            // Truncate lagi setelah QuizSeeder dipanggil karena QuizSeeder membuat quiz attempts dummy
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            QuizAttempt::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // 4. Buat 10 Petugas dengan berbagai job title
        $names = [
            ['name' => 'Fahri Hidayat', 'position' => 'Supervisor'],
            ['name' => 'Gunawan Saputra', 'position' => 'Supervisor'],
            ['name' => 'Hendra Wijaya', 'position' => 'Officer'],
            ['name' => 'Indra Lesmana', 'position' => 'Officer'],
            ['name' => 'Joko Susilo', 'position' => 'Pandu'],
            ['name' => 'Kurniawan Adi', 'position' => 'Pandu'],
            ['name' => 'Lukman Hakim', 'position' => 'Pandu'],
            ['name' => 'Mulyadi Pratama', 'position' => 'Operator'],
            ['name' => 'Nugroho Dwi', 'position' => 'Operator'],
            ['name' => 'Oki Setiawan', 'position' => 'Operator'],
        ];

        $users = [];
        foreach ($names as $idx => $userData) {
            $users[] = User::create([
                'name' => $userData['name'],
                'nip' => '10000' . ($idx + 1),
                'email' => 'petugas' . ($idx + 1) . '@r2g.com',
                'password' => Hash::make('password'),
                'role_id' => $petugasRole->id,
                'position' => $userData['position'],
                'status_fit' => true,
            ]);
        }

        // 5. Generate data quiz attempts dari 1 Maret 2026 sampai 12 Juni 2026
        $startDate = Carbon::create(2026, 3, 1);
        $endDate = Carbon::create(2026, 6, 12);
        
        $totalQuestions = $quiz->questions()->count();
        if ($totalQuestions === 0) {
            $totalQuestions = 5;
        }

        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $monthYear = $currentDate->format('Y-m');

            foreach ($users as $user) {
                // Probabilitas 85% untuk mengerjakan kuis pada hari tersebut
                if (rand(1, 100) <= 85) {
                    $perfType = rand(1, 3); // 1 = Tinggi, 2 = Sedang, 3 = Rendah
                    
                    if ($perfType === 1) {
                        // Tinggi: 80% - 100% jawaban benar
                        $correctAnswers = rand(ceil($totalQuestions * 0.8), $totalQuestions);
                    } elseif ($perfType === 2) {
                        // Sedang: 50% - 79% jawaban benar
                        $correctAnswers = rand(ceil($totalQuestions * 0.5), ceil($totalQuestions * 0.79));
                    } else {
                        // Rendah: 0% - 49% jawaban benar
                        $correctAnswers = rand(0, ceil($totalQuestions * 0.49));
                    }

                    // Score proporsional (0-100)
                    $score = round(($correctAnswers / $totalQuestions) * 100);

                    // Buat attempt record dengan custom timestamp
                    QuizAttempt::create([
                        'user_id' => $user->id,
                        'quiz_id' => $quiz->id,
                        'score' => $score,
                        'correct_answers' => $correctAnswers,
                        'time_ms' => rand(20000, 180000), // Antara 20 detik sampai 3 menit
                        'month_year' => $monthYear,
                        'created_at' => $currentDate->copy()->hour(rand(8, 17))->minute(rand(0, 59))->second(rand(0, 59)),
                        'updated_at' => $currentDate->copy()->hour(rand(8, 17))->minute(rand(0, 59))->second(rand(0, 59)),
                    ]);
                }
            }

            $currentDate->addDay();
        }
    }
}
