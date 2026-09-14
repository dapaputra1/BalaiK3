<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PetugasSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = Hash::make('Masuk54321_');

        $petugas = [
            ['email' => 'oktofa.sp@gmail.com', 'name' => 'Oktofa S. Pamungkas, S.T, M.Kes', 'role' => 'mp', 'nip' => '19791003 200912 1 002', 'jabatan' => 'PENGUJI K3 AHLI MADYA', 'golongan' => 'IVa'],
            ['email' => 'balaihiperkessurabaya@gmail.com', 'name' => 'Sugiyanto, S.H', 'role' => 'ma', 'nip' => '19831231 201503 1 004', 'jabatan' => 'PENELAAH TEKNIS KEBIJAKAN', 'golongan' => 'IIIc'],
            ['email' => 'oscarseptyan91@gmail.com', 'name' => 'Septyan Eko N., S.Kom', 'role' => 'admin', 'nip' => '19900916 202521 1 013', 'jabatan' => 'PENATA LAYANAN OPERASIONAL', 'golongan' => 'IX'],
            ['email' => 'ninind.ars@gmail.com', 'name' => 'Nindung Sutarsih, A.Md.', 'role' => 'analis', 'nip' => '19880616 202521 2 010', 'jabatan' => 'PENGELOLA LAYANAN OPERASIONAL', 'golongan' => 'VII'],
            ['email' => 'y.andaka.setyawan@gmail.com', 'name' => 'Yudha Andaka S., S.KM', 'role' => 'pcu', 'nip' => '19880510 202521 1 023', 'jabatan' => 'PENATA LAYANAN OPERASIONAL', 'golongan' => 'IX'],
            ['email' => 'dwi.online85@gmail.com', 'name' => 'Dwi Suhartanto, S.KM', 'role' => 'penyelia', 'nip' => '19851218 202521 1 010', 'jabatan' => 'PENATA LAYANAN OPERASIONAL', 'golongan' => 'IX'],
            ['email' => 'ratihhadiwijaya@gmail.com', 'name' => 'Sri Ratih Robiatul A., A.Md', 'role' => 'analis', 'nip' => '19841213 202521 2 008', 'jabatan' => 'PENGELOLA LAYANAN OPERASIONAL', 'golongan' => 'VII'],
            ['email' => 'mohamadsyafii257@gmail.com', 'name' => "Mohamad Syafi'i, S.E.", 'role' => 'pcu', 'nip' => '19820808 202521 1 022', 'jabatan' => 'PENATA LAYANAN OPERASIONAL', 'golongan' => 'IX'],
            ['email' => 'trisuharianto06@gmail.com', 'name' => 'Tri Suharianto', 'role' => 'admin', 'nip' => '197406282025211004', 'jabatan' => 'PENGADMITRASI PERKANTORAN', 'golongan' => 'V'],
            ['email' => 'yuliowahyu6@gmail.com', 'name' => 'Yulio Wahyu Haryatama', 'role' => 'admin', 'nip' => '19990711 202521 1 007', 'jabatan' => 'PENGADMINISTRASI PERKANTORAN', 'golongan' => 'V'],
            ['email' => 'irwinfarizky@gmail.com', 'name' => 'Irwin Farizky H., S.T.', 'role' => 'pcu', 'nip' => '19950624 202521 1 010', 'jabatan' => 'PENATA LAYANAN OPERASIONAL', 'golongan' => 'IX'],
            ['email' => 'dwirianti838@gmail.com', 'name' => 'Rizki Dwi Rianti, S.E.', 'role' => 'admin', 'nip' => '19940505 202521 2 027', 'jabatan' => 'PENATA LAYANAN OPERASIONAL', 'golongan' => 'IX'],
            ['email' => 'widyaaisyah93@gmail.com', 'name' => 'Widya Aisyah P., S.Farm', 'role' => 'admin', 'nip' => '19931228 202521 2 017', 'jabatan' => 'PENATA LAYANAN OPERASIONAL', 'golongan' => 'IX'],
            ['email' => 'azhar.taruna.at@gmail.com', 'name' => 'Azhar Kadar Taruna, S.KM', 'role' => 'pcu', 'nip' => '19930702 202521 1 010', 'jabatan' => 'PENATA LAYANAN OPERASIONAL', 'golongan' => 'IX'],
            ['email' => 'jatisekar645@gmail.com', 'name' => 'Sekar Jati Ningrum, S.Si', 'role' => 'qc', 'nip' => '19870526 202521 2 018', 'jabatan' => 'PENATA LAYANAN OPERASIONAL', 'golongan' => 'IX'],
            ['email' => 'denirak320@example.com', 'name' => 'Denira Fitri Lestari, S.T', 'role' => 'pcu', 'nip' => '19930820 201801 2 003', 'jabatan' => 'PENGUJI K3 AHLI PERTAMA', 'golongan' => 'IIIb'],
            ['email' => 'mokographa@yahoo.co.id', 'name' => 'Prihatmoko Saptinis Huboyojati, SKM,MKM', 'role' => 'penyelia', 'nip' => '197702032011011004', 'jabatan' => 'PENGUJI K3', 'golongan' => 'IIId'],
        ];

        foreach ($petugas as $item) {
            User::updateOrCreate(
                ['email' => $item['email']],
                [
                    'name' => $item['name'],
                    'password' => $defaultPassword,
                    'role' => $item['role'],
                    'nip' => $item['nip'] ?? null,
                    'jabatan' => $item['jabatan'] ?? null,
                    'golongan' => $item['golongan'] ?? null,
                    'is_active' => 1,
                ]
            );
        }
    }
}
