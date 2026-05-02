<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyncQueue extends Model
{
    use HasFactory;

    protected $fillable = [
        'model_type',
        'model_id',
        'action',
        'data',
        'status',
        'error_message',
        'retry_count',
        'retry_at',
        'processed_at',
    ];

    protected $casts = [
        'data' => 'array',
        'retry_count' => 'integer',
        'retry_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    /**
     * Scope a query to only include pending items.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include failed items.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to only include items ready for retry.
     */
    public function scopeReadyForRetry($query)
    {
        return $query->where('status', 'failed')
                    ->where('retry_at', '<=', now());
    }
}
