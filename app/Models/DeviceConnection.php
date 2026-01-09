<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'client_id',
        'product_key',
        'host',
        'port',
        'username',
        'password',
        'timestamp',
        'status',
        'connected_at',
        'disconnected_at',
    ];

    protected $casts = [
        'connected_at' => 'datetime',
        'disconnected_at' => 'datetime',
        'timestamp' => 'integer',
        'port' => 'integer',
    ];

    /**
     * Get the device that owns this connection
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }
}

