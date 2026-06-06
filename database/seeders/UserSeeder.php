<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'Admin')->first();
        $petugasRole = Role::where('name', 'Petugas')->first();

        // Seed Admin User
        User::create([
            'name' => 'Administrator K3',
            'nip' => '000000',
            'email' => 'admin@r2g.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'position' => 'Super Admin K3',
            'status_fit' => true,
        ]);

        // Seed Petugas User 1
        User::create([
            'name' => 'Ahmad R2G',
            'nip' => '000001',
            'email' => 'petugas@r2g.com',
            'password' => Hash::make('password'),
            'role_id' => $petugasRole->id,
            'position' => 'Petugas Kepanduan Lapangan',
            'status_fit' => true,
        ]);

        // Seed Petugas User 2
        User::create([
            'name' => 'Budi R2G',
            'nip' => '000002',
            'email' => 'petugas2@r2g.com',
            'password' => Hash::make('password'),
            'role_id' => $petugasRole->id,
            'position' => 'Petugas Patroli Dermaga',
            'status_fit' => true,
        ]);

        // Seed Petugas User 3
        User::create([
            'name' => 'Charlie R2G',
            'nip' => '000003',
            'email' => 'petugas3@r2g.com',
            'password' => Hash::make('password'),
            'role_id' => $petugasRole->id,
            'position' => 'Operator Crane',
            'status_fit' => true,
        ]);

        // Seed Petugas User 4
        User::create([
            'name' => 'Dedi R2G',
            'nip' => '000004',
            'email' => 'petugas4@r2g.com',
            'password' => Hash::make('password'),
            'role_id' => $petugasRole->id,
            'position' => 'Pengawas Lapangan K3',
            'status_fit' => true,
        ]);

        // Seed Petugas User 5
        User::create([
            'name' => 'Eko R2G',
            'nip' => '000005',
            'email' => 'petugas5@r2g.com',
            'password' => Hash::make('password'),
            'role_id' => $petugasRole->id,
            'position' => 'Petugas Tambat Kapal',
            'status_fit' => true,
        ]);
    }
}
