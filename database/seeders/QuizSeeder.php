<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Answer;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $quiz = Quiz::create([
            'title' => 'Kuis Harian: Alat Keselamatan Diri',
            'theme' => 'K3 Pelabuhan & APAR',
            'duration_minutes' => 5,
            'is_active' => true,
        ]);

        // Soal 1: Teks saja, Pilihan jawaban teks saja.
        $q1 = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Apa kepanjangan dari APAR?',
            'question_image' => null,
            'timer_seconds' => 15,
        ]);
        Answer::create(['question_id' => $q1->id, 'answer_text' => 'Alat Pemadam Api Ringan', 'is_correct' => true]);
        Answer::create(['question_id' => $q1->id, 'answer_text' => 'Alat Pengaman Area Rawan', 'is_correct' => false]);
        Answer::create(['question_id' => $q1->id, 'answer_text' => 'Alat Pelindung Anggota Raga', 'is_correct' => false]);
        Answer::create(['question_id' => $q1->id, 'answer_text' => 'Alat Penyelamat Air Ringan', 'is_correct' => false]);

        // Soal 2: Teks + Gambar, Pilihan jawaban teks saja.
        $q2 = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Berdasarkan gambar rambu di atas, apa arti dari rambu tersebut?',
            'question_image' => 'https://dummyimage.com/600x400/000/fff&text=Rambu+K3', // Dummy image URL
            'timer_seconds' => 15,
        ]);
        Answer::create(['question_id' => $q2->id, 'answer_text' => 'Wajib Menggunakan Helm Keselamatan', 'is_correct' => true]);
        Answer::create(['question_id' => $q2->id, 'answer_text' => 'Dilarang Masuk Area Ini', 'is_correct' => false]);
        Answer::create(['question_id' => $q2->id, 'answer_text' => 'Bahaya Tegangan Tinggi', 'is_correct' => false]);
        Answer::create(['question_id' => $q2->id, 'answer_text' => 'Area Titik Kumpul', 'is_correct' => false]);

        // Soal 3: Teks saja, Pilihan jawaban berupa Gambar saja.
        $q3 = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Manakah dari gambar berikut yang merupakan sepatu safety (safety shoes)?',
            'question_image' => null,
            'timer_seconds' => 15,
        ]);
        Answer::create(['question_id' => $q3->id, 'answer_text' => null, 'answer_image' => 'https://dummyimage.com/150x150/000/fff&text=Sepatu+Safety', 'is_correct' => true]);
        Answer::create(['question_id' => $q3->id, 'answer_text' => null, 'answer_image' => 'https://dummyimage.com/150x150/000/fff&text=Sepatu+Kets', 'is_correct' => false]);
        Answer::create(['question_id' => $q3->id, 'answer_text' => null, 'answer_image' => 'https://dummyimage.com/150x150/000/fff&text=Sandal+Jepit', 'is_correct' => false]);
        Answer::create(['question_id' => $q3->id, 'answer_text' => null, 'answer_image' => 'https://dummyimage.com/150x150/000/fff&text=Sepatu+Pantofel', 'is_correct' => false]);

        // Soal 4: Teks saja, Pilihan jawaban kombinasi Teks + Gambar.
        $q4 = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Pilih APAR yang tepat untuk memadamkan kebakaran akibat korsleting listrik.',
            'question_image' => null,
            'timer_seconds' => 15,
        ]);
        Answer::create(['question_id' => $q4->id, 'answer_text' => 'APAR CO2', 'answer_image' => 'https://dummyimage.com/100x100/e74c3c/fff&text=CO2', 'is_correct' => true]);
        Answer::create(['question_id' => $q4->id, 'answer_text' => 'APAR Air (Water)', 'answer_image' => 'https://dummyimage.com/100x100/3498db/fff&text=Air', 'is_correct' => false]);
        Answer::create(['question_id' => $q4->id, 'answer_text' => 'APAR Busa (Foam)', 'answer_image' => 'https://dummyimage.com/100x100/f1c40f/fff&text=Busa', 'is_correct' => false]);
        Answer::create(['question_id' => $q4->id, 'answer_text' => 'Air Keran', 'answer_image' => 'https://dummyimage.com/100x100/95a5a6/fff&text=Keran', 'is_correct' => false]);

        // Soal 5: Teks + Gambar, Pilihan jawaban kombinasi Teks + Gambar.
        $q5 = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Perhatikan gambar petugas ini, perlengkapan K3 apa yang kurang saat bekerja di ketinggian?',
            'question_image' => 'https://dummyimage.com/600x400/000/fff&text=Pekerja+Tanpa+Harness',
            'timer_seconds' => 15,
        ]);
        Answer::create(['question_id' => $q5->id, 'answer_text' => 'Full Body Harness', 'answer_image' => 'https://dummyimage.com/100x100/2ecc71/fff&text=Harness', 'is_correct' => true]);
        Answer::create(['question_id' => $q5->id, 'answer_text' => 'Helm Keselamatan', 'answer_image' => 'https://dummyimage.com/100x100/f39c12/fff&text=Helm', 'is_correct' => false]);
        Answer::create(['question_id' => $q5->id, 'answer_text' => 'Kacamata Safety', 'answer_image' => 'https://dummyimage.com/100x100/8e44ad/fff&text=Kacamata', 'is_correct' => false]);
        Answer::create(['question_id' => $q5->id, 'answer_text' => 'Sarung Tangan', 'answer_image' => 'https://dummyimage.com/100x100/d35400/fff&text=Sarung+Tangan', 'is_correct' => false]);

        // Seed Quiz Attempts for other officers to populate Leaderboard
        $users = \App\Models\User::where('email', '!=', 'admin@r2g.com')
            ->where('email', '!=', 'petugas@r2g.com')
            ->get();

        $scores = [550, 480, 390, 600];
        $correctAnswers = [4, 3, 2, 5];
        $times = [28000, 32000, 45000, 25000];
        $monthYear = now()->format('Y-m');

        foreach ($users as $index => $user) {
            \App\Models\QuizAttempt::create([
                'user_id' => $user->id,
                'quiz_id' => $quiz->id,
                'score' => $scores[$index % 4],
                'correct_answers' => $correctAnswers[$index % 4],
                'time_ms' => $times[$index % 4],
                'month_year' => $monthYear,
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ]);
        }
    }
}
