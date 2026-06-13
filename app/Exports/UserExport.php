<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UserExport implements FromCollection, WithHeadings, WithMapping
{
    protected $roleId;
    protected $locationId;
    protected $search;

    public function __construct($roleId = null, $locationId = null, $search = null)
    {
        $this->roleId = $roleId;
        $this->locationId = $locationId;
        $this->search = $search;
    }

    public function collection()
    {
        $query = User::with(['role', 'location']);

        if ($this->roleId) {
            $query->where('role_id', $this->roleId);
        }

        if ($this->locationId) {
            $query->where('location_id', $this->locationId);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('nip', 'like', '%' . $this->search . '%')
                  ->orWhere('position', 'like', '%' . $this->search . '%');
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'nama',
            'nip',
            'email',
            'jabatan',
            'lokasi_unit_kerja',
            'role',
            'status_kebugaran'
        ];
    }

    public function map($user): array
    {
        return [
            $user->name,
            $user->nip,
            $user->email,
            $user->position,
            $user->location ? $user->location->name : '-',
            $user->role ? $user->role->name : '-',
            $user->status_fit ? 'Fit' : 'Unfit'
        ];
    }
}
