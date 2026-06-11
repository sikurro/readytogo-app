<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UserExport implements FromCollection, WithHeadings, WithMapping
{
    protected $roleId;
    protected $location;
    protected $search;

    public function __construct($roleId = null, $location = null, $search = null)
    {
        $this->roleId = $roleId;
        $this->location = $location;
        $this->search = $search;
    }

    public function collection()
    {
        $query = User::with('role');

        if ($this->roleId) {
            $query->where('role_id', $this->roleId);
        }

        if ($this->location) {
            $query->where('location', $this->location);
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
            $user->location,
            $user->role ? $user->role->name : '-',
            $user->status_fit ? 'Fit' : 'Unfit'
        ];
    }
}
