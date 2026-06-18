<?php

namespace App\Exports;

use App\Models\Incident;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class IncidentExport implements FromQuery, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        // Panggil relasi user dan juga location-nya (jika ada) untuk export yang komprehensif
        $query = Incident::with(['user.location'])->latest();

        $status = $this->filters['status'] ?? null;
        $category = $this->filters['category'] ?? null;
        $severity = $this->filters['severity'] ?? null;

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        if ($category !== null && $category !== '') {
            $query->where('category', $category);
        }

        if ($severity !== null && $severity !== '') {
            $query->where('severity', $severity);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID Laporan',
            'Tanggal Dilaporkan',
            'Nama Pelapor',
            'NIP',
            'Lokasi / Unit',
            'Kategori',
            'Severity',
            'Status',
            'Deskripsi Bahaya/Insiden',
            'Latitude',
            'Longitude',
            'Diselesaikan Pada',
            'Tindakan/Feedback Admin',
        ];
    }

    public function map($row): array
    {
        $categoryLabel = $row->category;
        switch ($row->category) {
            case 'unsafe_condition': $categoryLabel = 'Kondisi Tidak Aman'; break;
            case 'unsafe_act': $categoryLabel = 'Tindakan Tidak Aman'; break;
            case 'near_miss': $categoryLabel = 'Hampir Celaka'; break;
            case 'positive_observation': $categoryLabel = 'Observasi Positif'; break;
        }

        return [
            $row->id,
            $row->created_at ? $row->created_at->format('Y-m-d H:i:s') : '-',
            $row->user->name ?? '-',
            $row->user->nip ?? '-',
            $row->user->location->name ?? '-',
            $categoryLabel,
            strtoupper($row->severity),
            strtoupper($row->status),
            $row->description ?? '-',
            $row->latitude ?? '-',
            $row->longitude ?? '-',
            $row->resolved_at ? \Carbon\Carbon::parse($row->resolved_at)->format('Y-m-d H:i:s') : '-',
            $row->admin_feedback ?? '-',
        ];
    }
}
