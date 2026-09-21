<?php

namespace App\Services;

use App\Models\SuketK3;
use App\Models\User;
use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\JcTable;
use ZipArchive;

class SuketDocxService
{
    /**
     * Generate standard Suket K3 Lingkungan Kerja DOCX file compliant with Permenaker 05/2018
     * matching the layout, typography, and structure of the official template.
     */
    public function generateDocx(SuketK3 $suket): string
    {
        $errorLevel = error_reporting(E_ALL & ~E_DEPRECATED);
        $prevXmlErrors = libxml_use_internal_errors(true);

        try {
            $phpWord = new PhpWord();
            $phpWord->setDefaultFontName('Times New Roman');
            $phpWord->setDefaultFontSize(11);

            // Document metadata
            $properties = $phpWord->getDocInfo();
            $properties->setCreator('Balai K3 Surabaya - Kemnaker RI');
            $properties->setTitle('Surat Keterangan K3 Lingkungan Kerja - ' . ($suket->perusahaan_nama ?: $suket->nomor_order));
            $properties->setSubject('Hasil Pengujian K3 Lingkungan Kerja');

            $section = $phpWord->addSection([
                'paperSize' => 'A4',
                'marginTop' => Converter::cmToTwip(1.8),
                'marginBottom' => Converter::cmToTwip(1.8),
                'marginLeft' => Converter::cmToTwip(2.2),
                'marginRight' => Converter::cmToTwip(1.8),
            ]);

            // 1. KOP SURAT RESMI KEMNAKER / BALAI K3 SURABAYA
            $headerTable = $section->addTable([
                'alignment' => JcTable::CENTER,
                'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
                'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width' => Converter::cmToTwip(16.8),
                'cellMargin' => 0,
                'borderSize' => 0,
            ]);
            $headerTable->addRow();

            // Logo Kemnaker / Balai K3
            $logoCell = $headerTable->addCell(Converter::cmToTwip(2.3), [
                'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width' => Converter::cmToTwip(2.3),
            ]);
            $logoCandidates = [
                public_path('images/Logo Kemnaker.png'),
                public_path('images/Logo.png'),
            ];
            foreach ($logoCandidates as $candidate) {
                if (file_exists($candidate) && is_readable($candidate)) {
                    $logoCell->addImage($candidate, [
                        'width' => 60,
                        'height' => 60,
                        'alignment' => Jc::CENTER,
                    ]);
                    break;
                }
            }

            // Teks Instansi
            $textCell = $headerTable->addCell(Converter::cmToTwip(14.5), [
                'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width' => Converter::cmToTwip(14.5),
            ]);
            $textCell->addText('KEMENTERIAN KETENAGAKERJAAN REPUBLIK INDONESIA', ['bold' => true, 'size' => 12, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 20]);
            $textCell->addText('DIREKTORAT JENDERAL PEMBINAAN PENGAWASAN KETENAGAKERJAAN DAN K3', ['bold' => true, 'size' => 10.5, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 20]);
            $textCell->addText('BALAI KESELAMATAN DAN KESEHATAN KERJA SURABAYA', ['bold' => true, 'size' => 12.5, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 20]);
            $textCell->addText('Jl. Dukuh Menanggal XII/No. 2, Gayungan, Surabaya, Jawa Timur 60234', ['italic' => true, 'size' => 8.5, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 10]);
            $textCell->addText('Telepon: (031) 8280220 | Email: balaik3_sby@kemnaker.go.id | Website: balaik3surabaya.kemnaker.go.id', ['italic' => true, 'size' => 8, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);

            // Garis Pembatas Kop (Double Border Standar Kementerian)
            $dividerTable = $section->addTable([
                'alignment' => JcTable::CENTER,
                'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
                'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width' => Converter::cmToTwip(16.8),
                'cellMargin' => 0,
            ]);
            $divRow = $dividerTable->addRow(Converter::pointToTwip(2));
            $divCell = $divRow->addCell(Converter::cmToTwip(16.8), [
                'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width' => Converter::cmToTwip(16.8),
                'borderBottomSize' => 18,
                'borderBottomColor' => '000000',
                'borderBottomStyle' => 'double',
            ]);
            $divCell->addText('', [], ['spaceBefore' => 0, 'spaceAfter' => 0]);

            $section->addTextBreak(1, ['size' => 6]);

            // 2. JUDUL SURAT
            $nomorSurat = $suket->nomor_surat ?: ('.../SK-LK/BK3-SBY/' . Carbon::now()->format('m/Y'));
            $section->addText('SURAT KETERANGAN', ['bold' => true, 'underline' => 'single', 'size' => 13, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 30]);
            $section->addText($this->xmlSafe('Nomor: ' . $nomorSurat), ['size' => 10.5, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 30]);
            $section->addText('TENTANG', ['bold' => true, 'size' => 11, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 20]);
            $section->addText('HASIL PENGUJIAN KESELAMATAN DAN KESEHATAN KERJA LINGKUNGAN KERJA', ['bold' => true, 'size' => 11, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 100]);

            // 3. PEMBUKA
            $section->addText(
                'Berdasarkan hasil pemeriksaan dan pengujian Keselamatan dan Kesehatan Kerja (K3) Lingkungan Kerja yang telah dilaksanakan di tempat kerja sesuai dengan ketentuan Peraturan Menteri Ketenagakerjaan Republik Indonesia Nomor 5 Tahun 2018 tentang Keselamatan dan Kesehatan Kerja Lingkungan Kerja, Kepala Balai Keselamatan dan Kesehatan Kerja Surabaya dengan ini menerangkan bahwa:',
                ['size' => 11, 'name' => 'Times New Roman'],
                ['alignment' => Jc::BOTH, 'spaceAfter' => 80, 'indentation' => ['firstLine' => Converter::cmToTwip(1.0)]]
            );

            // 4. DATA PERUSAHAAN
            $dasarLhu = ($suket->lhu_source === 'auto')
                ? 'Laporan Hasil Uji (LHU) Resmi Terverifikasi Balai K3 Surabaya'
                : 'Laporan Hasil Uji (LHU) Pengujian K3 Mandiri / Laboratorium Terakreditasi';

            $companyTable = $section->addTable([
                'alignment' => JcTable::CENTER,
                'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
                'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width' => Converter::cmToTwip(16.8),
                'cellMarginTop' => 30,
                'cellMarginBottom' => 30,
                'cellMarginLeft' => 80,
                'cellMarginRight' => 80,
                'borderSize' => 0,
            ]);
            $this->addCompanyRow($companyTable, '1. Nama Perusahaan / Pemohon', $suket->perusahaan_nama ?: 'Perusahaan ' . $suket->nomor_order, true);
            $this->addCompanyRow($companyTable, '2. Alamat / Lokasi Pengujian', $suket->lokasi ?: 'Lokasi Uji K3');
            $this->addCompanyRow($companyTable, '3. Nomor Order / Kode Permohonan', $suket->nomor_order);
            $this->addCompanyRow($companyTable, '4. Dasar Dokumen Pengujian', $dasarLhu);

            $section->addTextBreak(1, ['size' => 4]);

            // 5. RUANG LINGKUP & TABEL FAKTOR
            $section->addText(
                'Telah dilakukan pengujian dan evaluasi terhadap faktor-faktor lingkungan kerja dengan ruang lingkup sebagai berikut:',
                ['size' => 11, 'name' => 'Times New Roman'],
                ['alignment' => Jc::BOTH, 'spaceAfter' => 60]
            );

            $faktorLabels = [
                'fisika' => 'Faktor Fisika (Kebisingan, Iklim Kerja/ISBB, Penerangan, Getaran)',
                'kimia' => 'Faktor Kimia (Debu Total/Respirabel, Uap, Gas Kimia Berbahaya)',
                'biologi' => 'Faktor Biologi (Bakteri, Jamur, Angka Kuman Udara)',
                'ergonomi' => 'Faktor Ergonomi (Postur Kerja, Gerakan Berulang, Desain Kerja)',
                'psikologi' => 'Faktor Psikologi (Beban Kerja Mental, Potensi Stres Kerja)',
            ];

            $faktorList = is_array($suket->faktor_k3) ? $suket->faktor_k3 : [];
            $catatanEvaluasi = !empty($suket->catatan_evaluasi)
                ? $suket->catatan_evaluasi
                : 'Telah diuji dan dievaluasi sesuai Nilai Ambang Batas (NAB).';

            $factorTable = $section->addTable([
                'alignment' => JcTable::CENTER,
                'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
                'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width' => Converter::cmToTwip(16.8),
                'cellMarginTop' => 60,
                'cellMarginBottom' => 60,
                'cellMarginLeft' => 80,
                'cellMarginRight' => 80,
                'borderSize' => 6,
                'borderColor' => '000000',
            ]);

            // Table Header
            $factorTable->addRow(Converter::pointToTwip(22));
            $factorTable->addCell(Converter::cmToTwip(1.0), ['bgColor' => 'F2F2F2'])->addText('No', ['bold' => true, 'size' => 10], ['alignment' => Jc::CENTER]);
            $factorTable->addCell(Converter::cmToTwip(6.8), ['bgColor' => 'F2F2F2'])->addText('Faktor Lingkungan Kerja', ['bold' => true, 'size' => 10], ['alignment' => Jc::CENTER]);
            $factorTable->addCell(Converter::cmToTwip(3.4), ['bgColor' => 'F2F2F2'])->addText('Standar Regulasi', ['bold' => true, 'size' => 10], ['alignment' => Jc::CENTER]);
            $factorTable->addCell(Converter::cmToTwip(5.6), ['bgColor' => 'F2F2F2'])->addText('Hasil Evaluasi Teknis K3', ['bold' => true, 'size' => 10], ['alignment' => Jc::CENTER]);

            $no = 1;
            if (!empty($faktorList)) {
                foreach ($faktorList as $fKey) {
                    $factorTable->addRow();
                    $factorTable->addCell(Converter::cmToTwip(1.0))->addText((string) $no++, ['size' => 10], ['alignment' => Jc::CENTER]);
                    $factorTable->addCell(Converter::cmToTwip(6.8))->addText($this->xmlSafe($faktorLabels[$fKey] ?? ucfirst($fKey)), ['bold' => true, 'size' => 10]);
                    $factorTable->addCell(Converter::cmToTwip(3.4))->addText('Permenaker No. 5/2018', ['size' => 10], ['alignment' => Jc::CENTER]);
                    $factorTable->addCell(Converter::cmToTwip(5.6))->addText($this->xmlSafe($catatanEvaluasi), ['size' => 10]);
                }
            } else {
                $factorTable->addRow();
                $factorTable->addCell(Converter::cmToTwip(1.0))->addText('1', ['size' => 10], ['alignment' => Jc::CENTER]);
                $factorTable->addCell(Converter::cmToTwip(6.8))->addText('Faktor Fisika dan Kimia Lingkungan Kerja', ['bold' => true, 'size' => 10]);
                $factorTable->addCell(Converter::cmToTwip(3.4))->addText('Permenaker No. 5/2018', ['size' => 10], ['alignment' => Jc::CENTER]);
                $factorTable->addCell(Converter::cmToTwip(5.6))->addText($this->xmlSafe($catatanEvaluasi), ['size' => 10]);
            }

            $section->addTextBreak(1, ['size' => 6]);

            // 6. KESIMPULAN (Callout Box Berbingkai Sesuai Template Resmi)
            $section->addText(
                'Berdasarkan telaah dokumen teknis, foto pengujian lapangan, serta denah penempatan titik ukur, kondisi lingkungan kerja pada area yang diuji dinyatakan:',
                ['size' => 11, 'name' => 'Times New Roman'],
                ['alignment' => Jc::BOTH, 'spaceAfter' => 60, 'indentation' => ['firstLine' => Converter::cmToTwip(1.0)]]
            );

            $boxTable = $section->addTable([
                'alignment' => JcTable::CENTER,
                'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
                'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width' => Converter::cmToTwip(16.8),
                'borderSize' => 8,
                'borderColor' => '000000',
                'cellMarginTop' => 120,
                'cellMarginBottom' => 120,
                'cellMarginLeft' => 120,
                'cellMarginRight' => 120,
            ]);
            $boxRow = $boxTable->addRow();
            $boxCell = $boxRow->addCell(Converter::cmToTwip(16.8), ['bgColor' => 'F9F9F9']);
            $boxCell->addText('MEMENUHI PERSYARATAN KESELAMATAN DAN KESEHATAN KERJA LINGKUNGAN KERJA', ['bold' => true, 'size' => 11.5, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 30]);
            $boxCell->addText('Sesuai dengan Nilai Ambang Batas (NAB) dan Standar Higiene Industri Permenaker No. 5 Tahun 2018', ['italic' => true, 'size' => 9.5, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);

            $section->addTextBreak(1, ['size' => 6]);

            // 7. KETENTUAN DAN MASA BERLAKU
            $section->addText('Ketentuan dan Masa Berlaku:', ['bold' => true, 'size' => 11, 'name' => 'Times New Roman'], ['spaceBefore' => 60, 'spaceAfter' => 30]);

            $p1 = $section->addTextRun(['alignment' => Jc::BOTH, 'spaceAfter' => 30, 'indentation' => ['left' => Converter::cmToTwip(0.8), 'hanging' => Converter::cmToTwip(0.5)]]);
            $p1->addText('1. ');
            $p1->addText('Perusahaan wajib mempertahankan dan memelihara kondisi lingkungan kerja yang telah memenuhi syarat K3 serta melakukan pengendalian teknis secara berkesinambungan.');

            $p2 = $section->addTextRun(['alignment' => Jc::BOTH, 'spaceAfter' => 30, 'indentation' => ['left' => Converter::cmToTwip(0.8), 'hanging' => Converter::cmToTwip(0.5)]]);
            $p2->addText('2. ');
            $p2->addText('Surat Keterangan ini berlaku selama ');
            $p2->addText('1 (satu) tahun', ['bold' => true]);
            $p2->addText(' terhitung sejak tanggal diterbitkan, sepanjang tidak terdapat perubahan tata letak mesin, proses produksi, bahan kimia yang digunakan, maupun modifikasi lingkungan kerja yang signifikan.');

            $p3 = $section->addTextRun(['alignment' => Jc::BOTH, 'spaceAfter' => 60, 'indentation' => ['left' => Converter::cmToTwip(0.8), 'hanging' => Converter::cmToTwip(0.5)]]);
            $p3->addText('3. ');
            $p3->addText('Surat Keterangan ini dapat dicabut kembali apabila di kemudian hari ditemukan ketidaksesuaian penerapan norma K3 di tempat kerja.');

            // 8. PENUTUP
            $section->addText(
                'Demikian Surat Keterangan ini dibuat dan diterbitkan untuk dapat dipergunakan sebagaimana mestinya.',
                ['size' => 11, 'name' => 'Times New Roman'],
                ['alignment' => Jc::BOTH, 'spaceBefore' => 60, 'spaceAfter' => 100]
            );

            // 9. TANDA TANGAN KEPALA BALAI
            $kepalaBalai = User::whereIn('role', ['kepala_balai', 'mp'])->first();
            $kepalaNama = $kepalaBalai?->name ?? 'Dr. H. Agus Triyono, S.T., M.Kes.';
            $kepalaNip = $kepalaBalai?->nip ?? '19750812 200212 1 001';

            $tanggalTerbit = $suket->tanggal_surat
                ? Carbon::parse($suket->tanggal_surat)->locale('id')->translatedFormat('d F Y')
                : Carbon::now()->locale('id')->translatedFormat('d F Y');

            $signTable = $section->addTable([
                'alignment' => JcTable::CENTER,
                'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
                'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width' => Converter::cmToTwip(16.8),
                'cellMarginTop' => 0,
                'cellMarginBottom' => 0,
                'cellMarginLeft' => 0,
                'cellMarginRight' => 0,
                'borderSize' => 0,
            ]);
            $signRow = $signTable->addRow();
            $leftCell = $signRow->addCell(Converter::cmToTwip(9.5), [
                'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width' => Converter::cmToTwip(9.5),
            ]);
            // Non-breaking space agar Microsoft Word tidak mengecilkan/meng-collapse kolom kosong kiri saat diedit
            $leftCell->addTextRun()->addText(' ', ['size' => 1]);

            $signCell = $signRow->addCell(Converter::cmToTwip(7.3), [
                'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
                'width' => Converter::cmToTwip(7.3),
            ]);

            $signCell->addText('Ditetapkan di: Surabaya', ['size' => 10.5, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 10]);
            $signCell->addText('Pada tanggal: ' . $this->xmlSafe($tanggalTerbit), ['size' => 10.5, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 20]);
            $signCell->addText('KEPALA BALAI KESELAMATAN DAN', ['bold' => true, 'size' => 10.5, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 10]);
            $signCell->addText('KESEHATAN KERJA SURABAYA', ['bold' => true, 'size' => 10.5, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 20]);

            if (!empty($suket->signed_at)) {
                $tteBox = $signCell->addTable([
                    'alignment' => JcTable::CENTER,
                    'borderSize' => 12,
                    'borderColor' => '198754',
                    'borderStyle' => 'dashed',
                    'cellMarginTop' => 60,
                    'cellMarginBottom' => 60,
                    'cellMarginLeft' => 80,
                    'cellMarginRight' => 80,
                ]);
                $tteRow = $tteBox->addRow();
                $tteCell = $tteRow->addCell(Converter::cmToTwip(6.8), ['bgColor' => 'F0FDF4']);
                $tteCell->addText('✓ TERTANDATANGANI ELEKTRONIK', ['bold' => true, 'color' => '15803D', 'size' => 9, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 10]);
                $tteCell->addText('Balai K3 Surabaya - Kemnaker RI', ['size' => 8.5, 'color' => '374151', 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 10]);
                $tteCell->addText('Pada: ' . Carbon::parse($suket->signed_at)->format('d/m/Y H:i') . ' WIB', ['size' => 8, 'color' => '6B7280', 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
            } else {
                $signCell->addText('[Draft Dokumen - Menunggu Pengesahan]', ['italic' => true, 'color' => '888888', 'size' => 9, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceBefore' => 180, 'spaceAfter' => 180]);
            }

            $signCell->addText($this->xmlSafe($kepalaNama), ['bold' => true, 'underline' => 'single', 'size' => 11, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 10]);
            $signCell->addText('NIP. ' . $this->xmlSafe($kepalaNip), ['size' => 10, 'name' => 'Times New Roman'], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);

            $tempFile = tempnam(sys_get_temp_dir(), 'suket_docx_') . '.docx';
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

    private function addCompanyRow($table, string $label, string $value, bool $boldValue = false): void
    {
        $table->addRow(Converter::pointToTwip(18));
        $table->addCell(Converter::cmToTwip(6.0), [
            'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
            'width' => Converter::cmToTwip(6.0),
        ])->addText($label, ['size' => 11, 'name' => 'Times New Roman'], ['spaceAfter' => 0]);
        $table->addCell(Converter::cmToTwip(0.4), [
            'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
            'width' => Converter::cmToTwip(0.4),
        ])->addText(':', ['size' => 11, 'name' => 'Times New Roman'], ['spaceAfter' => 0]);
        $table->addCell(Converter::cmToTwip(10.4), [
            'unit' => \PhpOffice\PhpWord\SimpleType\TblWidth::TWIP,
            'width' => Converter::cmToTwip(10.4),
        ])->addText($this->xmlSafe($value), ['size' => 11, 'name' => 'Times New Roman', 'bold' => $boldValue], ['spaceAfter' => 0]);
    }

    private function xmlSafe(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1, 'UTF-8');
    }

    private const PRIVATE_DISK = 'local';

    /**
     * Sinkronkan Nomor Surat dan Tanggal Surat ke berkas fisik DOCX
     * Berlaku baik untuk draf otomatis maupun draf revisi hasil upload penguji
     */
    public function syncDocxMetadata(SuketK3 $suket, ?string $explicitPath = null): bool
    {
        if (empty($suket->nomor_surat) && empty($suket->tanggal_surat)) {
            return false;
        }

        $fullPath = null;
        $disk = self::PRIVATE_DISK;
        if ($explicitPath) {
            if (file_exists($explicitPath)) {
                $fullPath = $explicitPath;
            } elseif (Storage::disk($disk)->exists($explicitPath)) {
                $fullPath = Storage::disk($disk)->path($explicitPath);
            }
        } elseif (!empty($suket->draft_file_path) && Storage::disk($disk)->exists($suket->draft_file_path)) {
            $fullPath = Storage::disk($disk)->path($suket->draft_file_path);
        }

        if (!$fullPath || !file_exists($fullPath) || !str_ends_with(strtolower($fullPath), '.docx')) {
            return false;
        }

        return $this->replaceMetadataInDocxFile($fullPath, $suket->nomor_surat, $suket->tanggal_surat);
    }

    /**
     * Manipulasi word/document.xml di dalam arsip ZIP .docx secara in-place
     * untuk mengganti nomor surat dan tanggal surat tanpa merusak format Word dan isinya.
     */
    public function replaceMetadataInDocxFile(string $docxFullPath, ?string $nomorSurat, ?string $tanggalSurat = null): bool
    {
        if (!file_exists($docxFullPath) || !is_writable($docxFullPath)) {
            return false;
        }

        $zip = new ZipArchive();
        if ($zip->open($docxFullPath) !== true) {
            return false;
        }

        $xml = $zip->getFromName('word/document.xml');
        if (!$xml) {
            $zip->close();
            return false;
        }

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = false;

        $prevXmlErrors = libxml_use_internal_errors(true);
        if (!@$dom->loadXML($xml, LIBXML_NOERROR | LIBXML_NOWARNING)) {
            libxml_use_internal_errors($prevXmlErrors);
            $zip->close();
            return false;
        }
        libxml_use_internal_errors($prevXmlErrors);

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $paragraphs = $xpath->query('//w:p');
        $nomorFound = false;
        $isNextNomor = false;
        $suratKeteranganNode = null;

        $formattedTanggal = null;
        if (!empty($tanggalSurat)) {
            try {
                $formattedTanggal = Carbon::parse($tanggalSurat)->locale('id')->translatedFormat('d F Y');
            } catch (\Throwable $e) {
                $formattedTanggal = null;
            }
        }

        foreach ($paragraphs as $p) {
            $tNodes = $xpath->query('.//w:t', $p);
            $pText = '';
            foreach ($tNodes as $t) {
                $pText .= $t->nodeValue;
            }
            $trimmed = trim($pText);

            if ($trimmed === 'SURAT KETERANGAN' || str_contains($trimmed, 'SURAT KETERANGAN')) {
                $isNextNomor = true;
                $suratKeteranganNode = $p;
                continue;
            }

            // Ganti Nomor Surat
            if (!empty($nomorSurat) && !$nomorFound) {
                $isNomorLine = (
                    $isNextNomor ||
                    preg_match('/^\s*(Nomor\s*Surat|Nomor|No)[\s\.:]+/i', $pText) ||
                    (str_contains($pText, 'SK-LK') && !str_contains($pText, 'Peraturan')) ||
                    str_contains($pText, '.../SK-LK/BK3-SBY')
                ) && !str_contains($pText, 'Nomor Order')
                  && !str_contains($pText, 'Nomor 5 Tahun')
                  && !str_contains($pText, 'Permenaker')
                  && !empty($trimmed);

                if ($isNomorLine) {
                    if ($tNodes->length > 0) {
                        $firstT = $tNodes->item(0);
                        while ($firstT->hasChildNodes()) {
                            $firstT->removeChild($firstT->firstChild);
                        }
                        $firstT->appendChild($dom->createTextNode('Nomor: ' . $nomorSurat));
                        $firstT->setAttribute('xml:space', 'preserve');

                        for ($i = 1; $i < $tNodes->length; $i++) {
                            $remT = $tNodes->item($i);
                            while ($remT->hasChildNodes()) {
                                $remT->removeChild($remT->firstChild);
                            }
                        }
                    }
                    $nomorFound = true;
                    $isNextNomor = false;
                    continue;
                }
            }

            // Ganti Tanggal Surat di Blok Tanda Tangan
            if (!empty($formattedTanggal) && preg_match('/Pada\s+tanggal\s*:/i', $pText)) {
                if ($tNodes->length > 0) {
                    $firstT = $tNodes->item(0);
                    while ($firstT->hasChildNodes()) {
                        $firstT->removeChild($firstT->firstChild);
                    }
                    $firstT->appendChild($dom->createTextNode('Pada tanggal: ' . $formattedTanggal));
                    $firstT->setAttribute('xml:space', 'preserve');

                    for ($i = 1; $i < $tNodes->length; $i++) {
                        $remT = $tNodes->item($i);
                        while ($remT->hasChildNodes()) {
                            $remT->removeChild($remT->firstChild);
                        }
                    }
                }
            }

            if (!empty($trimmed)) {
                $isNextNomor = false;
            }
        }

        // Jika nomor surat belum terpasang dan elemen SURAT KETERANGAN ditemukan, sisipkan paragraf baru
        if (!empty($nomorSurat) && !$nomorFound && $suratKeteranganNode) {
            $wNs = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
            $newP = $dom->createElementNS($wNs, 'w:p');
            $pPr = $dom->createElementNS($wNs, 'w:pPr');
            $jc = $dom->createElementNS($wNs, 'w:jc');
            $jc->setAttributeNS($wNs, 'w:val', 'center');
            $pPr->appendChild($jc);
            $newP->appendChild($pPr);

            $r = $dom->createElementNS($wNs, 'w:r');
            $t = $dom->createElementNS($wNs, 'w:t');
            $t->appendChild($dom->createTextNode('Nomor: ' . $nomorSurat));
            $t->setAttribute('xml:space', 'preserve');
            $r->appendChild($t);
            $newP->appendChild($r);

            if ($suratKeteranganNode->nextSibling) {
                $suratKeteranganNode->parentNode->insertBefore($newP, $suratKeteranganNode->nextSibling);
            } else {
                $suratKeteranganNode->parentNode->appendChild($newP);
            }
        }

        $newXml = $dom->saveXML();
        $zip->addFromString('word/document.xml', $newXml);
        $zip->close();

        return true;
    }

    /**
     * Convert an existing DOCX file to HTML for screen preview with strict styling constraint
     */
    public function renderDocxToHtmlPreview(string $fullPath, SuketK3 $suket): ?string
    {
        if (!file_exists($fullPath) || !is_readable($fullPath)) {
            return null;
        }

        if (!empty($suket->nomor_surat)) {
            $this->syncDocxMetadata($suket, $fullPath);
        }

        $errorLevel = error_reporting(E_ALL & ~E_DEPRECATED);
        $prevXmlErrors = libxml_use_internal_errors(true);

        try {
            $phpWord = IOFactory::load($fullPath, 'Word2007');
            $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');

            ob_start();
            $htmlWriter->save('php://output');
            $rawHtml = ob_get_clean();

            $styledBody = $this->normalizeAndStyleHtmlOutput($rawHtml, $phpWord, $suket);

            return $this->wrapInA4PreviewContainer($styledBody, $suket);
        } catch (\Throwable $e) {
            Log::warning('SuketDocxService: failed to render docx to html: ' . $e->getMessage());
            return null;
        } finally {
            libxml_use_internal_errors($prevXmlErrors);
            error_reporting($errorLevel);
        }
    }

    /**
     * Convert an existing DOCX file to PDF binary
     */
    public function renderDocxToPdf(string $fullPath, SuketK3 $suket): ?string
    {
        if (!file_exists($fullPath) || !is_readable($fullPath)) {
            return null;
        }

        if (!empty($suket->nomor_surat)) {
            $this->syncDocxMetadata($suket, $fullPath);
        }

        $errorLevel = error_reporting(E_ALL & ~E_DEPRECATED);
        $prevXmlErrors = libxml_use_internal_errors(true);

        try {
            $phpWord = IOFactory::load($fullPath, 'Word2007');
            $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');

            ob_start();
            $htmlWriter->save('php://output');
            $rawHtml = ob_get_clean();

            $styledBody = $this->normalizeAndStyleHtmlOutput($rawHtml, $phpWord, $suket);
            $styledHtml = $this->wrapInA4PdfContainer($styledBody, $suket);

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($styledHtml)->setPaper('a4', 'portrait');
            return $pdf->output();
        } catch (\Throwable $e) {
            Log::warning('SuketDocxService: failed to render docx to pdf: ' . $e->getMessage());
            return null;
        } finally {
            libxml_use_internal_errors($prevXmlErrors);
            error_reporting($errorLevel);
        }
    }

    /**
     * Normalisasi dan styling cerdas HTML hasil konversi DOCX agar layout (termasuk posisi tanda tangan kanan,
     * tabel faktor K3, kop surat, dan metadata) presisi dan tidak berantakan / acak-acakan di preview browser maupun PDF.
     */
    public function normalizeAndStyleHtmlOutput(string $rawHtml, $phpWord = null, ?SuketK3 $suket = null): string
    {
        if (empty(trim($rawHtml))) {
            return $rawHtml;
        }

        $bodyContent = $rawHtml;
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $rawHtml, $matches)) {
            $bodyContent = $matches[1];
        }

        // 1. Normalisasi dimensi gambar Logo Kemnaker agar proporsional
        $bodyContent = preg_replace_callback('/<img([^>]+)>/i', function ($match) {
            $tag = $match[0];
            if (preg_match('/style="[^"]*"/i', $tag)) {
                return preg_replace('/style="[^"]*"/i', 'style="max-width: 65px; max-height: 65px; width: 65px; height: auto; display: block; margin: 0 auto;"', $tag);
            }
            return '<img style="max-width: 65px; max-height: 65px; width: 65px; height: auto; display: block; margin: 0 auto;" ' . $match[1] . '>';
        }, $bodyContent);

        // 2. Gunakan DOMDocument untuk restrukturisasi tabel dan kolom secara cerdas
        $dom = new \DOMDocument();
        $prevXmlErrors = libxml_use_internal_errors(true);
        $encodedHtml = mb_convert_encoding($bodyContent, 'HTML-ENTITIES', 'UTF-8');
        $dom->loadHTML('<div>' . $encodedHtml . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($prevXmlErrors);

        $xpath = new \DOMXPath($dom);
        $tables = $xpath->query('//table');

        foreach ($tables as $tbl) {
            $text = $tbl->textContent;
            $isNested = $tbl->parentNode && ($tbl->parentNode->nodeName === 'td' || $tbl->parentNode->nodeName === 'th');

            // Kasus 1: Tabel Bersarang TTE Badge (✓ TERTANDATANGANI ELEKTRONIK / Draft Dokumen)
            if ($isNested || str_contains($text, 'TERTANDATANGANI ELEKTRONIK') || str_contains($text, 'DRAFT DOKUMEN')) {
                $tbl->setAttribute('style', 'width: auto !important; max-width: 250px !important; min-width: 210px !important; margin: 6pt auto !important; border: 1.5px dashed #198754 !important; background-color: #F0FDF4 !important; border-collapse: collapse !important;');
                foreach ($xpath->query('.//td | .//th', $tbl) as $cell) {
                    $cell->setAttribute('style', 'border: none !important; padding: 4pt 8pt !important; text-align: center !important; background-color: #F0FDF4 !important;');
                }
                continue;
            }

            // Kasus 2: Tabel Tanda Tangan (Ditetapkan di: Surabaya / KEPALA BALAI) -> WAJIB RATA KANAN
            if (str_contains($text, 'Ditetapkan di') || (str_contains($text, 'KEPALA BALAI') && !str_contains($text, 'KEMENTERIAN'))) {
                $tbl->setAttribute('style', 'width: 100% !important; border-collapse: collapse !important; border: none !important; margin-top: 18pt !important; margin-bottom: 0 !important;');
                $rows = $xpath->query('./tr', $tbl);
                foreach ($rows as $row) {
                    $cells = $xpath->query('./td | ./th', $row);
                    if ($cells->length >= 2) {
                        // Kolom 1 adalah spacer kosong kiri (55%)
                        $cells->item(0)->setAttribute('style', 'width: 55% !important; border: none !important; padding: 0 !important; vertical-align: top;');
                        // Kolom 2 adalah blok tanda tangan kanan (45%) terpusat
                        $cells->item(1)->setAttribute('style', 'width: 45% !important; text-align: center !important; border: none !important; padding: 0 4pt !important; vertical-align: top;');
                    } elseif ($cells->length === 1) {
                        // Jika dalam format 1 kolom tapi tanda tangan, dorong seluruh tabel ke kanan
                        $tbl->setAttribute('style', 'width: 45% !important; margin-left: auto !important; margin-right: 0 !important; border-collapse: collapse !important; border: none !important; margin-top: 18pt !important;');
                        $cells->item(0)->setAttribute('style', 'width: 100% !important; text-align: center !important; border: none !important; padding: 0 4pt !important; vertical-align: top;');
                    }
                }
                continue;
            }

            // Kasus 3: Kop Surat (Logo Kemnaker & Teks Kementerian)
            if (str_contains($text, 'KEMENTERIAN KETENAGAKERJAAN') || str_contains($text, 'BALAI KESELAMATAN DAN KESEHATAN KERJA SURABAYA')) {
                $tbl->setAttribute('style', 'width: 100% !important; border-collapse: collapse !important; border: none !important; margin-bottom: 0 !important;');
                $rows = $xpath->query('./tr', $tbl);
                foreach ($rows as $row) {
                    $cells = $xpath->query('./td | ./th', $row);
                    if ($cells->length === 2) {
                        $cells->item(0)->setAttribute('style', 'width: 15% !important; text-align: center !important; vertical-align: middle !important; border: none !important; padding: 2pt !important;');
                        $cells->item(1)->setAttribute('style', 'width: 85% !important; text-align: center !important; vertical-align: middle !important; border: none !important; padding: 2pt !important;');
                    }
                }
                continue;
            }

            // Kasus 4: Garis Pembatas Kop Surat (Double Line Border Bottom)
            if ($tbl->getElementsByTagName('tr')->length === 1 && $tbl->getElementsByTagName('td')->length === 1 && empty(trim($text))) {
                $tbl->setAttribute('style', 'width: 100% !important; border-collapse: collapse !important; border: none !important; margin: 0 0 10pt 0 !important;');
                foreach ($xpath->query('.//td', $tbl) as $td) {
                    $td->setAttribute('style', 'border-bottom: 3px double #000000 !important; border-top: none !important; border-left: none !important; border-right: none !important; height: 3px !important; padding: 0 !important;');
                }
                continue;
            }

            // Kasus 5: Tabel Metadata Perusahaan (Nama Perusahaan, Alamat, Nomor Order, Dasar Dokumen)
            if (str_contains($text, 'Nama Perusahaan') || str_contains($text, 'Nomor Order') || str_contains($text, 'Dasar Dokumen')) {
                $tbl->setAttribute('style', 'width: 100% !important; border-collapse: collapse !important; border: none !important; margin: 6pt 0 10pt 0 !important;');
                $rows = $xpath->query('./tr', $tbl);
                foreach ($rows as $row) {
                    $cells = $xpath->query('./td | ./th', $row);
                    if ($cells->length === 3) {
                        $cells->item(0)->setAttribute('style', 'width: 32% !important; border: none !important; padding: 2pt 4pt !important; vertical-align: top !important;');
                        $cells->item(1)->setAttribute('style', 'width: 3% !important; text-align: center !important; border: none !important; padding: 2pt 0 !important; vertical-align: top !important;');
                        $cells->item(2)->setAttribute('style', 'width: 65% !important; border: none !important; padding: 2pt 4pt !important; vertical-align: top !important;');
                    }
                }
                continue;
            }

            // Kasus 6: Kotak Kesimpulan (MEMENUHI PERSYARATAN KESELAMATAN...)
            if (str_contains($text, 'MEMENUHI PERSYARATAN')) {
                $tbl->setAttribute('style', 'width: 100% !important; border-collapse: collapse !important; border: 1.5pt solid #000000 !important; background-color: #F9F9F9 !important; margin: 8pt 0 10pt 0 !important;');
                foreach ($xpath->query('.//td | .//th', $tbl) as $td) {
                    $td->setAttribute('style', 'border: 1.5pt solid #000000 !important; background-color: #F9F9F9 !important; padding: 8pt 14pt !important; text-align: center !important;');
                }
                continue;
            }

            // Kasus 7: Tabel Faktor K3 Permenaker (4 Kolom)
            if (str_contains($text, 'Permenaker') || str_contains($text, 'Standar Pengujian') || str_contains($text, 'Faktor Fisika')) {
                $tbl->setAttribute('style', 'width: 100% !important; border-collapse: collapse !important; border: 1px solid #000000 !important; margin: 8pt 0 10pt 0 !important;');
                $rows = $xpath->query('./tr', $tbl);
                foreach ($rows as $rIdx => $row) {
                    $cells = $xpath->query('./td | ./th', $row);
                    if ($cells->length === 4) {
                        $isHeader = ($rIdx === 0) || $row->getElementsByTagName('th')->length > 0;
                        $bg = $isHeader ? 'background-color: #F2F2F2 !important; font-weight: bold !important;' : '';
                        $cells->item(0)->setAttribute('style', 'width: 7% !important; text-align: center !important; border: 1px solid #000000 !important; padding: 4pt 6pt !important; ' . $bg);
                        $cells->item(1)->setAttribute('style', 'width: 41% !important; border: 1px solid #000000 !important; padding: 4pt 6pt !important; ' . $bg);
                        $cells->item(2)->setAttribute('style', 'width: 22% !important; text-align: center !important; border: 1px solid #000000 !important; padding: 4pt 6pt !important; ' . $bg);
                        $cells->item(3)->setAttribute('style', 'width: 30% !important; border: 1px solid #000000 !important; padding: 4pt 6pt !important; ' . $bg);
                    }
                }
                continue;
            }
        }

        // 3. Pastikan Nomor Surat dan Tanggal Surat resmi pada HTML terupdate (sinkron dengan data SuketK3)
        if ($suket && !empty($suket->nomor_surat)) {
            $paragraphs = $xpath->query('//p');
            foreach ($paragraphs as $p) {
                $pText = trim($p->textContent);
                if ((preg_match('/^\s*(Nomor\s*Surat|Nomor|No)[\s\.:]+/i', $pText) || str_contains($pText, 'SK-LK/BK3-SBY'))
                    && !str_contains($pText, 'Nomor Order')
                    && !str_contains($pText, 'Nomor 5 Tahun')
                    && !str_contains($pText, 'Permenaker')) {
                    $p->nodeValue = 'Nomor: ' . $suket->nomor_surat;
                    $p->setAttribute('style', 'text-align: center !important; font-size: 10.5pt !important; margin-bottom: 8pt !important;');
                    break;
                }
            }
        }

        if ($suket && !empty($suket->tanggal_surat)) {
            try {
                $formattedTanggal = Carbon::parse($suket->tanggal_surat)->locale('id')->translatedFormat('d F Y');
                $paragraphs = $xpath->query('//p');
                foreach ($paragraphs as $p) {
                    $pText = trim($p->textContent);
                    if (preg_match('/Pada\s+tanggal\s*:/i', $pText)) {
                        $p->nodeValue = 'Pada tanggal: ' . $formattedTanggal;
                        $p->setAttribute('style', 'text-align: center !important; font-size: 10.5pt !important; margin-bottom: 6pt !important;');
                        break;
                    }
                }
            } catch (\Throwable $e) {
                // Ignore date parse errors
            }
        }

        $cleanBody = $dom->saveHTML();
        if (preg_match('/<div>(.*)<\/div>/is', $cleanBody, $m)) {
            $cleanBody = $m[1];
        }

        return $cleanBody;
    }

    private function wrapInA4PreviewContainer(string $bodyHtml, SuketK3 $suket): string
    {
        $title = 'Surat Keterangan K3 - ' . htmlspecialchars($suket->perusahaan_nama ?: $suket->nomor_order);

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{$title}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background-color: #525659;
            padding: 24px 14px;
            display: flex;
            justify-content: center;
            min-height: 100vh;
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            line-height: 1.35;
            color: #000;
            -webkit-font-smoothing: antialiased;
        }
        .A4Page {
            background: #ffffff;
            width: 100%;
            max-width: 210mm;
            min-height: 297mm;
            padding: 20mm 20mm 25mm 20mm;
            margin: 0 auto;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.35);
            border-radius: 2px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 6pt;
        }
        td, th {
            vertical-align: top;
            font-size: 10.5pt;
        }
        p {
            margin: 0 0 4pt 0;
        }
        .A4Page > div > p, .A4Page > p {
            text-align: justify;
        }
        table p {
            margin-bottom: 2pt;
        }
        img {
            max-width: 65px !important;
            max-height: 65px !important;
            width: 65px !important;
            height: auto !important;
            display: block !important;
            margin: 0 auto !important;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .A4Page { width: 100% !important; max-width: 100% !important; padding: 0 !important; margin: 0 !important; box-shadow: none !important; }
        }
    </style>
</head>
<body>
    <div class="A4Page">
        {$bodyHtml}
    </div>
</body>
</html>
HTML;
    }

    private function wrapInA4PdfContainer(string $bodyHtml, SuketK3 $suket): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page { size: A4 portrait; margin: 15mm 20mm 20mm 20mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #ffffff;
            font-family: "Times New Roman", Times, serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #000;
        }
        table { border-collapse: collapse; width: 100%; margin-bottom: 6pt; }
        td, th { vertical-align: top; font-size: 10pt; }
        p { margin: 0 0 4pt 0; }
        body > div > p, body > p { text-align: justify; }
        table p { margin-bottom: 2pt; }
        img {
            max-width: 65px !important;
            max-height: 65px !important;
            width: 65px !important;
            height: auto !important;
            display: block !important;
            margin: 0 auto !important;
        }
    </style>
</head>
<body>
    {$bodyHtml}
</body>
</html>
HTML;
    }
}
