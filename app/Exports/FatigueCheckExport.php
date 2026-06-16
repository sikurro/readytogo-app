<?php

namespace App\Exports;

use App\Models\FatigueCheck;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FatigueCheckExport implements FromQuery, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $search = $this->filters['search'] ?? null;
        $status = $this->filters['status'] ?? null;
        $date = $this->filters['date'] ?? null;

        $query = FatigueCheck::with(['user.location'])->latest();

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('is_fit', $status === 'fit');
        }

        if ($date) {
            if (strlen($date) === 7) { // Format: YYYY-MM
                $query->whereYear('created_at', substr($date, 0, 4))
                      ->whereMonth('created_at', substr($date, 5, 2));
            } else {
                $query->whereDate('created_at', $date);
            }
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Tanggal Pengecekan',
            'Nama',
            'NIP',
            'Email',
            'Lokasi',
            'Status',
            'Waktu Reaksi (ms)',
        ];
    }

    public function map($row): array
    {
        return [
            $row->created_at->format('Y-m-d H:i:s'),
            $row->user->name ?? '-',
            $row->user->nip ?? '-',
            $row->user->email ?? '-',
            $row->user->location->name ?? '-',
            $row->is_fit ? 'Fit' : 'Unfit',
            $row->reaction_time_ms ?? '-',
        ];
    }
}
