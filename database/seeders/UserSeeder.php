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

        // Seed Petugas User
        User::create([
            'name' => 'Ahmad R2G',
            'nip' => '000001',
            'email' => 'petugas@r2g.com',
            'password' => Hash::make('password'),
            'role_id' => $petugasRole->id,
            'position' => 'Petugas Kepanduan Lapangan',
            'status_fit' => true,
        ]);
    }
}
