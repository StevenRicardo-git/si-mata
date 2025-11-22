<?php

namespace App\Imports;

use App\Models\Penghuni;
use App\Models\Kontrak;
use App\Models\Unit;
use App\Models\Keluarga;
use App\Services\AuditBatchService;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Throwable;

class PenghuniImport implements 
    ToCollection, 
    WithStartRow, 
    SkipsOnError,
    SkipsOnFailure
{
    public function startRow(): int
    {
        return 2;
    }
    
    public function onError(Throwable $e)
    {

    }
    
    public function onFailure(...$failures)
    {

    }
    
    public function collection(Collection $rows)
    {
        $firstRow = $rows->first();
        if ($firstRow && $this->isSipDocument($firstRow)) {
            return;
        }

        try {
            DB::beginTransaction();
            
            $totalRows = $rows->count();
            AuditBatchService::start("Import Excel: {$totalRows} baris data penghuni");

            foreach ($rows as $rowIndex => $row) {
                try {
                    $this->processRow($row, $rowIndex);
                } catch (\Exception $e) {
                    continue;
                }
            }

            AuditBatchService::end();
            DB::commit();
            
        } catch (\Exception $e) {
            AuditBatchService::end();
            DB::rollBack();
            throw $e;
        }
    }

    private function processRow($row, $rowIndex)
    {
        if ($this->isSipDocumentRow($row)) {
            return;
        }
        
        $kodeUnit = $row[0] ?? null;
        $nama = $row[2] ?? null;
        $noKtp = $row[12] ?? null;

        if ($nama && stripos(trim($nama), 'hunian kosong') !== false) {
            return;
        }
        
        if (empty($noKtp) || strlen((string)$noKtp) != 16) {
            return;
        }
        
        $noKtp = (string) $noKtp;
        
        if (empty($nama) || empty($kodeUnit)) {
            return;
        }
        
        $kodeUnit = trim(strtoupper(str_replace(' ', '', (string) $kodeUnit)));
        
        $ttl = $row[10] ?? null;
        $tempatLahir = null;
        $tanggalLahir = null;

        if ($ttl) {
            $ttlParts = explode(',', $ttl);
            if (count($ttlParts) == 2) {
                $tempatLahir = trim($ttlParts[0]);
                $tanggalLahir = $this->parseDate(trim($ttlParts[1]));
            }
        }
        
        $penghuni = Penghuni::updateOrCreate(
            ['nik' => $noKtp],
            [
                'nama' => trim((string) $nama),
                'tempat_lahir' => $tempatLahir,
                'tanggal_lahir' => $tanggalLahir,
                'pekerjaan' => $row[11] ?? null,
                'alamat_ktp' => $row[13] ?? null,
            ]
        );
        
        $unit = Unit::firstOrCreate(
            ['kode_unit' => $kodeUnit],
            [
                'tipe' => $this->getUnitType($kodeUnit),
                'status' => 'terisi',
                'harga_sewa' => 0,
            ]
        );
        
        $keringananText = $row[5] ?? null;
        
        if (empty($keringananText)) {
            $keringanan = 'tidak';
        } else {
            $keringananText = strtolower(trim((string) $keringananText));
            
            if (in_array($keringananText, ['dapat', 'ya', 'y', '1', 'true', 'iya', 'dpt'])) {
                $keringanan = 'dapat';
            } elseif (in_array($keringananText, ['normal', 'n'])) {
                $keringanan = 'normal';
            } else {
                $keringanan = 'tidak';
            }
        }
        
        $tanggalMasuk = $this->parseDate($row[3] ?? null);
        $tanggalKeluar = $this->parseDate($row[4] ?? null);
        
        $noSip = isset($row[6]) && !empty($row[6]) ? trim((string)$row[6]) : null;
        if ($noSip && (strpos($noSip, '................') !== false || strpos($noSip, '….') !== false)) {
            $noSip = null;
        }

        $noSps = isset($row[8]) && !empty($row[8]) ? trim((string)$row[8]) : null;
        if ($noSps && (strpos($noSps, '..................') !== false || strpos($noSps, '….') !== false)) {
            $noSps = null;
        }
        
        $tanggalSip = null;
        $tanggalSps = null;
        
        if ($tanggalMasuk) {
            if ($noSip) {
                $tanggalSip = $tanggalMasuk;
            }
            
            if ($noSps) {
                $tanggalSps = $tanggalMasuk;
            }
        }
        
        if (!$tanggalMasuk) {
            return;
        }
        
        if (!$tanggalKeluar) {
            $tanggalKeluar = Carbon::parse($tanggalMasuk)->addYears(3)->subDay();
        }
        
        $statusKontrak = 'aktif';
        if ($tanggalKeluar) {
            $keluar = Carbon::parse($tanggalKeluar);
            if ($keluar->isPast()) {
                $statusKontrak = 'keluar';
            }
        }
        
        $blok = substr($kodeUnit, 0, 1);
        $lantaiInt = null;
        
        if (in_array($blok, ['A', 'B', 'C'])) {
            $lantaiInt = (int)substr($kodeUnit, 1, 1);
        } elseif ($blok === 'D') {
            $unitNum = (int)substr($kodeUnit, 1);
            if ($unitNum >= 101 && $unitNum <= 110) $lantaiInt = 1;
            elseif ($unitNum >= 211 && $unitNum <= 226) $lantaiInt = 2;
            elseif ($unitNum >= 327 && $unitNum <= 342) $lantaiInt = 3;
        }
        
        $tarifKeringanan = $this->getTarifKeringanan();
        $nominalKeringanan = 0;
        
        if ($lantaiInt && isset($tarifKeringanan[$blok][$lantaiInt][$keringanan])) {
            $nominalKeringanan = $tarifKeringanan[$blok][$lantaiInt][$keringanan];
        }
        
        $tarifAir = 0;
        if (in_array($blok, ['A', 'B', 'C'])) {
            $tarifAir = 60000; 
        } elseif ($blok === 'D') {
            $tarifAir = 70000; 
        }

        $kontrak = Kontrak::updateOrCreate(
            [
                'penghuni_id' => $penghuni->id,
                'status' => 'aktif',
            ],
            [
                'unit_id' => $unit->id,
                'tanggal_masuk' => $tanggalMasuk,
                'tanggal_keluar' => $tanggalKeluar,
                'status' => $statusKontrak,
                'keringanan' => $keringanan,
                'nominal_keringanan' => $nominalKeringanan,
                'tarif_air' => $tarifAir,
                'no_sip' => $noSip,
                'tanggal_sip' => $tanggalSip,
                'no_sps' => $noSps,
                'tanggal_sps' => $tanggalSps,
            ]
        );

        $penghuni->status = $statusKontrak;
        $penghuni->save();

        if ($statusKontrak === 'aktif') {
            $unit->status = 'terisi';
            $unit->save();
        }

        if (isset($row[14]) && !empty($row[14])) {
            $namaPasangan = trim($row[14]);
            $nikPasangan = isset($row[15]) && !empty($row[15]) ? trim($row[15]) : null;
            $umurPasangan = null;
            
            if (isset($row[16]) && !empty($row[16])) {
                $umurValue = $row[16];
                if (is_numeric($umurValue) && strlen((string)$umurValue) <= 3 && $umurValue < 150) {
                    $umurPasangan = (int)$umurValue;
                }
            }
            
            $hubunganPasangan = 'istri';
            if (isset($row[17]) && !empty($row[17])) {
                $hubungan = strtolower(trim($row[17]));
                if (in_array($hubungan, ['istri', 'suami'])) {
                    $hubunganPasangan = $hubungan;
                }
            }
            
            if ($nikPasangan && strlen($nikPasangan) != 16) {
                $nikPasangan = null;
            }

            $jenisKelaminPasangan = ($hubunganPasangan === 'istri') ? 'perempuan' : 'laki-laki';
            
            Keluarga::updateOrCreate(
                [
                    'penghuni_id' => $penghuni->id,
                    'nama' => $namaPasangan,
                    'hubungan' => $hubunganPasangan,
                ],
                [
                    'nik' => $nikPasangan,
                    'umur' => $umurPasangan,
                    'jenis_kelamin' => $jenisKelaminPasangan,
                ]
            );
        }

        $startCol = 18; 
        $maxMembers = 15; 
        
        for ($i = 0; $i < $maxMembers; $i++) {
            $namaIndex = $startCol + ($i * 4);
            $nikIndex = $namaIndex + 1;
            $umurIndex = $namaIndex + 2;
            $hubunganIndex = $namaIndex + 3;
            
            if (!isset($row[$namaIndex]) || empty($row[$namaIndex])) {
                continue;
            }
            
            $namaAnak = trim($row[$namaIndex]);

            if (is_numeric($namaAnak)) {
                continue;
            }

            $namaLower = strtolower($namaAnak);
            if (strlen($namaAnak) < 2 || in_array($namaLower, ['anak', 'istri', 'suami', 'tidak', 'dapat'])) {
                continue;
            }
            
            $nikAnak = isset($row[$nikIndex]) && !empty($row[$nikIndex]) ? trim($row[$nikIndex]) : null;
            if ($nikAnak && strlen($nikAnak) != 16) {
                $nikAnak = null;
            }
            
            $umurAnak = null;
            if (isset($row[$umurIndex]) && !empty($row[$umurIndex])) {
                $umurValue = $row[$umurIndex];
                if (is_numeric($umurValue)) {
                    $umurLength = strlen((string)$umurValue);
                    if ($umurLength <= 3 && $umurValue > 0 && $umurValue < 150) {
                        $umurAnak = (int)$umurValue;
                    }
                }
            }
            
            $hubunganAnak = 'anak';
            if (isset($row[$hubunganIndex]) && !empty($row[$hubunganIndex])) {
                $hubungan = strtolower(trim($row[$hubunganIndex]));
                if ($hubungan == 'anak') {
                    $hubunganAnak = 'anak';
                }
            }
            
            Keluarga::updateOrCreate(
                [
                    'penghuni_id' => $penghuni->id,
                    'nama' => $namaAnak,
                    'hubungan' => $hubunganAnak,
                ],
                [
                    'nik' => $nikAnak,
                    'umur' => $umurAnak,
                ]
            );
        }
    }

    private function isSipDocument($row): bool
    {
        $sipKeywords = [
            'PEMERINTAH',
            'SURAT IJIN PENGHUNIAN',
            'KOTA TEGAL',
            'DINAS',
            'SIP'
        ];
        
        $rowStr = strtoupper(implode(' ', $row->toArray()));
        
        foreach ($sipKeywords as $keyword) {
            if (str_contains($rowStr, $keyword)) {
                return true;
            }
        }
        
        return false;
    }

    private function isSipDocumentRow($row): bool
    {
        $cellValue = $row[2] ?? '';
        $sipPatterns = [
            'SURAT IJIN',
            'Nomor :',
            'Berdasarkan pada',
            'Tempat / tgl lahir',
            'Pekerjaan',
            'Alamat KTP',
            'Telah diserahkan',
            'DAFTAR KELUARGA',
            'HUBUNGAN DENGAN PENYEWA'
        ];
        
        foreach ($sipPatterns as $pattern) {
            if (str_contains((string)$cellValue, $pattern)) {
                return true;
            }
        }
        
        return false;
    }

    private function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }

        if (is_string($date)) {
            $date = trim($date);
            if ($date === '' || $date === '-' || $date === 'NULL' || strtolower($date) === 'null') {
                return null;
            }
        }
        
        try {
            if (is_numeric($date) && $date > 100) {
                try {
                    $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date);
                    $parsed = Carbon::instance($dateObj);
                    
                    if ($parsed->year >= 1900 && $parsed->year <= 2100) {
                        return $parsed->startOfDay();
                    }
                } catch (\Exception $e) {
                    try {
                        $unix_date = ($date - 25569) * 86400;
                        $parsed = Carbon::createFromTimestamp($unix_date);
                        
                        if ($parsed->year >= 1900 && $parsed->year <= 2100) {
                            return $parsed->startOfDay();
                        }
                    } catch (\Exception $e2) {
                    }
                }
            }

            $dateStr = trim((string)$date);
     
            if (str_starts_with($dateStr, '=')) {
                return null;
            }

            if (is_numeric($dateStr) && (int)$dateStr < 100) {
                return null;
            }

            $monthTranslation = [
                'Januari' => 'January', 'Februari' => 'February', 'Maret' => 'March',
                'April' => 'April', 'Mei' => 'May', 'Juni' => 'June',
                'Juli' => 'July', 'Agustus' => 'August', 'September' => 'September',
                'Oktober' => 'October', 'November' => 'November', 'Desember' => 'December',
            ];
            
            foreach ($monthTranslation as $indo => $eng) {
                $dateStr = str_ireplace($indo, $eng, $dateStr);
            }
            
            $formats = [
                'd F Y', 'j F Y',
                'd M Y', 'j M Y',
                'd-F-Y', 'j-F-Y',
                'd-M-Y', 'j-M-Y',
                'd/F/Y', 'j/F/Y',
                'd/M/Y', 'j/M/Y',
                'd-m-Y', 'd/m/Y', 'd.m.Y',
                'j-m-Y', 'j/m/Y', 'j.m.Y',
                'Y-m-d', 'Y/m/d',
                'd F Y H:i:s', 'j F Y H:i:s',
                'd M Y H:i:s', 'j M Y H:i:s',
            ];
            
            foreach ($formats as $format) {
                try {
                    $parsed = Carbon::createFromFormat($format, $dateStr);
                    if ($parsed && $parsed->year >= 1900 && $parsed->year <= 2100) {
                        return $parsed->startOfDay();
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
 
            try {
                $parsed = Carbon::parse($dateStr);
                if ($parsed && $parsed->year >= 1900 && $parsed->year <= 2100) {
                    return $parsed->startOfDay();
                }
            } catch (\Exception $e) {
            }
            
            return null;
            
        } catch (\Exception $e) {
            return null;
        }
    }
    
    private function getUnitType($kodeUnit)
    {
        $blok = substr($kodeUnit, 0, 1);
        
        if (in_array($blok, ['A', 'B', 'C'])) {
            return 'Rumah Susun Sederhana';
        } elseif ($blok === 'D') {
            return 'MBR Tegalsari';
        } elseif ($blok === 'P') {
            return 'Prototipe Tegalsari';
        }
        
        return 'Unknown';
    }
    
    private function getTarifKeringanan()
    {
        return config('tarif.keringanan');
    }
}