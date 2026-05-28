<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun Admin
        User::create([
            'name'     => 'Admin FILKOM',
            'nim'      => 'ADMIN001',
            'email'    => 'admin@filkom.ub.ac.id',
            'password' => Hash::make('admin123'),
            'role'     => 'admin',
            'angkatan' => '2020',
            'fakultas' => 'Ilmu Komputer',
            'jurusan'  => 'Teknik Informatika',
            'phone'    => '08123456789',
        ]);

        // Akun Mahasiswa contoh
        User::create([
            'name'     => 'Mahasiswa Test',
            'nim'      => '245150207111001',
            'email'    => 'mahasiswa@ub.ac.id',
            'password' => Hash::make('password'),
            'role'     => 'mahasiswa',
            'angkatan' => '2024',
            'fakultas' => 'Ilmu Komputer',
            'jurusan'  => 'Teknik Informatika',
            'phone'    => '08987654321',
        ]);
    }
}