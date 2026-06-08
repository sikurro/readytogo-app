<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Question;
use App\Models\Category;
use App\Models\Answer;

class QuestionImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Validate required columns
            if (empty($row['pertanyaan']) || empty($row['jawaban_benar'])) {
                continue; // Skip invalid rows
            }

            // Normalize risk_level (e.g. low -> Low, HIGH -> High)
            $riskInput = isset($row['level_resiko']) ? trim($row['level_resiko']) : 'Low';
            $riskLevel = ucfirst(strtolower($riskInput));
            if (!in_array($riskLevel, ['Low', 'Medium', 'High'])) {
                $riskLevel = 'Low';
            }

            // Create question without quiz_id
            $question = Question::create([
                'question_text' => $row['pertanyaan'],
                'risk_level' => $riskLevel,
                'reference' => $row['referensi'] ?? null,
                'timer_seconds' => 30 // Default timer
            ]);

            // Handle categories (comma separated)
            if (!empty($row['kategori'])) {
                $categoryNames = array_map('trim', explode(',', $row['kategori']));
                $categoryIds = [];
                foreach ($categoryNames as $catName) {
                    $category = Category::firstOrCreate(['name' => $catName]);
                    $categoryIds[] = $category->id;
                }
                $question->categories()->sync($categoryIds);
            }

            // Create answers
            $options = [
                'A' => $row['jawaban_a'] ?? null,
                'B' => $row['jawaban_b'] ?? null,
                'C' => $row['jawaban_c'] ?? null,
                'D' => $row['jawaban_d'] ?? null,
            ];

            $correctLetter = strtoupper(trim($row['jawaban_benar']));

            foreach ($options as $letter => $answerText) {
                if (empty($answerText)) continue;
                
                Answer::create([
                    'question_id' => $question->id,
                    'answer_text' => $answerText,
                    'is_correct' => ($letter === $correctLetter)
                ]);
            }
        }
    }
}
