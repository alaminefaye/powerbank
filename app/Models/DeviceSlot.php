<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'slot_number',
        'status',
        'powerbank_sn',
        'battery_level',
        'last_update',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_update' => 'datetime',
        'battery_level' => 'integer',
    ];

    /**
     * Get the device that owns this slot
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Check if slot is empty
     */
    public function isEmpty(): bool
    {
        return $this->status === 'empty';
    }

    /**
     * Check if slot is occupied
     */
    public function isOccupied(): bool
    {
        return $this->status === 'occupied';
    }
}

