<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\Style\Table;

class ErgoDocxService
{
    /**
     * Menghasilkan dokumen DOCX Laporan Hasil Uji (LHU) Faktor Ergonomi resmi Balai K3 Surabaya
     * sesuai format baku SNI 9011:2021.
     */
    public function generateDocx($assessment, $gotrakAssessments = null, $photos = null): string
    {
        $errorLevel = error_reporting(E_ALL & ~E_DEPRECATED);
        $prevXmlErrors = libxml_use_internal_errors(true);

        try {
            $phpWord = new PhpWord();
            $phpWord->setDefaultFontName('Times New Roman');
            $phpWord->setDefaultFontSize(10);

            // Metadata Dokumen
            $properties = $phpWord->getDocInfo();
            $properties->setCreator('Balai Keselamatan dan Kesehatan Kerja Surabaya');
            $properties->setTitle('Laporan Hasil Uji Ergonomi - ' . ($assessment->company_name ?? 'Balai K3'));
            $properties->setSubject('LHU Pengujian Faktor Ergonomi SNI 9011:2021');

            // Set Margin Halaman A4
            $section = $phpWord->addSection([
                'paperSize'    => 'A4',
                'marginTop'    => Converter::cmToTwip(1.4),
                'marginBottom' => Converter::cmToTwip(1.6),
                'marginLeft'   => Converter::cmToTwip(2.0),
                'marginRight'  => Converter::cmToTwip(1.8),
            ]);

            // Ambil berkas foto dokumentasi jika ada
            if ($photos === null) {
                try {
                    $rawPhotos = DB::table('ergo_assessment_photos')
                        ->where('assessment_id', $assessment->id)
                        ->get();
                } catch (\Throwable $e) {
                    $rawPhotos = collect();
                }
            } else {
                $rawPhotos = collect($photos);
            }

            // Ambil gotrak jika null
            if ($gotrakAssessments === null) {
                try {
                    $gotrakAssessments = DB::table('ergo_gotrak_assessments')
                        ->where('assessment_id', $assessment->id)
                        ->get();
                } catch (\Throwable $e) {
                    $gotrakAssessments = collect();
                }
            } else {
                $gotrakAssessments = collect($gotrakAssessments);
            }

            // Total Halaman Est: 2 Halaman (atau 3 jika ada foto/checklist)
            $totalEstPages = (count($rawPhotos) > 0) ? 3 : 2;

            // =========================================================================
            // 1. KOP SURAT RESMI KEMNAKER / BALAI K3 SURABAYA (HALAMAN 1)
            // =========================================================================
            $headerTable = $section->addTable([
                'alignment'  => JcTable::CENTER,
                'layout'     => Table::LAYOUT_FIXED,
                'unit'       => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width'      => Converter::cmToTwip(17.2),
                'cellMargin' => 0,
                'borderSize' => 0,
            ]);
            $headerTable->addRow();

            // Logo Kementerian Ketenagakerjaan
            $logoCell = $headerTable->addCell(Converter::cmToTwip(2.2), [
                'unit'  => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width' => Converter::cmToTwip(2.2),
            ]);
            $basePath = (function_exists('app') && app()->has('path.public')) ? app('path.public') : (dirname(__DIR__, 2) . '/public');
            $logoCandidates = [
                $basePath . '/images/Logo Kemnaker.png',
                $basePath . '/images/Logo.png',
            ];
            foreach ($logoCandidates as $candidate) {
                if (file_exists($candidate) && is_readable($candidate)) {
                    $logoCell->addImage($candidate, [
                        'width'     => 55,
                        'height'    => 55,
                        'alignment' => Jc::CENTER,
                    ]);
                    break;
                }
            }

            // Teks Instansi Kop Surat
            $textCell = $headerTable->addCell(Converter::cmToTwip(15.0), [
                'unit'  => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width' => Converter::cmToTwip(15.0),
            ]);
            $textCell->addText('KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA', ['bold' => true, 'size' => 10.5, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 15]);
            $textCell->addText('DIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN', ['bold' => true, 'size' => 9.5, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 15]);
            $textCell->addText('DAN KESELAMATAN DAN KESEHATAN KERJA', ['bold' => true, 'size' => 9.5, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 15]);
            $textCell->addText('BALAI HIGIENE PERUSAHAAN KESEHATAN DAN KESELAMATAN KERJA SURABAYA', ['bold' => true, 'size' => 11, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 15]);
            $textCell->addText('Jl. Dukuh Menanggal No. 122, Dukuh Menanggal, Kec. Gayungan, Kota SBY, Jawa Timur 60234, Laman: balaik3surabaya@kemnaker.go.id', ['italic' => true, 'size' => 7.5, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);

            // Garis Pembatas Kop Ganda
            $divTable = $section->addTable([
                'alignment'  => JcTable::CENTER,
                'layout'     => Table::LAYOUT_FIXED,
                'unit'       => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width'      => Converter::cmToTwip(17.2),
                'cellMargin' => 0,
            ]);
            $divRow = $divTable->addRow(Converter::pointToTwip(2));
            $divCell = $divRow->addCell(Converter::cmToTwip(17.2), [
                'unit'              => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width'             => Converter::cmToTwip(17.2),
                'borderBottomSize'  => 18,
                'borderBottomColor' => '000000',
                'borderBottomStyle' => 'double',
            ]);
            $divCell->addText('', [], ['spaceBefore' => 0, 'spaceAfter' => 0]);

            $section->addTextBreak(1, ['size' => 4]);

            // =========================================================================
            // 2. JUDUL LAPORAN HASIL
            // =========================================================================
            $section->addText('LAPORAN HASIL', ['bold' => true, 'underline' => 'single', 'size' => 12, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 15]);
            $section->addText('Pengujian Faktor Ergonomi di Tempat Kerja', ['bold' => true, 'size' => 10, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 15]);
            $docNumber = $assessment->lhu_doc_number ?? ('No. LAB. 0032/VII/' . date('Y', strtotime($assessment->assessment_date)));
            $section->addText($this->xmlSafe($docNumber), ['size' => 9.5, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 100]);

            // =========================================================================
            // 3. BUTIR 1: DATA UMUM
            // =========================================================================
            $section->addText('1. DATA UMUM', ['bold' => true, 'size' => 9.5, 'name' => 'Times New Roman'], ['spaceBefore' => 40, 'spaceAfter' => 20]);
            
            $metaTable = $section->addTable([
                'alignment'  => JcTable::CENTER,
                'layout'     => Table::LAYOUT_FIXED,
                'unit'       => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width'      => Converter::cmToTwip(17.2),
                'cellMargin' => 10,
                'borderSize' => 0,
            ]);
            $prevDocNo = 'LAB. ' . str_pad((string) max(1, $assessment->id - 1), 4, '0', STR_PAD_LEFT) . '/VII/' . (date('Y', strtotime($assessment->assessment_date)) - 1);
            $this->addKeyValueRow($metaTable, 'a.', 'Perusahaan', $assessment->company_name ?? '-', true);
            $this->addKeyValueRow($metaTable, 'b.', 'Alamat', $assessment->company_address ?? '-');
            $this->addKeyValueRow($metaTable, 'c.', 'Pengurus / Penanggung Jawab', $assessment->company_pic ?? 'Mohammad Nurul Huda');
            $this->addKeyValueRow($metaTable, 'd.', 'Nomor Dokumen Pengujian Sebelumnya', $prevDocNo);

            // =========================================================================
            // 4. BUTIR 2: PENGUKURAN ERGONOMI
            // =========================================================================
            $section->addText('2. PENGUKURAN ERGONOMI', ['bold' => true, 'size' => 9.5, 'name' => 'Times New Roman'], ['spaceBefore' => 50, 'spaceAfter' => 20]);
            
            $ergoMetaTable = $section->addTable([
                'alignment'  => JcTable::CENTER,
                'layout'     => Table::LAYOUT_FIXED,
                'unit'       => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width'      => Converter::cmToTwip(17.2),
                'cellMargin' => 10,
                'borderSize' => 0,
            ]);
            $tglUji = Carbon::parse($assessment->assessment_date)->locale('id')->translatedFormat('d F Y');
            $this->addKeyValueRow($ergoMetaTable, 'a.', 'Tanggal Pengukuran', $tglUji);
            $this->addKeyValueRow($ergoMetaTable, 'b.', 'Jumlah Departemen', '1');
            $this->addKeyValueRow($ergoMetaTable, 'c.', 'Jumlah Pekerjaan', '1');

            // =========================================================================
            // 5. BUTIR 3: METODE PENGUKURAN
            // =========================================================================
            $section->addText('3. METODE PENGUKURAN YANG DIPAKAI', ['bold' => true, 'size' => 9.5, 'name' => 'Times New Roman'], ['spaceBefore' => 50, 'spaceAfter' => 20]);
            $section->addText(
                'SNI 9011:2021 tentang Pengukuran dan evaluasi potensi bahaya ergonomi di tempat kerja',
                ['size' => 9.5, 'name' => 'Times New Roman'],
                ['spaceAfter' => 80]
            );

            // =========================================================================
            // 6. BUTIR 4: REKAPITULASI HASIL PENGUKURAN
            // =========================================================================
            $section->addText('4. HASIL PENGUKURAN ERGONOMI', ['bold' => true, 'size' => 9.5, 'name' => 'Times New Roman'], ['spaceBefore' => 50, 'spaceAfter' => 30]);

            $rekapTable = $section->addTable([
                'alignment'       => JcTable::CENTER,
                'layout'          => Table::LAYOUT_FIXED,
                'unit'            => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width'           => Converter::cmToTwip(17.2),
                'borderSize'      => 6,
                'borderColor'     => '000000',
                'cellMarginTop'   => 40,
                'cellMarginBottom'=> 40,
                'cellMarginLeft'  => 40,
                'cellMarginRight' => 40,
            ]);

            // Header Baris 1
            $r1 = $rekapTable->addRow();
            $r1->addCell(Converter::cmToTwip(0.9), ['vMerge' => 'restart'])->addText('No.', ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER]);
            $r1->addCell(Converter::cmToTwip(2.8), ['vMerge' => 'restart'])->addText("Departemen/\nBagian/\nRuangan", ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER]);
            $r1->addCell(Converter::cmToTwip(2.6), ['vMerge' => 'restart'])->addText("Jenis\nPekerjaan", ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER]);
            $r1->addCell(Converter::cmToTwip(4.2), ['gridSpan' => 3])->addText("Hasil Penilaian Potensi\nBahaya (Skor)", ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER]);
            $r1->addCell(Converter::cmToTwip(1.4), ['vMerge' => 'restart'])->addText("Total\nSkor", ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER]);
            $r1->addCell(Converter::cmToTwip(2.7), ['vMerge' => 'restart'])->addText("Interpretasi\nHasil", ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER]);
            $r1->addCell(Converter::cmToTwip(2.6), ['vMerge' => 'restart'])->addText("Metode\nPengendalian\nYang Ada", ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER]);

            // Header Baris 2
            $r2 = $rekapTable->addRow();
            $r2->addCell(Converter::cmToTwip(0.9), ['vMerge' => 'continue']);
            $r2->addCell(Converter::cmToTwip(2.8), ['vMerge' => 'continue']);
            $r2->addCell(Converter::cmToTwip(2.6), ['vMerge' => 'continue']);
            $r2->addCell(Converter::cmToTwip(1.4))->addText("Bagian\nAtas", ['bold' => true, 'size' => 7.5], ['alignment' => Jc::CENTER]);
            $r2->addCell(Converter::cmToTwip(1.5))->addText("Punggung\n& Bawah", ['bold' => true, 'size' => 7.5], ['alignment' => Jc::CENTER]);
            $r2->addCell(Converter::cmToTwip(1.3))->addText("Angkat\nManual", ['bold' => true, 'size' => 7.5], ['alignment' => Jc::CENTER]);
            $r2->addCell(Converter::cmToTwip(1.4), ['vMerge' => 'continue']);
            $r2->addCell(Converter::cmToTwip(2.7), ['vMerge' => 'continue']);
            $r2->addCell(Converter::cmToTwip(2.6), ['vMerge' => 'continue']);

            // Data Baris
            $totalScore = (float) ($assessment->total_score ?? ($assessment->final_score ?? 0));
            if ($totalScore <= 2) {
                $interpretasi = 'Kondisi tempat kerja aman';
            } elseif ($totalScore <= 6) {
                $interpretasi = 'Kondisi tempat kerja perlu pengamatan lebih lanjut';
            } else {
                $interpretasi = 'Kondisi tempat kerja berbahaya';
            }

            $rData = $rekapTable->addRow();
            $rData->addCell(Converter::cmToTwip(0.9))->addText('1.', ['size' => 8], ['alignment' => Jc::CENTER]);
            $rData->addCell(Converter::cmToTwip(2.8))->addText($this->xmlSafe($assessment->worker_name ?? '-'), ['bold' => true, 'size' => 8]);
            $rData->addCell(Converter::cmToTwip(2.6))->addText($this->xmlSafe($assessment->position ?? '-'), ['size' => 8]);
            $rData->addCell(Converter::cmToTwip(1.4))->addText((string) ($assessment->upper_body_score ?? 0), ['size' => 8], ['alignment' => Jc::CENTER]);
            $rData->addCell(Converter::cmToTwip(1.5))->addText((string) ($assessment->lower_body_score ?? 0), ['size' => 8], ['alignment' => Jc::CENTER]);
            $rData->addCell(Converter::cmToTwip(1.3))->addText((string) ($assessment->mmh_score ?? 0), ['size' => 8], ['alignment' => Jc::CENTER]);
            $rData->addCell(Converter::cmToTwip(1.4))->addText((string) $totalScore, ['bold' => true, 'size' => 8.5], ['alignment' => Jc::CENTER]);
            $rData->addCell(Converter::cmToTwip(2.7))->addText($interpretasi, ['size' => 8]);
            $rData->addCell(Converter::cmToTwip(2.6))->addText($this->xmlSafe($assessment->existing_control ?? 'Adanya waktu istirahat/peregangan'), ['size' => 7.5]);

            // Catatan Kriteria ERFC
            $section->addTextBreak(1, ['size' => 4]);
            $section->addText('Catatan:', ['bold' => true, 'size' => 8, 'name' => 'Times New Roman'], ['spaceAfter' => 10]);
            $section->addText('Penilaian dari metode pengukuran ERFC atau Daftar Periksa Potensi Bahaya Faktor Ergonomi adalah sebagai berikut:', ['size' => 8, 'name' => 'Times New Roman'], ['spaceAfter' => 10]);
            $section->addText('a. Nilai <= 2, maka kondisi tempat kerja aman', ['size' => 8, 'name' => 'Times New Roman'], ['spaceAfter' => 10, 'indentation' => ['left' => Converter::cmToTwip(0.4)]]);
            $section->addText('b. Nilai 3 - 6, maka kondisi tempat kerja perlu pengamatan lebih lanjut', ['size' => 8, 'name' => 'Times New Roman'], ['spaceAfter' => 10, 'indentation' => ['left' => Converter::cmToTwip(0.4)]]);
            $section->addText('c. Nilai >= 7, maka kondisi tempat kerja berbahaya', ['size' => 8, 'name' => 'Times New Roman'], ['spaceAfter' => 30, 'indentation' => ['left' => Converter::cmToTwip(0.4)]]);

            // Footer Baku Halaman 1
            $this->addStandardFooter($section);

            // =========================================================================
            // 7. HALAMAN 2: ANALISIS, KESIMPULAN & REKOMENDASI PERBAIKAN
            // =========================================================================
            $section->addPageBreak();

            // Header Berulang ISO Balai K3 Halaman 2
            $this->addHeaderRepeatBox($section, 2, $totalEstPages);

            // 5. ANALISIS
            $section->addText('5. ANALISIS :', ['bold' => true, 'size' => 9.5, 'name' => 'Times New Roman'], ['spaceBefore' => 40, 'spaceAfter' => 20]);
            if (!empty($assessment->lhu_analysis)) {
                $lines = explode("\n", str_replace("\r", "", $assessment->lhu_analysis));
                foreach ($lines as $line) {
                    $section->addText($this->xmlSafe(trim($line)), ['size' => 9, 'name' => 'Times New Roman'], ['alignment' => Jc::BOTH, 'spaceAfter' => 20]);
                }
            } else {
                $section->addText(
                    'a. Hasil penilaian potensi bahaya ergonomi ' . ($assessment->worker_name ?? '-') . ' (' . ($assessment->position ?? '-') . ') tubuh bagian atas yang berpotensi bahaya adalah :',
                    ['size' => 9, 'name' => 'Times New Roman'],
                    ['alignment' => Jc::BOTH, 'spaceAfter' => 15]
                );
                $section->addText('- Leher menekuk ke depan > 20° atau ke belakang < 5°', ['size' => 9], ['indentation' => ['left' => Converter::cmToTwip(0.6)], 'spaceAfter' => 10]);
                $section->addText('- Bahu : Lengan atau siku yang tidak ditopang, dengan posisi di atas tinggi perut', ['size' => 9], ['indentation' => ['left' => Converter::cmToTwip(0.6)], 'spaceAfter' => 10]);
                $section->addText('- Pergelangan tangan : Menekuk ke depan atau kesamping', ['size' => 9], ['indentation' => ['left' => Converter::cmToTwip(0.6)], 'spaceAfter' => 15]);
                $gotrakSentence = !empty($assessment->gotrak_summary_narrative)
                    ? $assessment->gotrak_summary_narrative
                    : 'Dari hasil wawancara menggunakan formulir keluhan Gangguan Otot Rangka Akibat Kerja didapatkan keluhan tidak nyaman pada leher dan punggung bawah dengan frekuensi terkadang.';
                $section->addText(
                    'Dari hasil wawancara menggunakan formulir keluhan Gangguan Otot Rangka Akibat Kerja didapatkan keluhan tidak nyaman pada leher dan punggung bawah dengan frekuensi terkadang.',
                    $this->xmlSafe($gotrakSentence),
                    ['size' => 9, 'name' => 'Times New Roman'],
                    ['alignment' => Jc::BOTH, 'spaceAfter' => 15]
                );
                $section->addText(
                    'b. Hasil penilaian potensi bahaya ergonomi bagian bawah yang berpotensi bahaya adalah :',
                    ['size' => 9, 'name' => 'Times New Roman'],
                    ['alignment' => Jc::BOTH, 'spaceAfter' => 15]
                );
                $section->addText('- Duduk dalam waktu yang lama tanpa sandaran atau penopang punggung yang memadai', ['size' => 9], ['indentation' => ['left' => Converter::cmToTwip(0.6)], 'spaceAfter' => 10]);
                $section->addText('- Tubuh membungkuk ke depan dengan sudut antara 20 hingga 45 derajat', ['size' => 9], ['indentation' => ['left' => Converter::cmToTwip(0.6)], 'spaceAfter' => 30]);
            }

            // 6. KESIMPULAN
            $section->addText('6. KESIMPULAN', ['bold' => true, 'size' => 9.5, 'name' => 'Times New Roman'], ['spaceBefore' => 40, 'spaceAfter' => 20]);
            if (!empty($assessment->lhu_conclusion)) {
                $lines = explode("\n", str_replace("\r", "", $assessment->lhu_conclusion));
                foreach ($lines as $line) {
                    $section->addText($this->xmlSafe(trim($line)), ['size' => 9, 'name' => 'Times New Roman'], ['alignment' => Jc::BOTH, 'spaceAfter' => 20]);
                }
            } else {
                $kesimpulanDefault = 'Penilaian potensi bahaya ergonomi pada ' . ($assessment->worker_name ?? '-') . ' (' . ($assessment->position ?? '-') . ') ';
                if ($totalScore <= 2) {
                    $kesimpulanDefault .= 'termasuk dalam kondisi tempat kerja aman.';
                } elseif ($totalScore <= 6) {
                    $kesimpulanDefault .= 'dalam kondisi tempat kerja perlu pengamatan lebih lanjut.';
                } else {
                    $kesimpulanDefault .= 'dalam kondisi tempat kerja berbahaya dan memerlukan tindakan perbaikan segera.';
                }
                $section->addText($this->xmlSafe($kesimpulanDefault), ['size' => 9, 'name' => 'Times New Roman'], ['alignment' => Jc::BOTH, 'spaceAfter' => 40]);
            }

            // 7. SARAN DAN TINDAKAN PERBAIKAN
            $section->addText('7. SARAN DAN TINDAKAN PERBAIKAN', ['bold' => true, 'size' => 9.5, 'name' => 'Times New Roman'], ['spaceBefore' => 40, 'spaceAfter' => 20]);
            if (!empty($assessment->lhu_recommendation)) {
                $lines = explode("\n", str_replace("\r", "", $assessment->lhu_recommendation));
                foreach ($lines as $line) {
                    $section->addText($this->xmlSafe(trim($line)), ['size' => 9, 'name' => 'Times New Roman'], ['alignment' => Jc::BOTH, 'spaceAfter' => 20]);
                }
            } else {
                $section->addText(
                    'Secara umum terdapat 2 postur kerja yaitu postur kerja dinamis dan statis. Postur kerja statis teridentifikasi pada pekerja yang bekerja di perkantoran (menyusun laporan, memverifikasi laporan), sedangkan postur kerja dinamis teridentifikasi pada pekerja lapangan/operasional teknis.',
                    ['size' => 9, 'name' => 'Times New Roman'],
                    ['alignment' => Jc::BOTH, 'spaceAfter' => 20]
                );

                $section->addText('A. Jenis Pekerjaan Perkantoran', ['bold' => true, 'size' => 9], ['spaceBefore' => 20, 'spaceAfter' => 15]);
                $section->addText(
                    'Tenaga kerja yang mempunyai aktivitas kerja berupa administrasi perkantoran dengan kegiatan mengoperasikan komputer, saran dan tindakan yang dapat dilakukan adalah sebagai berikut:',
                    ['size' => 9],
                    ['alignment' => Jc::BOTH, 'spaceAfter' => 15]
                );
                $this->addBulletPoint($section, 'a.', 'Apabila kepala menunduk saat bekerja dengan layar monitor, maka angkat/turunkan tinggi monitor agar mata sejajar dengan bagian atas layar, dan atur dokumen lain agar tingginya sejajar monitor.');
                $this->addBulletPoint($section, 'b.', 'Apabila kepala tidak sejajar dengan tulang belakang, atur stasiun kerja agar memungkinkan postur duduk bersandar yang ergonomis dan letakkan keyboard di dekat pengguna.');
                $this->addBulletPoint($section, 'c.', 'Apabila meraih ke samping atau ke depan saat menggunakan mouse, letakkan mouse di samping keyboard dengan tinggi yang sejajar dan gunakan bantalan alas pergelangan tangan (mouse pad).');
                $this->addBulletPoint($section, 'd.', 'Terapkan metode 20-20-20 (setiap 20 menit bekerja, alihkan pandangan sejauh 6 meter selama 20 detik) serta istirahat sejenak 5-10 menit tiap 1-2 jam untuk peregangan otot.');

                $section->addText('B. Jenis Pekerjaan Dinamis & Lapangan', ['bold' => true, 'size' => 9], ['spaceBefore' => 20, 'spaceAfter' => 15]);
                $this->addBulletPoint($section, 'a.', 'Hindari pekerjaan yang dilakukan dengan posisi membungkuk dengan menyediakan meja/landasan kerja yang sesuai dengan tinggi siku pekerja.');
                $this->addBulletPoint($section, 'b.', 'Pekerjaan yang memerlukan ketelitian sebaiknya dilaksanakan pada sudut pandang setinggi dada dengan menjaga posisi siku tetap dekat dengan tubuh.');
                $this->addBulletPoint($section, 'c.', 'Hindari posisi memuntir torso (batang tubuh) saat memindahkan atau memeriksa benda kerja.');

                $section->addText('C. Pengangkatan Beban Manual (MMH)', ['bold' => true, 'size' => 9], ['spaceBefore' => 20, 'spaceAfter' => 15]);
                $this->addBulletPoint($section, 'a.', 'Gunakan alat bantu angkut (hand truck, trolley, hoist) untuk meniadakan pengangkatan beban berlebih secara manual.');
                $this->addBulletPoint($section, 'b.', 'Terapkan teknik pengangkatan jongkok dengan bertumpu pada otot paha/kaki, bukan pada tulang belakang/punggung.');
            }

            // KLAUSUL CATATAN RESMI & TANDA TANGAN (Table 2 Kolom)
            $section->addTextBreak(1, ['size' => 4]);
            $signTable = $section->addTable([
                'alignment'  => JcTable::CENTER,
                'layout'     => Table::LAYOUT_FIXED,
                'unit'       => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width'      => Converter::cmToTwip(17.2),
                'cellMargin' => 0,
                'borderSize' => 0,
            ]);
            $signRow = $signTable->addRow();

            // Kolom Kiri: Catatan Klausul ISO
            $clauseCell = $signRow->addCell(Converter::cmToTwip(9.5), [
                'unit'  => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width' => Converter::cmToTwip(9.5),
            ]);
            $clauseCell->addText('Catatan:', ['bold' => true, 'size' => 7.5, 'name' => 'Times New Roman'], ['spaceAfter' => 5]);
            $clauseCell->addText('1. Data uji di atas hanya berlaku untuk contoh yang diuji.', ['size' => 7], ['spaceAfter' => 3]);
            $clauseCell->addText('2. Laporan Hasil Uji ini tidak boleh digandakan, kecuali secara lengkap dan seijin tertulis dari Balai Hiperkes dan KK Surabaya.', ['size' => 7], ['spaceAfter' => 3]);
            $clauseCell->addText('3. Laboratorium melayani pengaduan maksimum 1 minggu sejak tanggal penyerahan LHU.', ['size' => 7], ['spaceAfter' => 3]);
            $clauseCell->addText('4. Laboratorium menyerahkan rekaman teknis bila diminta oleh pelanggan secara tertulis.', ['size' => 7], ['spaceAfter' => 0]);

            // Kolom Kanan: Tanda Tangan Manajer Teknis
            $signCell = $signRow->addCell(Converter::cmToTwip(7.7), [
                'unit'  => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width' => Converter::cmToTwip(7.7),
            ]);
            $signDate = Carbon::parse($assessment->assessment_date)->locale('id')->translatedFormat('d F Y');
            $signCell->addText('Surabaya, ' . $signDate, ['size' => 9, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 10]);
            $signCell->addText($this->xmlSafe($assessment->signer_position ?? 'Manajer Teknis') . ',', ['bold' => true, 'size' => 9, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 60]);
            
            // Ruang Tanda Tangan
            $signCell->addTextBreak(2, ['size' => 12]);

            $signerName = $assessment->signer_name ?? 'OKTOFA S. PAMUNGKAS S.T., M.Kes';
            $signerNip = $assessment->signer_nip ?? '19791003 200912 1 002';
            $signCell->addText($this->xmlSafe($signerName), ['bold' => true, 'underline' => 'single', 'size' => 9, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 5]);
            $signCell->addText('NIP. ' . $this->xmlSafe($signerNip), ['size' => 8.5, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);

            // Footer Baku Halaman 2
            $this->addStandardFooter($section);

            // =========================================================================
            // 8. HALAMAN 3: LAMPIRAN FOTO & CHECKLIST (JIKA ADA FOTO)
            // =========================================================================
            if (count($rawPhotos) > 0) {
                $section->addPageBreak();

                // Header Berulang ISO Balai K3 Halaman 3
                $this->addHeaderRepeatBox($section, 3, $totalEstPages);

                $section->addText('Lampiran 1 :', ['bold' => true, 'size' => 9, 'name' => 'Times New Roman'], ['spaceBefore' => 20, 'spaceAfter' => 10]);

                // Box Profil Pekerja Lampiran
                $lampiranBox = $section->addTable([
                    'alignment'       => JcTable::CENTER,
                    'layout'          => Table::LAYOUT_FIXED,
                    'unit'            => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                    'width'           => Converter::cmToTwip(17.2),
                    'borderSize'      => 6,
                    'borderColor'     => '000000',
                    'cellMarginTop'   => 40,
                    'cellMarginBottom'=> 40,
                    'cellMarginLeft'  => 60,
                    'cellMarginRight' => 60,
                ]);
                $lRowHeader = $lampiranBox->addRow();
                $lCellH = $lRowHeader->addCell(Converter::cmToTwip(17.2), ['bgColor' => 'F5F5F5']);
                $lCellH->addText('1. ' . $this->xmlSafe($assessment->worker_name ?? '-') . ', ' . $this->xmlSafe($assessment->position ?? '-') . ' (Masa Kerja: ' . ($assessment->work_duration_level ?? '3 tahun') . ')', ['bold' => true, 'size' => 9], ['spaceAfter' => 0]);

                $lRowBody = $lampiranBox->addRow();
                $lCellB = $lRowBody->addCell(Converter::cmToTwip(17.2));
                $lCellB->addText('Deskripsi Pekerjaan:', ['bold' => true, 'size' => 8.5], ['spaceAfter' => 5]);
                $jobDescText = ($assessment->job_tasks ?? 'Aktivitas pengujian dan operasional rutin harian.') . ' ' . ($assessment->job_duration ? $assessment->job_duration . '. ' : '') . 'Durasi kerja per shift adalah ' . ($assessment->shift_hours ?? 8) . ' jam/hari. Tangan dominan: ' . ($assessment->dominant_hand ?? 'Kanan') . '.';
                $lCellB->addText($this->xmlSafe($jobDescText), ['size' => 8.5], ['spaceAfter' => 0]);

                // Tabel Hasil Evaluasi Keluhan GOTRAK (SNI 9011:2021)
                if ($gotrakAssessments && count($gotrakAssessments) > 0) {
                    $activeGotrak = $gotrakAssessments->filter(function($item) {
                        return (int)$item->score > 1;
                    });

                    $section->addText('Hasil Evaluasi Keluhan Otot Rangka (GOTRAK / SNI 9011:2021):', ['bold' => true, 'size' => 8.5, 'name' => 'Times New Roman'], ['spaceBefore' => 30, 'spaceAfter' => 10, 'alignment' => Jc::CENTER]);

                    $gTable = $section->addTable([
                        'alignment'        => JcTable::CENTER,
                        'layout'           => Table::LAYOUT_FIXED,
                        'unit'             => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                        'width'            => Converter::cmToTwip(17.2),
                        'borderSize'       => 6,
                        'borderColor'      => '000000',
                        'cellMarginTop'    => 20,
                        'cellMarginBottom' => 20,
                        'cellMarginLeft'   => 30,
                        'cellMarginRight'  => 30,
                    ]);

                    $gHead = $gTable->addRow();
                    $gHead->addCell(Converter::cmToTwip(0.9))->addText('No.', ['bold' => true, 'size' => 7.5], ['alignment' => Jc::CENTER]);
                    $gHead->addCell(Converter::cmToTwip(3.2))->addText('Bagian Tubuh', ['bold' => true, 'size' => 7.5], ['alignment' => Jc::CENTER]);
                    $gHead->addCell(Converter::cmToTwip(1.5))->addText('Sisi', ['bold' => true, 'size' => 7.5], ['alignment' => Jc::CENTER]);
                    $gHead->addCell(Converter::cmToTwip(2.8))->addText('Frekuensi', ['bold' => true, 'size' => 7.5], ['alignment' => Jc::CENTER]);
                    $gHead->addCell(Converter::cmToTwip(2.5))->addText('Keparahan', ['bold' => true, 'size' => 7.5], ['alignment' => Jc::CENTER]);
                    $gHead->addCell(Converter::cmToTwip(1.1))->addText('Skor', ['bold' => true, 'size' => 7.5], ['alignment' => Jc::CENTER]);
                    $gHead->addCell(Converter::cmToTwip(2.2))->addText('Kategori Risiko', ['bold' => true, 'size' => 7.5], ['alignment' => Jc::CENTER]);
                    $gHead->addCell(Converter::cmToTwip(3.0))->addText('Pekerjaan Penyebab', ['bold' => true, 'size' => 7.5], ['alignment' => Jc::CENTER]);

                    $fLabels = [1 => 'Tidak pernah', 2 => 'Terkadang (1-3x/th)', 3 => 'Sering (1-3x/bln)', 4 => 'Selalu (tiap hari)'];
                    $sLabels = [1 => 'Tidak ada masalah', 2 => 'Tidak nyaman', 3 => 'Sakit', 4 => 'Sakit parah'];

                    if ($activeGotrak->count() > 0) {
                        $no = 1;
                        foreach ($activeGotrak as $g) {
                            $gRow = $gTable->addRow();
                            $gRow->addCell(Converter::cmToTwip(0.9))->addText((string)$no++, ['size' => 7.5], ['alignment' => Jc::CENTER]);
                            $gRow->addCell(Converter::cmToTwip(3.2))->addText($this->xmlSafe($g->body_part_name ?? '-'), ['bold' => true, 'size' => 7.5]);
                            $gRow->addCell(Converter::cmToTwip(1.5))->addText($this->xmlSafe($g->side ?? '-'), ['size' => 7.5], ['alignment' => Jc::CENTER]);
                            $gRow->addCell(Converter::cmToTwip(2.8))->addText($this->xmlSafe($fLabels[$g->frequency] ?? (string)$g->frequency), ['size' => 7.5]);
                            $gRow->addCell(Converter::cmToTwip(2.5))->addText($this->xmlSafe($sLabels[$g->severity] ?? (string)$g->severity), ['size' => 7.5]);
                            $gRow->addCell(Converter::cmToTwip(1.1))->addText((string)$g->score, ['bold' => true, 'size' => 7.5], ['alignment' => Jc::CENTER]);
                            
                            $riskColor = ($g->risk_category === 'Risiko Rendah') ? '065F46' : (($g->risk_category === 'Risiko Sedang') ? '92400E' : '991B1B');
                            $gRow->addCell(Converter::cmToTwip(2.2))->addText($this->xmlSafe($g->risk_category ?? '-'), ['bold' => true, 'size' => 7.5, 'color' => $riskColor], ['alignment' => Jc::CENTER]);
                            $gRow->addCell(Converter::cmToTwip(3.0))->addText($this->xmlSafe($g->cause_description ?? '-'), ['size' => 7.5]);
                        }
                    } else {
                        $emptyRow = $gTable->addRow();
                        $emptyRow->addCell(Converter::cmToTwip(17.2), ['gridSpan' => 8])->addText('Tidak terdapat keluhan gangguan otot rangka kerja yang dilaporkan (Semua skor keluhan = 1, Risiko Rendah).', ['italic' => true, 'size' => 7.5], ['alignment' => Jc::CENTER]);
                    }
                }

                // Lampiran Foto Dokumentasi Teranotasi Sudut
                $section->addText('Hasil Rekaman Foto/Video Dokumentasi:', ['bold' => true, 'size' => 8.5, 'name' => 'Times New Roman'], ['spaceBefore' => 40, 'spaceAfter' => 15, 'alignment' => Jc::CENTER]);

                $photoTable = $section->addTable([
                    'alignment'       => JcTable::CENTER,
                    'layout'          => Table::LAYOUT_FIXED,
                    'unit'            => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                    'width'           => Converter::cmToTwip(17.2),
                    'borderSize'      => 6,
                    'borderColor'     => '888888',
                    'cellMarginTop'   => 30,
                    'cellMarginBottom'=> 30,
                    'cellMarginLeft'  => 30,
                    'cellMarginRight' => 30,
                ]);

                // Tambahkan foto per baris (maks 2 per baris agar rapi)
                $photoChunks = $rawPhotos->chunk(2);
                foreach ($photoChunks as $chunk) {
                    $pRow = $photoTable->addRow();
                    $cellWidthCm = 17.2 / count($chunk);
                    foreach ($chunk as $p) {
                        $pCell = $pRow->addCell(Converter::cmToTwip($cellWidthCm));
                        $filePath = storage_path('app/public/' . $p->file_path);
                        if (file_exists($filePath)) {
                            $pCell->addImage($filePath, [
                                'width'     => 220,
                                'height'    => 160,
                                'alignment' => Jc::CENTER,
                            ]);
                        } else {
                            $pCell->addText('[Foto tidak ditemukan: ' . $this->xmlSafe($p->photo_name) . ']', ['size' => 8, 'italic' => true], ['alignment' => Jc::CENTER]);
                        }
                    }
                }

                // Rincian Butir Checklist SNI 9011:2021
                $section->addText('Rincian Daftar Periksa Potensi Bahaya Faktor Ergonomi (SNI 9011:2021):', ['bold' => true, 'size' => 8.5, 'name' => 'Times New Roman'], ['spaceBefore' => 40, 'spaceAfter' => 15, 'alignment' => Jc::CENTER]);

                $checkTable = $section->addTable([
                    'alignment'       => JcTable::CENTER,
                    'layout'          => Table::LAYOUT_FIXED,
                    'unit'            => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                    'width'           => Converter::cmToTwip(17.2),
                    'borderSize'      => 6,
                    'borderColor'     => '000000',
                    'cellMarginTop'   => 25,
                    'cellMarginBottom'=> 25,
                    'cellMarginLeft'  => 40,
                    'cellMarginRight' => 40,
                ]);
                $cHead = $checkTable->addRow();
                $cHead->addCell(Converter::cmToTwip(12.2))->addText('Potensi Bahaya', ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER]);
                $cHead->addCell(Converter::cmToTwip(3.2))->addText('Durasi Paparan', ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER]);
                $cHead->addCell(Converter::cmToTwip(1.8))->addText('Skor', ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER]);

                // Group 1
                $g1 = $checkTable->addRow();
                $g1->addCell(Converter::cmToTwip(17.2), ['gridSpan' => 3, 'bgColor' => 'F5F5F5'])->addText('Hasil Penilaian Potensi Bahaya Tubuh Bagian Atas', ['bold' => true, 'size' => 8]);

                $this->addChecklistRow($checkTable, 'Leher menekuk ke depan > 20° atau ke belakang < 5°', '25–50%', '1');
                $this->addChecklistRow($checkTable, 'Bahu : Lengan atau siku yang tidak ditopang, di atas tinggi perut', '25–50%', (string) min(2, max(1, $assessment->upper_body_score ?? 1)));
                $this->addChecklistRow($checkTable, 'Pergelangan tangan : Menekuk ke depan atau ke samping', '0–25%', '1');
                $this->addChecklistRow($checkTable, 'Rotasi lengan bawah secara cepat (mengocok / repetitif)', '0–25%', '0');
                
                $tot1 = $checkTable->addRow();
                $tot1->addCell(Converter::cmToTwip(15.4), ['gridSpan' => 2])->addText('Total Tubuh Bagian Atas', ['bold' => true, 'size' => 8], ['alignment' => Jc::END]);
                $tot1->addCell(Converter::cmToTwip(1.8))->addText((string) ($assessment->upper_body_score ?? 0), ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER]);

                // Group 2
                $g2 = $checkTable->addRow();
                $g2->addCell(Converter::cmToTwip(17.2), ['gridSpan' => 3, 'bgColor' => 'F5F5F5'])->addText('Hasil Penilaian Potensi Bahaya Punggung & Tubuh Bagian Bawah', ['bold' => true, 'size' => 8]);

                $this->addChecklistRow($checkTable, 'Tubuh membungkuk ke depan antara 20° hingga 45°', '0–25%', '1');
                $this->addChecklistRow($checkTable, 'Duduk dalam waktu yang lama tanpa sandaran memadai', '25–50%', (string) min(2, max(0, ($assessment->lower_body_score ?? 1) - 1)));
                $this->addChecklistRow($checkTable, 'Bekerja dengan berdiri diam dalam waktu lama', '25–50%', '0');

                $tot2 = $checkTable->addRow();
                $tot2->addCell(Converter::cmToTwip(15.4), ['gridSpan' => 2])->addText('Total Punggung & Tubuh Bawah', ['bold' => true, 'size' => 8], ['alignment' => Jc::END]);
                $tot2->addCell(Converter::cmToTwip(1.8))->addText((string) ($assessment->lower_body_score ?? 0), ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER]);

                // Group 3
                $g3 = $checkTable->addRow();
                $g3->addCell(Converter::cmToTwip(17.2), ['gridSpan' => 3, 'bgColor' => 'F5F5F5'])->addText('Hasil Periksa Pengangkatan Beban Secara Manual (MMH - SNI 9011:2021)', ['bold' => true, 'size' => 8]);

                $mmhStep2Val = (string) ($assessment->mmh_step2_score ?? ($assessment->mmh_score ?? 0));
                $this->addChecklistRow($checkTable, '32 & 33. Berat beban dan jarak angkut/bawa (Langkah ke-2)', 'Terpapar', $mmhStep2Val);

                $mmhTitles = [
                    34 => 'Batang tubuh memuntir saat mengangkat',
                    35 => 'Mengangkat dengan satu tangan',
                    36 => 'Mengangkat beban tidak terduga/tidak diprediksi',
                    37 => 'Mengangkat 1-5 kali per menit',
                    38 => 'Mengangkat lebih dari 5 kali per menit',
                    39 => 'Posisi benda yang diangkat di atas bahu',
                    40 => 'Posisi benda yang diangkat di bawah siku',
                    41 => 'Membawa benda dengan jarak 3-9 meter',
                    42 => 'Membawa benda dengan jarak > 9 meter',
                    43 => 'Mengangkat saat duduk / bertumpu pada lutut',
                ];

                $step3Items = [];
                if (!empty($assessment->mmh_step3_items)) {
                    $step3Items = is_array($assessment->mmh_step3_items) ? $assessment->mmh_step3_items : json_decode($assessment->mmh_step3_items, true);
                }

                $hasStep3Exposures = false;
                if (!empty($step3Items)) {
                    foreach ($step3Items as $itNo => $itScore) {
                        if ((float)$itScore > 0) {
                            $hasStep3Exposures = true;
                            $freqText = (((int)$itScore >= 2 && in_array((int)$itNo, [35,36,39,40,41,43])) || ((int)$itScore >= 3 && in_array((int)$itNo, [38,42]))) ? 'Sering' : 'Sesekali';
                            $this->addChecklistRow($checkTable, $itNo . '. ' . ($mmhTitles[$itNo] ?? 'Faktor risiko langkah ke-3'), $freqText, (string)$itScore);
                        }
                    }
                }

                if (!$hasStep3Exposures && empty($assessment->mmh_step3_score)) {
                    $this->addChecklistRow($checkTable, '34 - 43. Faktor risiko tambahan (Langkah ke-3)', 'Tidak Terpapar', '0');
                }

                $tot3 = $checkTable->addRow();
                $tot3->addCell(Converter::cmToTwip(15.4), ['gridSpan' => 2])->addText('Total Pengangkatan Beban Manual (MMH)', ['bold' => true, 'size' => 8], ['alignment' => Jc::END]);
                $tot3->addCell(Converter::cmToTwip(1.8))->addText((string) ($assessment->mmh_total_score ?? ($assessment->mmh_score ?? 0)), ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER]);

                $totFinal = $checkTable->addRow();
                $totFinal->addCell(Converter::cmToTwip(15.4), ['gridSpan' => 2, 'bgColor' => 'EBEBEB'])->addText('TOTAL HASIL PENILAIAN POTENSI BAHAYA ERGONOMI', ['bold' => true, 'size' => 8.5], ['alignment' => Jc::END]);
                $totFinal->addCell(Converter::cmToTwip(1.8), ['bgColor' => 'EBEBEB'])->addText((string) $totalScore, ['bold' => true, 'size' => 9], ['alignment' => Jc::CENTER]);

                // Footer Baku Halaman 3
                $this->addStandardFooter($section);
            }

            // Simpan ke berkas sementara lalu baca biner DOCX
            $tempFile = tempnam(sys_get_temp_dir(), 'lhu_ergo_docx_') . '.docx';
            $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save($tempFile);

            $binary = file_get_contents($tempFile);
            @unlink($tempFile);

            return $binary;
        } finally {
            libxml_use_internal_errors($prevXmlErrors);
            error_reporting($errorLevel);
        }
    }

    private function addKeyValueRow($table, string $bullet, string $key, string $value, bool $bold = false): void
    {
        $row = $table->addRow(Converter::pointToTwip(16));
        $row->addCell(Converter::cmToTwip(0.6))->addText($bullet, ['size' => 9.5], ['spaceAfter' => 0]);
        $row->addCell(Converter::cmToTwip(5.8))->addText($key, ['size' => 9.5], ['spaceAfter' => 0]);
        $row->addCell(Converter::cmToTwip(0.4))->addText(':', ['size' => 9.5], ['spaceAfter' => 0]);
        $row->addCell(Converter::cmToTwip(10.4))->addText($this->xmlSafe($value), ['bold' => $bold, 'size' => 9.5], ['spaceAfter' => 0]);
    }

    private function addBulletPoint($section, string $bullet, string $text): void
    {
        $run = $section->addTextRun([
            'alignment'   => Jc::BOTH,
            'spaceAfter'  => 12,
            'indentation' => [
                'left'    => Converter::cmToTwip(0.8),
                'hanging' => Converter::cmToTwip(0.5),
            ],
        ]);
        $run->addText($bullet . ' ', ['bold' => true, 'size' => 9]);
        $run->addText($this->xmlSafe($text), ['size' => 9]);
    }

    private function addChecklistRow($table, string $potensi, string $durasi, string $skor): void
    {
        $row = $table->addRow();
        $row->addCell(Converter::cmToTwip(12.2))->addText($this->xmlSafe($potensi), ['size' => 8]);
        $row->addCell(Converter::cmToTwip(3.2))->addText($durasi, ['size' => 8], ['alignment' => Jc::CENTER]);
        $row->addCell(Converter::cmToTwip(1.8))->addText($skor, ['size' => 8], ['alignment' => Jc::CENTER]);
    }

    private function addHeaderRepeatBox($section, int $currentPage, int $totalPages): void
    {
        $table = $section->addTable([
            'alignment'       => JcTable::CENTER,
            'layout'          => Table::LAYOUT_FIXED,
            'unit'            => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
            'width'           => Converter::cmToTwip(17.2),
            'borderSize'      => 6,
            'borderColor'     => '000000',
            'cellMarginTop'   => 30,
            'cellMarginBottom'=> 30,
            'cellMarginLeft'  => 30,
            'cellMarginRight' => 30,
        ]);
        $row = $table->addRow();

        // Logo
        $cellLogo = $row->addCell(Converter::cmToTwip(2.2));
        $basePath = (function_exists('app') && app()->has('path.public')) ? app('path.public') : (dirname(__DIR__, 2) . '/public');
        $logoCandidates = [
            $basePath . '/images/Logo Kemnaker.png',
            $basePath . '/images/Logo.png',
        ];
        foreach ($logoCandidates as $cand) {
            if (file_exists($cand) && is_readable($cand)) {
                $cellLogo->addImage($cand, ['width' => 40, 'height' => 40, 'alignment' => Jc::CENTER]);
                break;
            }
        }

        // Teks Instansi
        $cellInst = $row->addCell(Converter::cmToTwip(11.8));
        $cellInst->addText("KEMENTERIAN KETENAGAKERJAAN RI\nDIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN\nDAN KESELAMATAN DAN KESEHATAN KERJA\nBALAI HIPERKES DAN KESELAMATAN KERJA SURABAYA", ['bold' => true, 'size' => 8], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);

        // Meta Page Box
        $cellMeta = $row->addCell(Converter::cmToTwip(3.2));
        $cellMeta->addText("Page: {$currentPage}/{$totalPages}\nRev/Terb.: -/1", ['size' => 8], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);

        $section->addTextBreak(1, ['size' => 4]);
    }

    private function addStandardFooter($section): void
    {
        $footer = $section->addFooter();
        $table = $footer->addTable([
            'alignment'       => JcTable::CENTER,
            'layout'          => Table::LAYOUT_FIXED,
            'unit'            => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
            'width'           => Converter::cmToTwip(17.2),
            'borderTopSize'   => 4,
            'borderTopColor'  => '000000',
            'cellMarginTop'   => 15,
            'cellMarginBottom'=> 0,
            'cellMarginLeft'  => 0,
            'cellMarginRight' => 0,
        ]);
        $row = $table->addRow();
        $row->addCell(Converter::cmToTwip(10.0))->addText('Tgl. terbit: 24 Desember 2024', ['size' => 7.5, 'name' => 'Times New Roman'], ['spaceAfter' => 0]);
        $row->addCell(Converter::cmToTwip(7.2))->addText('No. : F/7.8.38/BK3-SBY', ['size' => 7.5, 'name' => 'Times New Roman'], ['alignment' => Jc::END, 'spaceAfter' => 0]);
    }

    private function xmlSafe(?string $text): string
    {
        if ($text === null) {
            return '';
        }
        return htmlspecialchars($text, ENT_XML1, 'UTF-8');
    }
}
