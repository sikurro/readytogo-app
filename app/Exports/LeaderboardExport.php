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
    protected $isDaily;
    private int $rowNumber = 0;

    public function __construct($data, $isDaily = true)
    {
        $this->data = $data;
        $this->isDaily = $isDaily;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        $headings = [
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

        if ($this->isDaily) {
            $headings[] = 'Jumlah Kehadiran (Hari)';
        }

        return $headings;
    }

    public function map($row): array
    {
        $this->rowNumber++;
        $mapped = [
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

        if ($this->isDaily) {
            $mapped[] = $row['attendance'] ?? 0;
        }

        return $mapped;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
