<?php

namespace Database\Seeders;

use App\Models\Jurusan;
use App\Models\Mahasiswa;
use App\Models\Pengajar;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $jurusan = Jurusan::create([
            'kode_jurusan' => 'TI',
            'nama_jurusan' => 'Teknik Informatika',
        ]);

        $pengajar = Pengajar::create([
            'nama_pengajar' => 'Trainer Contoh',
            'no_hp_pengajar' => '081234567890',
        ]);

        $mahasiswa = Mahasiswa::create([
            'nrp' => '20260001',
            'nama' => 'Mahasiswa Contoh',
            'alamat' => 'Jl. Contoh No. 1',
            'semester' => 1,
            'kode_jurusan' => $jurusan->kode_jurusan,
            'id_pengajar' => $pengajar->id_pengajar,
            'no_telp' => '081234567891',
        ]);

        User::create([
            'name' => 'Atasan Admin',
            'email' => 'atasan@kampus.com',
            'password' => 'password',
            'role' => 'atasan_admin',
            'status_akun' => 'aktif',
        ]);

        User::create([
            'name' => 'Admin',
            'email' => 'admin@kampus.com',
            'password' => 'password',
            'role' => 'admin',
            'status_akun' => 'aktif',
        ]);

        User::create([
            'name' => 'Trainer Contoh',
            'email' => 'trainer@kampus.com',
            'password' => 'password',
            'role' => 'trainer',
            'status_akun' => 'aktif',
            'id_pengajar' => $pengajar->id_pengajar,
        ]);

        User::create([
            'name' => 'Mahasiswa Contoh',
            'email' => 'user@kampus.com',
            'password' => 'password',
            'role' => 'user',
            'status_akun' => 'aktif',
            'id_mahasiswa' => $mahasiswa->id,
        ]);
    }
}
