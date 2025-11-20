<?php

namespace App\Helpers;

class AuditDisplayHelper
{
    public static function getDisplayName($audit)
    {
        $modelName = class_basename($audit->auditable_type);
        $id = $audit->auditable_id;
        
        $data = $audit->new_values ?? $audit->old_values ?? [];
        
        switch($modelName) {
            case 'Penghuni':
                if (isset($data['nama'])) {
                    return "Penghuni: {$data['nama']}";
                }
                
                try {
                    $penghuni = \App\Models\Penghuni::find($id);
                    if ($penghuni && $penghuni->nama) {
                        return "Penghuni: {$penghuni->nama}";
                    }
                } catch (\Exception $e) {
                }
                
                return "Penghuni (ID: {$id})";
                
            case 'Kontrak':
                $penghuniName = $audit->penghuni_name ?? null;
                $unitCode = $audit->unit_code ?? null;
                
                if (!$penghuniName || !$unitCode) {
                    try {
                        $kontrak = \App\Models\Kontrak::with(['penghuni', 'unit'])->find($id);
                        if ($kontrak) {
                            $penghuniName = $penghuniName ?? ($kontrak->penghuni->nama ?? null);
                            $unitCode = $unitCode ?? ($kontrak->unit->kode_unit ?? null);
                        }
                    } catch (\Exception $e) {
                    }
                }
                
                $parts = [];
                if ($penghuniName) {
                    $parts[] = "Penghuni: {$penghuniName}";
                }
                if ($unitCode) {
                    $parts[] = "Unit: {$unitCode}";
                }
                
                if (!empty($parts)) {
                    return "Kontrak (" . implode(' | ', $parts) . ")";
                }
                
                return "Kontrak (ID: {$id})";
                
            case 'Keluarga':
                $namaKeluarga = $data['nama'] ?? null;
                $penghuniName = $audit->penghuni_name ?? null;
                
                if (!$namaKeluarga || !$penghuniName) {
                    try {
                        $keluarga = \App\Models\Keluarga::with('penghuni')->find($id);
                        if ($keluarga) {
                            $namaKeluarga = $namaKeluarga ?? $keluarga->nama;
                            if ($keluarga->penghuni) {
                                $penghuniName = $penghuniName ?? $keluarga->penghuni->nama;
                            }
                        }
                    } catch (\Exception $e) {
                    }
                }
                
                $parts = [];
                if ($namaKeluarga) {
                    $parts[] = $namaKeluarga;
                }
                if ($penghuniName) {
                    $parts[] = "Penghuni: {$penghuniName}";
                }
                
                if (!empty($parts)) {
                    return "Keluarga: " . implode(' | ', $parts);
                }
                
                return "Keluarga (ID: {$id})";
                
            case 'Unit':
                if (isset($data['kode_unit'])) {
                    return "Unit: {$data['kode_unit']}";
                }
                
                try {
                    $unit = \App\Models\Unit::find($id);
                    if ($unit && $unit->kode_unit) {
                        return "Unit: {$unit->kode_unit}";
                    }
                } catch (\Exception $e) {
                }
                
                return "Unit (ID: {$id})";
                
            case 'Blacklist':
                if (isset($data['nama'])) {
                    return "Blacklist: {$data['nama']}";
                }
                
                try {
                    $blacklist = \App\Models\Blacklist::find($id);
                    if ($blacklist && $blacklist->nama) {
                        return "Blacklist: {$blacklist->nama}";
                    }
                } catch (\Exception $e) {
                }
                
                return "Blacklist (ID: {$id})";
                
            case 'Disperkim':
                if (isset($data['nama'])) {
                    return "Staff: {$data['nama']}";
                }
                
                try {
                    $staff = \App\Models\Disperkim::find($id);
                    if ($staff && $staff->nama) {
                        return "Staff: {$staff->nama}";
                    }
                } catch (\Exception $e) {
                }
                
                return "Staff (ID: {$id})";
                
            case 'Tagihan':
                $penghuniName = $audit->penghuni_name ?? null;
                
                if ($penghuniName) {
                    return "Tagihan: Penghuni {$penghuniName}";
                }
                
                return "Tagihan (ID: {$id})";
                
            case 'Pembayaran':
                $penghuniName = $audit->penghuni_name ?? null;
                $amount = $data['jumlah_bayar'] ?? null;
                
                if ($penghuniName) {
                    return "Pembayaran: Penghuni {$penghuniName}";
                } elseif ($amount) {
                    $formatted = number_format($amount, 0, ',', '.');
                    return "Pembayaran: Rp {$formatted}";
                }
                
                return "Pembayaran (ID: {$id})";
                
            default:
                return "{$modelName} (ID: {$id})";
        }
    }
    
    public static function getSummary($audit)
    {
        $modelName = class_basename($audit->auditable_type);
        $event = $audit->event;
        $data = $audit->new_values ?? $audit->old_values ?? [];
        
        if (property_exists($audit, 'batch_description') && $audit->batch_description) {
            return $audit->batch_description;
        }
        
        if (property_exists($audit, 'batch_audits') && $audit->batch_audits && $audit->batch_audits->count() > 1) {
            return self::analyzeBatchOperation($audit->batch_audits);
        }
        
        return self::getSingleOperationSummary($modelName, $event, $data, $audit);
    }
    
    private static function getSingleOperationSummary($modelName, $event, $data, $audit)
    {
        switch($modelName) {
            case 'Penghuni':
                $nama = $data['nama'] ?? 'N/A';
                if ($event === 'created') return "Menambahkan penghuni baru: {$nama}";
                if ($event === 'updated') return "Memperbarui data penghuni: {$nama}";
                if ($event === 'deleted') return "Menghapus penghuni: {$nama}";
                break;
                
            case 'Kontrak':
                $penghuniName = $audit->penghuni_name ?? 'N/A';
                $unitCode = $audit->unit_code ?? 'N/A';
                
                if ($event === 'created') {
                    return "Membuat kontrak baru: {$penghuniName} di unit {$unitCode}";
                }
                if ($event === 'updated') {
                    if (isset($data['status']) && $data['status'] === 'keluar') {
                        return "Mengakhiri kontrak: {$penghuniName} - Unit {$unitCode}";
                    }
                    return "Memperbarui kontrak: {$penghuniName} - Unit {$unitCode}";
                }
                break;
                
            case 'Keluarga':
                $penghuniName = $audit->penghuni_name ?? 'N/A';
                $namaKeluarga = $data['nama'] ?? 'N/A';
                
                if ($event === 'created') return "Menambah keluarga {$namaKeluarga} untuk {$penghuniName}";
                if ($event === 'updated') return "Memperbarui data keluarga: {$namaKeluarga}";
                if ($event === 'deleted') return "Menghapus keluarga: {$namaKeluarga}";
                break;
                
            case 'Unit':
                $kodeUnit = $data['kode_unit'] ?? 'N/A';
                if ($event === 'created') return "Menambah unit baru: {$kodeUnit}";
                if ($event === 'updated') return "Memperbarui unit: {$kodeUnit}";
                break;
                
            case 'Blacklist':
                $nama = $data['nama'] ?? 'N/A';
                if ($event === 'created') return "Memasukkan {$nama} ke daftar hitam";
                if ($event === 'updated') {
                    if (isset($data['status']) && $data['status'] === 'aktif') {
                        return "Mengaktifkan kembali {$nama}";
                    }
                    return "Memperbarui blacklist: {$nama}";
                }
                break;
        }
        
        if ($event === 'created') return "Menambah data baru";
        if ($event === 'updated') return "Memperbarui data";
        if ($event === 'deleted') return "Menghapus data";
        return ucfirst($event);
    }
    
    private static function analyzeBatchOperation($audits)
    {
        $penghuniCount = 0;
        $kontrakCount = 0;
        $keluargaCount = 0;
        $unitCount = 0;
        $blacklistCount = 0;
        
        $penghuni = null;
        $kontrak = null;
        $unit = null;
        $keluargaList = [];
        
        $createdCount = 0;
        $updatedCount = 0;
        $deletedCount = 0;
        
        foreach ($audits as $audit) {
            $modelName = class_basename($audit->auditable_type);
            
            if ($audit->event === 'created') $createdCount++;
            if ($audit->event === 'updated') $updatedCount++;
            if ($audit->event === 'deleted') $deletedCount++;
            
            if ($modelName === 'Penghuni') {
                $penghuniCount++;
                if (!$penghuni) $penghuni = $audit;
            }
            if ($modelName === 'Kontrak') {
                $kontrakCount++;
                if (!$kontrak) $kontrak = $audit;
            }
            if ($modelName === 'Keluarga') {
                $keluargaCount++;
                $keluargaList[] = $audit;
            }
            if ($modelName === 'Unit') {
                $unitCount++;
                if (!$unit) $unit = $audit;
            }
            if ($modelName === 'Blacklist') {
                $blacklistCount++;
            }
        }
        
        if ($penghuniCount >= 3 && $kontrakCount >= 3 && $createdCount >= 3) {
            return "Import data Excel: {$penghuniCount} penghuni, {$kontrakCount} kontrak" . 
                   ($keluargaCount > 0 ? ", {$keluargaCount} keluarga" : "");
        }
        
        if ($createdCount >= 1 && $updatedCount >= 1 && $kontrakCount === 1 && $penghuniCount >= 1 && $unitCount >= 1) {
            if ($kontrak && $kontrak->event === 'created') {
                $namaPenghuni = self::extractPenghuniName($kontrak, $penghuni);
                $unitCode = self::extractUnitCode($kontrak, $unit);
                
                if ($namaPenghuni && $unitCode) {
                    return "{$namaPenghuni} menempati unit {$unitCode}";
                } elseif ($unitCode) {
                    return "Kontrak baru dibuat untuk unit {$unitCode}";
                }
            }
        }
        
        if ($createdCount > 0 && $penghuniCount === 1 && $kontrakCount === 1 && $keluargaCount > 0) {
            if ($penghuni && $kontrak) {
                $namaPenghuni = $penghuni->new_values['nama'] ?? null;
                $unitCode = self::extractUnitCode($kontrak, $unit);
                
                if ($namaPenghuni && $unitCode) {
                    return "Menambah penghuni baru {$namaPenghuni} ke unit {$unitCode} beserta {$keluargaCount} anggota keluarga";
                } elseif ($namaPenghuni) {
                    return "Menambah penghuni baru {$namaPenghuni} beserta {$keluargaCount} anggota keluarga";
                }
            }
        }

        if ($createdCount > 0 && $penghuniCount === 1 && $kontrakCount === 1 && $keluargaCount === 0) {
            if ($penghuni && $kontrak) {
                $namaPenghuni = $penghuni->new_values['nama'] ?? null;
                $unitCode = self::extractUnitCode($kontrak, $unit);
                
                if ($namaPenghuni && $unitCode) {
                    return "Menambah penghuni baru {$namaPenghuni} ke unit {$unitCode}";
                } elseif ($namaPenghuni) {
                    return "Menambah penghuni baru {$namaPenghuni}";
                }
            }
        }
        
        if ($updatedCount >= 2 && $kontrakCount === 1 && ($penghuniCount >= 1 || $unitCount >= 1)) {
            if ($kontrak && isset($kontrak->new_values['status']) && $kontrak->new_values['status'] === 'keluar') {
                $namaPenghuni = self::extractPenghuniName($kontrak, $penghuni);
                $unitCode = self::extractUnitCode($kontrak, $unit);
                
                if ($namaPenghuni && $unitCode) {
                    return "Mengakhiri kontrak {$namaPenghuni} - Unit {$unitCode}";
                } elseif ($namaPenghuni) {
                    return "Mengakhiri kontrak {$namaPenghuni}";
                } elseif ($unitCode) {
                    return "Mengakhiri kontrak di unit {$unitCode}";
                }
            }
        }

        if ($keluargaCount > 0 && $penghuniCount === 0 && $kontrakCount === 0) {
            $firstKeluarga = $keluargaList[0] ?? null;
            if ($firstKeluarga) {
                $penghuniName = $firstKeluarga->penghuni_name ?? null;
                
                if (!$penghuniName && isset($firstKeluarga->new_values['penghuni_id'])) {
                    try {
                        $penghuniModel = \App\Models\Penghuni::find($firstKeluarga->new_values['penghuni_id']);
                        if ($penghuniModel) {
                            $penghuniName = $penghuniModel->nama;
                        }
                    } catch (\Exception $e) {
                    }
                }
                
                if ($penghuniName) {
                    if ($keluargaCount === 1) {
                        $namaKeluarga = $firstKeluarga->new_values['nama'] ?? 'anggota keluarga';
                        return "Menambah keluarga {$namaKeluarga} untuk {$penghuniName}";
                    } else {
                        return "Menambah {$keluargaCount} anggota keluarga untuk {$penghuniName}";
                    }
                }
            }
            
            return $keluargaCount === 1 ? "Menambah 1 anggota keluarga" : "Menambah {$keluargaCount} anggota keluarga";
        }
        
        if ($blacklistCount > 0 && $penghuniCount === 1 && $kontrakCount >= 1) {
            if ($penghuni) {
                $namaPenghuni = $penghuni->old_values['nama'] ?? $penghuni->new_values['nama'] ?? null;
                if ($namaPenghuni) {
                    return "Memasukkan {$namaPenghuni} ke daftar hitam";
                }
            }
        }
        
        if ($createdCount > 0) {
            $parts = [];
            if ($penghuniCount > 0) $parts[] = "{$penghuniCount} penghuni";
            if ($kontrakCount > 0) $parts[] = "{$kontrakCount} kontrak";
            if ($keluargaCount > 0) $parts[] = "{$keluargaCount} keluarga";
            if ($unitCount > 0) $parts[] = "{$unitCount} unit";
            
            if (!empty($parts)) {
                return "Menambahkan " . implode(', ', $parts);
            }
        }
        
        if ($updatedCount > 0) {
            $parts = [];
            if ($penghuniCount > 0) $parts[] = "{$penghuniCount} penghuni";
            if ($kontrakCount > 0) $parts[] = "{$kontrakCount} kontrak";
            if ($keluargaCount > 0) $parts[] = "{$keluargaCount} keluarga";
            if ($unitCount > 0) $parts[] = "{$unitCount} unit";
            
            if (!empty($parts)) {
                return "Memperbarui " . implode(', ', $parts);
            }
        }
        
        $total = $audits->count();
        return "Operasi batch: {$total} perubahan";
    }
    
    private static function extractUnitCode($kontrak, $unit)
    {
        $unitCode = $kontrak->unit_code ?? null;
        
        if (!$unitCode && $unit) {
            $unitCode = $unit->new_values['kode_unit'] ?? $unit->old_values['kode_unit'] ?? null;
        }
        
        if (!$unitCode && isset($kontrak->new_values['unit_id'])) {
            try {
                $unitModel = \App\Models\Unit::find($kontrak->new_values['unit_id']);
                if ($unitModel) {
                    $unitCode = $unitModel->kode_unit;
                }
            } catch (\Exception $e) {
            }
        }
        
        return $unitCode;
    }

    private static function extractPenghuniName($kontrak, $penghuni)
    {
        $namaPenghuni = $kontrak->penghuni_name ?? null;
        
        if (!$namaPenghuni && $penghuni) {
            $namaPenghuni = $penghuni->new_values['nama'] ?? $penghuni->old_values['nama'] ?? null;
        }
        
        if (!$namaPenghuni && isset($kontrak->new_values['penghuni_id'])) {
            try {
                $penghuniModel = \App\Models\Penghuni::find($kontrak->new_values['penghuni_id']);
                if ($penghuniModel) {
                    $namaPenghuni = $penghuniModel->nama;
                }
            } catch (\Exception $e) {
            }
        }
        
        return $namaPenghuni;
    }
}