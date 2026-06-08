<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class QuestionTemplateExport implements FromArray, WithHeadings
{
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

    public function array(): array
    {
        return [];
    }
}
