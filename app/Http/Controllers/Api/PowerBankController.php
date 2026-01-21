<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceConnection;
use Illuminate\Http\Request;

class PowerBankController extends Controller
{
    /**
     * Device authentication endpoint
     * POST /api/rentbox/client/connect
     */
    public function connect(Request $request)
    {
        // Validate required parameters
        $request->validate([
            'uuid' => 'required|string',
            'deviceId' => 'required|string',
            'sign' => 'required|string',
        ]);

        $uuid = $request->input('uuid');
        $deviceId = $request->input('deviceId', '0');
        $simUUID = $request->input('simUUID', '');
        $simMobile = $request->input('simMobile', '');

        // Verify signature
        $expectedSign = md5("deviceId={$deviceId}|simMobile={$simMobile}|simUUID={$simUUID}|uuid={$uuid}");
        
        if ($request->input('sign') !== $expectedSign) {
            return response()->json([
                'code' => 401,
                'type' => 1,
                'msg' => 'Invalid signature',
                'time' => now()->timestamp * 1000,
            ], 401);
        }

        // Get or create device
        $device = Device::firstOrCreate(
            ['uuid' => $uuid],
            [
                'device_id' => $deviceId,
                'sim_uuid' => $simUUID ?: null,
                'sim_mobile' => $simMobile ?: null,
                'status' => 'offline',
            ]
        );

        // Update device info
        $device->update([
            'device_id' => $deviceId,
            'sim_uuid' => $simUUID ?: null,
            'sim_mobile' => $simMobile ?: null,
        ]);

        // Get hardware and software version from body
        $body = $request->getContent();
        if (preg_match('/hardware=([^&]+)/', $body, $hwMatch)) {
            $device->hardware_version = $hwMatch[1];
        }
        if (preg_match('/software=([^&]+)/', $body, $swMatch)) {
            $device->software_version = $swMatch[1];
        }
        $device->save();

        // Generate MQTT connection parameters
        $clientId = $uuid;
        $productKey = 'powerbank';
        $host = config('powerbank.mqtt_host', 'powerbank.universaltechnologiesafrica.com');
        $port = config('powerbank.mqtt_port', 1883);
        $username = $uuid;
        $password = md5($uuid . config('app.key') . now()->timestamp);
        $timestamp = now()->timestamp * 1000;

        // Update device MQTT info
        $device->update([
            'mqtt_client_id' => $clientId,
            'mqtt_host' => $host,
            'mqtt_port' => $port,
            'mqtt_username' => $username,
            'mqtt_password' => $password,
        ]);

        // Save connection record
        DeviceConnection::create([
            'device_id' => $device->id,
            'client_id' => $clientId,
            'product_key' => $productKey,
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'password' => $password,
            'timestamp' => $timestamp,
            'status' => 'connected',
            'connected_at' => now(),
        ]);

        // Return connection data
        $data = implode(',', [
            $clientId,
            $productKey,
            $host,
            $port,
            $username,
            $password,
            $timestamp,
        ]);

        return response()->json([
            'code' => 200,
            'type' => 0,
            'data' => $data,
            'msg' => 'OK',
            'time' => $timestamp,
        ]);
    }

    /**
     * Handle device upload (status report)
     * POST /api/rentbox/device/upload
     */
    public function upload(Request $request)
    {
        $request->validate([
            'uuid' => 'required|string',
            'data' => 'required|json',
        ]);

        $device = Device::where('uuid', $request->input('uuid'))->first();
        
        if (!$device) {
            return response()->json([
                'code' => 404,
                'msg' => 'Device not found',
            ], 404);
        }

        $data = json_decode($request->input('data'), true);
        
        // Update device status
        $device->update([
            'status' => 'online',
            'last_heartbeat' => now(),
            'total_slots' => $data['total_slots'] ?? $device->total_slots,
        ]);

        // Update slots
        if (isset($data['slots']) && is_array($data['slots'])) {
            foreach ($data['slots'] as $slotData) {
                $device->slots()->updateOrCreate(
                    ['slot_number' => $slotData['slot']],
                    [
                        'status' => $slotData['status'] ?? 'empty',
                        'powerbank_sn' => $slotData['sn'] ?? null,
                        'battery_level' => $slotData['battery'] ?? null,
                        'last_update' => now(),
                    ]
                );
            }
        }

        return response()->json([
            'code' => 200,
            'type' => 0,
            'msg' => 'OK',
            'time' => now()->timestamp * 1000,
        ]);
    }

    /**
     * Handle device return
     * POST /api/rentbox/device/return
     */
    public function returnPowerBank(Request $request)
    {
        $request->validate([
            'uuid' => 'required|string',
            'slot' => 'required|integer',
            'sn' => 'required|string',
        ]);

        $device = Device::where('uuid', $request->input('uuid'))->first();
        
        if (!$device) {
            return response()->json([
                'code' => 404,
                'msg' => 'Device not found',
            ], 404);
        }

        $slot = $device->slots()->where('slot_number', $request->input('slot'))->first();
        
        if ($slot) {
            $slot->update([
                'status' => 'occupied',
                'powerbank_sn' => $request->input('sn'),
                'last_update' => now(),
            ]);
        }

        return response()->json([
            'code' => 200,
            'type' => 0,
            'msg' => 'OK',
            'time' => now()->timestamp * 1000,
        ]);
    }
}

