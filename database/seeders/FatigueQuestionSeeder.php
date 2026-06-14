<?php

namespace Database\Seeders;

use App\Models\FatigueQuestion;
use Illuminate\Database\Seeder;

class FatigueQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $questions = [
            'Apakah Anda tidur minimal 6 jam semalam?',
            'Apakah Anda sedang mengonsumsi obat yang menyebabkan kantuk?',
            'Apakah kondisi badan Anda merasa sehat/fit hari ini?'
        ];

        foreach ($questions as $questionText) {
            FatigueQuestion::updateOrCreate(
                ['question_text' => $questionText],
                ['is_active' => true]
            );
        }
    }
}
