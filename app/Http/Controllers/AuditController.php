<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    public function index()
    {
        $auditsQuery = Audit::with('user')
                            ->latest()
                            ->orderBy('id', 'desc');
        $allAudits = $auditsQuery->get();
        
        $allAudits = $allAudits->map(function ($audit) {
            $auditableType = class_basename($audit->auditable_type);
            
            if ($audit->tags) {
                $tags = json_decode($audit->tags, true);
                if (isset($tags['penghuni_name'])) {
                    $audit->penghuni_name = $tags['penghuni_name'];
                }
                if (isset($tags['unit_code'])) {
                    $audit->unit_code = $tags['unit_code'];
                }
            }
            
            try {
                switch($auditableType) {
                    case 'Kontrak':
                        if (!$audit->penghuni_name || !$audit->unit_code) {
                            $kontrak = \App\Models\Kontrak::with(['penghuni', 'unit'])->find($audit->auditable_id);
                            if ($kontrak) {
                                $audit->penghuni_name = $audit->penghuni_name ?? ($kontrak->penghuni->nama ?? null);
                                $audit->unit_code = $audit->unit_code ?? ($kontrak->unit->kode_unit ?? null);
                            }
                        }
                        break;
                        
                    case 'Keluarga':
                        if (!$audit->penghuni_name) {
                            $keluarga = \App\Models\Keluarga::with('penghuni')->find($audit->auditable_id);
                            if ($keluarga && $keluarga->penghuni) {
                                $audit->penghuni_name = $keluarga->penghuni->nama;
                            }
                        }
                        break;
                        
                    case 'Tagihan':
                        if (!$audit->penghuni_name) {
                            $tagihan = \App\Models\Tagihan::with(['kontrak.penghuni', 'kontrak.unit'])->find($audit->auditable_id);
                            if ($tagihan && $tagihan->kontrak) {
                                $audit->penghuni_name = $tagihan->kontrak->penghuni->nama ?? null;
                                $audit->unit_code = $tagihan->kontrak->unit->kode_unit ?? null;
                            }
                        }
                        break;
                        
                    case 'Pembayaran':
                        if (!$audit->penghuni_name) {
                            $pembayaran = \App\Models\Pembayaran::with(['tagihan.kontrak.penghuni', 'tagihan.kontrak.unit'])->find($audit->auditable_id);
                            if ($pembayaran && $pembayaran->tagihan && $pembayaran->tagihan->kontrak) {
                                $audit->penghuni_name = $pembayaran->tagihan->kontrak->penghuni->nama ?? null;
                                $audit->unit_code = $pembayaran->tagihan->kontrak->unit->kode_unit ?? null;
                            }
                        }
                        break;
                }
            } catch (\Exception $e) {
            }
            
            return $audit;
        });
        
        $groupedAudits = $allAudits->groupBy(function ($item) {
            return $item->audit_batch_id ?? 'single-' . $item->id;
        });

        $representativeAudits = $groupedAudits->map(function ($group) {
            $first = $group->first();
            
            $first->batch_count = $group->count();
            $first->batch_audits = $group;
            $first->batch_description = null;
            
            if ($first->audit_batch_id) {
                $desc = cache()->get("audit_batch_desc_" . $first->audit_batch_id);
                
                if ($desc) {
                    $first->batch_description = $desc;
                } else {
                    $targetTypes = $group->pluck('auditable_type')->map(fn($type) => class_basename($type))->unique();
                    
                    if ($targetTypes->contains('Penghuni') && $group->contains(fn($a) => $a->event === 'created')) {
                        $penghuniAudit = $group->first(fn($a) => class_basename($a->auditable_type) === 'Penghuni' && $a->event === 'created');
                        $kontrakAudit = $group->first(fn($a) => class_basename($a->auditable_type) === 'Kontrak' && $a->event === 'created');
                        
                        if ($penghuniAudit) {
                            $nama = $penghuniAudit->new_values['nama'] ?? 'N/A';
                            $unit = $kontrakAudit ? ($kontrakAudit->unit_code ?? 'N/A') : 'N/A';
                            
                            $keluargaCount = $group->filter(fn($a) => class_basename($a->auditable_type) === 'Keluarga')->count();
                            if ($keluargaCount > 0) {
                                $first->batch_description = "Menambah Penghuni: {$nama} ke Unit {$unit} dan {$keluargaCount} anggota keluarga";
                            } else {
                                $first->batch_description = "Menambah Penghuni: {$nama} ke Unit {$unit}";
                            }
                        }
                    }
                    elseif ($targetTypes->contains('Kontrak') && $group->contains(fn($a) => 
                        class_basename($a->auditable_type) === 'Kontrak' && 
                        $a->event === 'updated' && 
                        isset($a->new_values['status']) && 
                        $a->new_values['status'] === 'keluar'
                    )) {
                        $kontrakAudit = $group->first(fn($a) => 
                            class_basename($a->auditable_type) === 'Kontrak' && 
                            $a->event === 'updated' &&
                            isset($a->new_values['status']) && 
                            $a->new_values['status'] === 'keluar'
                        );
                        
                        if ($kontrakAudit) {
                            $nama = $kontrakAudit->penghuni_name ?? 'N/A';
                            $unit = $kontrakAudit->unit_code ?? 'N/A';
                            $first->batch_description = "Mengakhiri Kontrak: {$nama} telah keluar dari Unit {$unit}";
                        }
                    }
                    elseif ($targetTypes->contains('Blacklist') && $group->contains(fn($a) => 
                        class_basename($a->auditable_type) === 'Blacklist' && $a->event === 'created'
                    )) {
                        $blacklistAudit = $group->first(fn($a) => 
                            class_basename($a->auditable_type) === 'Blacklist' && $a->event === 'created'
                        );
                        
                        if ($blacklistAudit) {
                            $nama = $blacklistAudit->new_values['nama'] ?? 'N/A';
                            $first->batch_description = "Blacklist Penghuni: {$nama}";
                        }
                    }
                    else {
                        $first->batch_description = 'Batch operasi terkait ' . $targetTypes->implode(', ');
                    }
                }
            }
            
            return $first;
        });

        $perPage = 25;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?? 1;
        
        $currentPageItems = $representativeAudits->slice(($currentPage - 1) * $perPage, $perPage);
        
        $audits = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $representativeAudits->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        return view('pages.audit', compact('audits'));
    }
}