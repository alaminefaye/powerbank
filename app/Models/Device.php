<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'device_id',
        'sim_uuid',
        'sim_mobile',
        'name',
        'location',
        'hardware_version',
        'software_version',
        'mqtt_client_id',
        'mqtt_host',
        'mqtt_port',
        'mqtt_username',
        'mqtt_password',
        'status',
        'last_heartbeat',
        'total_slots',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_heartbeat' => 'datetime',
    ];

    /**
     * Get all slots for this device
     */
    public function slots(): HasMany
    {
        return $this->hasMany(DeviceSlot::class);
    }

    /**
     * Get the current connection for this device
     */
    public function connection(): HasOne
    {
        return $this->hasOne(DeviceConnection::class)->latestOfMany();
    }

    /**
     * Check if device is online
     */
    public function isOnline(): bool
    {
        return $this->status === 'online' && 
               $this->last_heartbeat && 
               $this->last_heartbeat->gt(now()->subMinutes(5));
    }

    /**
     * Get available slots count
     */
    public function getAvailableSlotsCount(): int
    {
        return $this->slots()->where('status', 'empty')->count();
    }

    /**
     * Get occupied slots count
     */
    public function getOccupiedSlotsCount(): int
    {
        return $this->slots()->where('status', 'occupied')->count();
    }
}

