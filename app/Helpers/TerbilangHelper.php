<?php

namespace App\Helpers;

class TerbilangHelper
{
    protected static $angka = [
        '', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'
    ];

    public static function convert($n)
    {
        $n = intval($n);

        if ($n < 0) {
            return 'minus ' . static::convert(abs($n));
        }

        if ($n < 12) {
            return static::$angka[$n];
        }

        if ($n < 20) {
            return static::convert($n - 10) . ' belas';
        }

        if ($n < 100) {
            $puluh = floor($n / 10);
            $sisa = $n % 10;
            $hasil = ($puluh == 1 ? 'se' : static::convert($puluh)) . ' puluh';
            if ($sisa > 0) {
                $hasil .= ' ' . static::convert($sisa);
            }
            return $hasil;
        }

        if ($n < 200) {
            return 'seratus' . (static::convert($n - 100) ? ' ' . static::convert($n - 100) : '');
        }

        if ($n < 1000) {
            $ratus = floor($n / 100);
            $sisa = $n % 100;   
            $hasil = static::convert($ratus) . ' ratus';
            if ($sisa > 0) {
                $hasil .= ' ' . static::convert($sisa);
            }
            return $hasil;
        }

        if ($n < 2000) {
            return 'seribu' . (static::convert($n - 1000) ? ' ' . static::convert($n - 1000) : '');
        }

        if ($n < 1000000) {
            $ribu = floor($n / 1000);
            $sisa = $n % 1000;
            
            $hasil = static::convert($ribu) . ' ribu';
            
            if ($sisa > 0) {
                $hasil .= ' ' . static::convert($sisa);
            }
            return $hasil;
        }

        if ($n < 1000000000) {
            $juta = floor($n / 1000000);
            $sisa = $n % 1000000;
            
            $hasil = static::convert($juta) . ' juta';
            
            if ($sisa > 0) {
                $hasil .= ' ' . static::convert($sisa);
            }
            return $hasil;
        }

        if ($n < 1000000000000) {
            $miliar = floor($n / 1000000000);
            $sisa = $n % 1000000000;
            
            $hasil = static::convert($miliar) . ' miliar';
            
            if ($sisa > 0) {
                $hasil .= ' ' . static::convert($sisa);
            }
            return $hasil;
        }

        if ($n < 1000000000000000) {
            $triliun = floor($n / 1000000000000);
            $sisa = $n % 1000000000000;
            
            $hasil = static::convert($triliun) . ' triliun';
            
            if ($sisa > 0) {
                $hasil .= ' ' . static::convert($sisa);
            }
            return $hasil;
        }
        return 'angka terlalu besar';
    }
}