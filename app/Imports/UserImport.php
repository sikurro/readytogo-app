<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $adminRole = Role::where('name', 'Admin')->first();
        $petugasRole = Role::where('name', 'Petugas')->first();

        foreach ($rows as $row) {
            // Validate required columns
            if (empty($row['nama']) || empty($row['email']) || empty($row['nip'])) {
                continue;
            }

            // Check duplicate email or nip
            if (User::where('email', $row['email'])->orWhere('nip', $row['nip'])->exists()) {
                continue;
            }

            // Determine role
            $roleInput = isset($row['role']) ? trim($row['role']) : 'Petugas';
            $roleId = $petugasRole ? $petugasRole->id : 2; // Default fallback to 2
            if (strtolower($roleInput) === 'admin' && $adminRole) {
                $roleId = $adminRole->id;
            } elseif ($petugasRole) {
                $roleId = $petugasRole->id;
            }

            // Determine status fit
            $fitInput = isset($row['status_kebugaran']) ? strtolower(trim($row['status_kebugaran'])) : 'fit';
            $statusFit = ($fitInput === 'fit' || $fitInput === '1' || $fitInput === 'true');

            // Generate password
            $password = !empty($row['password']) ? trim($row['password']) : 'password123';

            User::create([
                'name' => $row['nama'],
                'nip' => $row['nip'],
                'email' => $row['email'],
                'password' => Hash::make($password),
                'role_id' => $roleId,
                'position' => $row['jabatan'] ?? null,
                'location' => $row['lokasi_unit_kerja'] ?? null,
                'status_fit' => $statusFit,
                'avatar' => null, // defaults to port-themed default in UI
            ]);
        }
    }
}
