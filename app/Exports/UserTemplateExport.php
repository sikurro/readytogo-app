<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UserTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'nama',
            'nip',
            'email',
            'password',
            'jabatan',
            'lokasi_unit_kerja',
            'role',
            'status_kebugaran'
        ];
    }

    public function array(): array
    {
        return [
            [
                'Budi Santoso',
                '198501012010121001',
                'budi.santoso@pelabuhan.co.id',
                'password123',
                'Supervisor Lapangan',
                'Cabang Samarinda',
                'Petugas',
                'Fit'
            ],
            [
                'Siti Aminah',
                '199203152015042002',
                'siti.aminah@pelabuhan.co.id',
                'password123',
                'Admin K3',
                'Kantor Pusat',
                'Admin',
                'Fit'
            ]
        ];
    }
}
