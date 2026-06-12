<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeaderboardExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $data;
    private int $rowNumber = 0;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama User',
            'NIP',
            'Jabatan',
            'Lokasi/Unit Kerja',
            'Total Skor',
            'Total Soal Dijawab',
            'Jawaban Benar',
            'Jawaban Salah',
            'Persentase Jawaban Benar (%)',
        ];
    }

    public function map($row): array
    {
        $this->rowNumber++;
        return [
            $this->rowNumber,
            $row['name'],
            $row['nip'] ?: '-',
            $row['position'] ?: '-',
            $row['location'],
            $row['total_score'],
            $row['total_questions'],
            $row['total_correct'],
            $row['total_wrong'],
            $row['percentage_correct'] . '%',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
