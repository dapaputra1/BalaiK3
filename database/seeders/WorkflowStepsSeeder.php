<?php

namespace Database\Seeders;

use App\Models\WorkflowStep;
use Illuminate\Database\Seeder;

class WorkflowStepsSeeder extends Seeder
{
    public function run(): void
    {
        $steps = [
            ['kode' => 'disposisi', 'nama' => 'Disposisi', 'urutan' => 1],
            ['kode' => 'kaji_ulang', 'nama' => 'Kaji Ulang', 'urutan' => 2],
            ['kode' => 'penawaran', 'nama' => 'Penawaran', 'urutan' => 3],
            ['kode' => 'penjadwalan', 'nama' => 'Penjadwalan', 'urutan' => 4],
            ['kode' => 'dokumen_spt', 'nama' => 'Dokumen SPT', 'urutan' => 5],
            ['kode' => 'pengujian', 'nama' => 'Pengujian', 'urutan' => 6],
            ['kode' => 'alur_bap', 'nama' => 'Alur BAP', 'urutan' => 7],
            ['kode' => 'verifikasi_pengujian', 'nama' => 'Verifikasi Pengujian', 'urutan' => 8],
            ['kode' => 'verifikasi_pcu', 'nama' => 'Verifikasi PCU', 'urutan' => 9],
            ['kode' => 'koding', 'nama' => 'Koding', 'urutan' => 10],
            ['kode' => 'preparasi_analisa', 'nama' => 'Preparasi Analisa', 'urutan' => 11],
            ['kode' => 'verifikasi', 'nama' => 'Verifikasi Hasil Analisa', 'urutan' => 12],
            ['kode' => 'pembuatan_lhu', 'nama' => 'Draft LHU', 'urutan' => 13],
            ['kode' => 'qc_lhu', 'nama' => 'QC LHU', 'urutan' => 14],
            ['kode' => 'ttd_lhu', 'nama' => 'Penandatanganan LHU', 'urutan' => 15],
            ['kode' => 'surat_tagihan', 'nama' => 'Surat Tagihan', 'urutan' => 16],
            ['kode' => 'kode_billing', 'nama' => 'Kode Billing', 'urutan' => 17],
            ['kode' => 'invoice', 'nama' => 'Kuitansi', 'urutan' => 18],
            ['kode' => 'penerbitan_suket', 'nama' => 'Penerbitan Suket', 'urutan' => 19],
            ['kode' => 'penyerahan_lhu', 'nama' => 'Penyerahan LHU', 'urutan' => 20],
        ];

        foreach ($steps as $step) {
            WorkflowStep::updateOrCreate(
                ['kode' => $step['kode']],
                ['nama' => $step['nama'], 'urutan' => $step['urutan']]
            );
        }
    }
}
