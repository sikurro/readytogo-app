<?php

namespace App\Exports;

use App\Models\Question;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class QuestionExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Question::with(['categories', 'answers'])->get();
    }

    public function headings(): array
    {
        return [
            'pertanyaan',
            'level_resiko',
            'referensi',
            'kategori',
            'jawaban_a',
            'jawaban_b',
            'jawaban_c',
            'jawaban_d',
            'jawaban_benar'
        ];
    }

    public function map($question): array
    {
        // Kategori (comma separated)
        $kategori = $question->categories->pluck('name')->implode(',');

        // Jawaban
        $jawabanA = '';
        $jawabanB = '';
        $jawabanC = '';
        $jawabanD = '';
        $jawabanBenar = '';

        $letters = ['A', 'B', 'C', 'D'];
        $answers = $question->answers->values();

        foreach ($answers as $index => $answer) {
            if ($index < 4) {
                $letter = $letters[$index];
                if ($letter === 'A') $jawabanA = $answer->answer_text;
                if ($letter === 'B') $jawabanB = $answer->answer_text;
                if ($letter === 'C') $jawabanC = $answer->answer_text;
                if ($letter === 'D') $jawabanD = $answer->answer_text;

                if ($answer->is_correct) {
                    $jawabanBenar = $letter;
                }
            }
        }

        return [
            $question->question_text,
            $question->risk_level,
            $question->reference,
            $kategori,
            $jawabanA,
            $jawabanB,
            $jawabanC,
            $jawabanD,
            $jawabanBenar
        ];
    }
}
