<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class SafeDocumentUpload
{
    private const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx'];
    private const ALLOWED_ORDER_PROOF_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png'];
    private const ALLOWED_PENGUJIAN_EXTENSIONS = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    private const BLOCKED_PAYMENT_PROOF_EXTENSIONS = [
        'php', 'phtml', 'phar', 'exe', 'dll', 'bat', 'cmd', 'ps1', 'sh', 'com', 'msi',
        'js', 'vbs', 'jar', 'scr', 'hta', 'html', 'htm', 'svg',
    ];

    public static function validateOrFail(UploadedFile $file, string $fieldName): void
    {
        self::validateWithAllowed($file, $fieldName, self::ALLOWED_EXTENSIONS, 'Format file harus PDF, DOC, atau DOCX.');
    }

    public static function validatePdfOrFail(UploadedFile $file, string $fieldName): void
    {
        self::validateWithAllowed($file, $fieldName, ['pdf'], 'Format file harus PDF.');
    }

    public static function validatePengujianOrFail(UploadedFile $file, string $fieldName): void
    {
        self::validateWithAllowed(
            $file,
            $fieldName,
            self::ALLOWED_PENGUJIAN_EXTENSIONS,
            'Format file harus PDF, DOC, DOCX, JPG, JPEG, atau PNG.'
        );
    }

    public static function validateOrderProofOrFail(UploadedFile $file, string $fieldName): void
    {
        self::validateWithAllowed(
            $file,
            $fieldName,
            self::ALLOWED_ORDER_PROOF_EXTENSIONS,
            'Format file harus PDF, JPG, JPEG, atau PNG.'
        );
    }

    public static function validatePaymentProofOrFail(UploadedFile $file, string $fieldName): void
    {
        $ext = strtolower((string) $file->getClientOriginalExtension());
        if ($ext === '' || in_array($ext, self::BLOCKED_PAYMENT_PROOF_EXTENSIONS, true)) {
            throw ValidationException::withMessages([
                $fieldName => 'Format file bukti pembayaran tidak diizinkan.',
            ]);
        }

        $path = $file->getRealPath();
        if (!$path || !is_file($path)) {
            throw ValidationException::withMessages([
                $fieldName => 'File upload tidak valid.',
            ]);
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw ValidationException::withMessages([
                $fieldName => 'File tidak dapat dibaca.',
            ]);
        }

        if (in_array($ext, ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'webp'], true)) {
            $detected = self::assertMagicSignature($ext, $content, $path, $fieldName);
            self::assertNoSuspiciousPayload($ext, $content, $fieldName);

            if ($ext === 'docx' || ($ext === 'doc' && $detected === 'docx')) {
                self::assertSafeDocxArchive($path, $fieldName);
            }

            return;
        }

        self::assertNoSuspiciousPayload($ext, $content, $fieldName);
    }

    private static function validateWithAllowed(UploadedFile $file, string $fieldName, array $allowedExtensions, string $formatMessage): void
    {
        $ext = strtolower((string) $file->getClientOriginalExtension());
        if (!in_array($ext, $allowedExtensions, true)) {
            throw ValidationException::withMessages([
                $fieldName => $formatMessage,
            ]);
        }

        $path = $file->getRealPath();
        if (!$path || !is_file($path)) {
            throw ValidationException::withMessages([
                $fieldName => 'File upload tidak valid.',
            ]);
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw ValidationException::withMessages([
                $fieldName => 'File tidak dapat dibaca.',
            ]);
        }

        $detected = self::assertMagicSignature($ext, $content, $path, $fieldName);
        self::assertNoSuspiciousPayload($ext, $content, $fieldName);

        if ($ext === 'docx' || ($ext === 'doc' && $detected === 'docx')) {
            self::assertSafeDocxArchive($path, $fieldName);
        }
    }

    private static function assertMagicSignature(string $ext, string $content, string $path, string $fieldName): string
    {
        $head = substr($content, 0, 1024);

        if ($ext === 'pdf') {
            if (strpos($head, '%PDF-') === false) {
                throw ValidationException::withMessages([
                    $fieldName => 'File PDF tidak valid.',
                ]);
            }
            return 'pdf';
        }

        if ($ext === 'doc') {
            $oleMagic = hex2bin('D0CF11E0A1B11AE1');
            $isOle = $oleMagic !== false && substr($content, 0, 8) === $oleMagic;
            $isZipDocx = substr($content, 0, 2) === "PK";
            if (!$isOle) {
                $trimHead = strtolower(ltrim($head));
                $isRtf = str_starts_with($trimHead, '{\\rtf');
                $isHtml = str_starts_with($trimHead, '<!doctype html') || str_starts_with($trimHead, '<html');
                $isXmlWord = str_starts_with($trimHead, '<?xml');
                // Keep DOC validation permissive for legacy generators:
                // accept text-based/zip variants and fallback to extension-based DOC.
                if (!$isZipDocx && !$isRtf && !$isHtml && !$isXmlWord) {
                    return 'doc';
                }
            }
            if ($isZipDocx) {
                return 'docx';
            }
            return 'doc';
        }

        if ($ext === 'docx') {
            if (substr($content, 0, 2) !== "PK") {
                throw ValidationException::withMessages([
                    $fieldName => 'File DOCX tidak valid.',
                ]);
            }
            return 'docx';
        }

        if (in_array($ext, ['jpg', 'jpeg'], true)) {
            $jpgMagic = substr($content, 0, 2) === "\xFF\xD8";
            if (!$jpgMagic || !self::isValidImageFile($path)) {
                throw ValidationException::withMessages([
                    $fieldName => 'File JPG/JPEG tidak valid.',
                ]);
            }
            return 'jpg';
        }

        if ($ext === 'png') {
            $pngMagic = substr($content, 0, 8) === "\x89PNG\x0D\x0A\x1A\x0A";
            if (!$pngMagic || !self::isValidImageFile($path)) {
                throw ValidationException::withMessages([
                    $fieldName => 'File PNG tidak valid.',
                ]);
            }
            return 'png';
        }

        if ($ext === 'webp') {
            $webpMagic = substr($content, 0, 4) === 'RIFF' && substr($content, 8, 4) === 'WEBP';
            if (!$webpMagic || !self::isValidImageFile($path)) {
                throw ValidationException::withMessages([
                    $fieldName => 'File WEBP tidak valid.',
                ]);
            }
            return 'webp';
        }

        return $ext;
    }

    private static function assertNoSuspiciousPayload(string $ext, string $content, string $fieldName): void
    {
        // Skip keyword scanning for binary office docs to avoid false positive.
        if ($ext === 'docx') {
            return;
        }
        if ($ext === 'doc') {
            return;
        }

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return;
        }

        $scan = strtolower(substr($content, 0, 2 * 1024 * 1024));
        $patterns = [
            '<?php',
            '<script',
            'powershell -',
            'wscript.shell',
            'cmd.exe',
            'mshta',
        ];

        foreach ($patterns as $pattern) {
            if (strpos($scan, $pattern) !== false) {
                throw ValidationException::withMessages([
                    $fieldName => 'File terindikasi berbahaya.',
                ]);
            }
        }
    }

    private static function assertSafeDocxArchive(string $path, string $fieldName): void
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw ValidationException::withMessages([
                $fieldName => 'File DOCX tidak dapat dibuka.',
            ]);
        }

        $dangerousExt = ['.exe', '.dll', '.js', '.vbs', '.bat', '.cmd', '.ps1', '.com', '.msi', '.hta', '.jar', '.scr'];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = strtolower((string) $zip->getNameIndex($i));
            if ($name === '') {
                continue;
            }

            if (str_contains($name, '../') || str_contains($name, '..\\')) {
                $zip->close();
                throw ValidationException::withMessages([
                    $fieldName => 'Struktur file DOCX tidak valid.',
                ]);
            }

            foreach ($dangerousExt as $ext) {
                if (str_ends_with($name, $ext)) {
                    $zip->close();
                    throw ValidationException::withMessages([
                        $fieldName => 'DOCX mengandung file terlarang.',
                    ]);
                }
            }

            if (str_contains($name, 'vbaproject.bin')) {
                $zip->close();
                throw ValidationException::withMessages([
                    $fieldName => 'DOCX mengandung macro dan ditolak demi keamanan.',
                ]);
            }
        }

        $zip->close();
    }

    private static function isValidImageFile(string $path): bool
    {
        $imageInfo = @getimagesize($path);
        return is_array($imageInfo) && !empty($imageInfo[0]) && !empty($imageInfo[1]);
    }
}
