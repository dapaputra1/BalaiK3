<?php

namespace App\Support;

class TerbilangHelper
{
    public static function make(float|int|string|null $nilai): string
    {
        $nilai = (int) round((float) $nilai);
        if ($nilai <= 0) {
            return 'Nol Rupiah';
        }

        return ucwords(trim(self::spell($nilai))) . ' Rupiah';
    }

    private static function spell(int $nilai): string
    {
        $huruf = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        if ($nilai < 12) {
            return $huruf[$nilai];
        }

        if ($nilai < 20) {
            return self::spell($nilai - 10) . ' belas';
        }

        if ($nilai < 100) {
            return trim(self::spell((int) floor($nilai / 10)) . ' puluh ' . self::spell($nilai % 10));
        }

        if ($nilai < 200) {
            return trim('seratus ' . self::spell($nilai - 100));
        }

        if ($nilai < 1000) {
            return trim(self::spell((int) floor($nilai / 100)) . ' ratus ' . self::spell($nilai % 100));
        }

        if ($nilai < 2000) {
            return trim('seribu ' . self::spell($nilai - 1000));
        }

        if ($nilai < 1000000) {
            return trim(self::spell((int) floor($nilai / 1000)) . ' ribu ' . self::spell($nilai % 1000));
        }

        if ($nilai < 1000000000) {
            return trim(self::spell((int) floor($nilai / 1000000)) . ' juta ' . self::spell($nilai % 1000000));
        }

        if ($nilai < 1000000000000) {
            return trim(self::spell((int) floor($nilai / 1000000000)) . ' milyar ' . self::spell($nilai % 1000000000));
        }

        return trim(self::spell((int) floor($nilai / 1000000000000)) . ' triliun ' . self::spell($nilai % 1000000000000));
    }
}
