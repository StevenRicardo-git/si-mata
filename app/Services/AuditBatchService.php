<?php

namespace App\Services;

use Illuminate\Support\Str;
use OwenIt\Auditing\Facades\Auditor;

class AuditBatchService
{
    protected static $currentBatchId = null;
    
    public static function start(string $description = null)
    {
        self::$currentBatchId = (string) Str::uuid();
        
        if ($description) {
            cache()->put("audit_batch_desc_" . self::$currentBatchId, $description, now()->addHours(1));
        }
        
        return self::$currentBatchId;
    }
    
    public static function getCurrentBatchId()
    {
        return self::$currentBatchId;
    }
    
    public static function end()
    {
        self::$currentBatchId = null;
    }
    
    public static function isActive()
    {
        return self::$currentBatchId !== null;
    }
}