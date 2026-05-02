<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockchainRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'naissance_id',
        'hash',
        'previous_hash',
        'timestamp',
        'block_number',
        'data_signature',
        'verified',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'verified' => 'boolean',
    ];

    /**
     * Get the naissance that owns the blockchain record.
     */
    public function naissance(): BelongsTo
    {
        return $this->belongsTo(Naissance::class);
    }

    /**
     * Get the previous record in the chain.
     */
    public function previousRecord()
    {
        if ($this->previous_hash) {
            return static::where('hash', $this->previous_hash)->first();
        }
        return null;
    }

    /**
     * Get the next record in the chain.
     */
    public function nextRecord()
    {
        return static::where('previous_hash', $this->hash)->first();
    }

    /**
     * Scope to get records in order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('block_number', 'asc');
    }

    /**
     * Scope to get verified records.
     */
    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    /**
     * Check if this record is part of a valid chain.
     */
    public function isValidChain(): bool
    {
        $previous = $this->previousRecord();
        
        if ($previous) {
            return $this->previous_hash === $previous->hash;
        }
        
        return true;
    }
}
