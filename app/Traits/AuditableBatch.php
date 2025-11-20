<?php

namespace App\Traits;

use App\Services\AuditBatchService;

trait AuditableBatch
{
    use \OwenIt\Auditing\Auditable;
    
    public function generateAuditBatchId(): ?string
    {
        return AuditBatchService::getCurrentBatchId();
    }
    
    public function transformAudit(array $data): array
    {
        $batchId = $this->generateAuditBatchId();
        
        if ($batchId) {
            $data['audit_batch_id'] = $batchId;
        }
        
        $context = $this->getAuditContext();
        
        if (!empty($context)) {
            $data['tags'] = json_encode($context);
        }
        
        return $data;
    }
    
    protected function getAuditContext(): array
    {
        $modelClass = get_class($this);
        $context = [];
        
        try {
            switch (class_basename($modelClass)) {
                case 'Kontrak':
                    if ($this->penghuni) {
                        $context['penghuni_name'] = $this->penghuni->nama;
                    }
                    if ($this->unit) {
                        $context['unit_code'] = $this->unit->kode_unit;
                    }
                    break;
                    
                case 'Keluarga':
                    if ($this->penghuni) {
                        $context['penghuni_name'] = $this->penghuni->nama;
                    }
                    break;
                    
                case 'Tagihan':
                    if ($this->kontrak && $this->kontrak->penghuni) {
                        $context['penghuni_name'] = $this->kontrak->penghuni->nama;
                    }
                    if ($this->kontrak && $this->kontrak->unit) {
                        $context['unit_code'] = $this->kontrak->unit->kode_unit;
                    }
                    break;
                    
                case 'Pembayaran':
                    if ($this->tagihan && $this->tagihan->kontrak) {
                        if ($this->tagihan->kontrak->penghuni) {
                            $context['penghuni_name'] = $this->tagihan->kontrak->penghuni->nama;
                        }
                        if ($this->tagihan->kontrak->unit) {
                            $context['unit_code'] = $this->tagihan->kontrak->unit->kode_unit;
                        }
                    }
                    break;
            }
        } catch (\Exception $e) {
        }
        
        return $context;
    }
}