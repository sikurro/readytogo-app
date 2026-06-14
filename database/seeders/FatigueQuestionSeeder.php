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
            [
                'question_text' => 'Apakah Anda tidur minimal 6 jam semalam?',
                'safe_answer' => true
            ],
            [
                'question_text' => 'Apakah Anda sedang mengonsumsi obat yang menyebabkan kantuk?',
                'safe_answer' => false
            ],
            [
                'question_text' => 'Apakah kondisi badan Anda merasa sehat/fit hari ini?',
                'safe_answer' => true
            ]
        ];

        foreach ($questions as $qData) {
            FatigueQuestion::updateOrCreate(
                ['question_text' => $qData['question_text']],
                [
                    'is_active' => true,
                    'safe_answer' => $qData['safe_answer']
                ]
            );
        }
    }
}
