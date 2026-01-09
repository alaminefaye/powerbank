<?php

namespace App\Services;

use App\Models\Device;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MqttService
{
    /**
     * Publish a message to a device via MQTT
     * Note: This is a simplified implementation. 
     * For production, use a proper MQTT client library like php-mqtt/client
     */
    public function publish(string $topic, string $message, int $qos = 0): bool
    {
        try {
            // Option 1: Use HTTP API if your MQTT broker supports it (like EMQX)
            $mqttApiUrl = config('powerbank.mqtt_api_url');
            if ($mqttApiUrl) {
                $response = Http::withBasicAuth(
                    config('powerbank.mqtt_api_username', ''),
                    config('powerbank.mqtt_api_password', '')
                )->post("{$mqttApiUrl}/api/v5/publish", [
                    'topic' => $topic,
                    'payload' => $message,
                    'qos' => $qos,
                ]);
                
                if ($response->successful()) {
                    Log::info("MQTT Published to {$topic}: {$message}");
                    return true;
                }
            }
            
            // Option 2: Use command line mosquitto_pub if available
            $mosquittoPub = config('powerbank.mosquitto_pub_path', '/usr/bin/mosquitto_pub');
            if (file_exists($mosquittoPub)) {
                $host = config('powerbank.mqtt_host', 'localhost');
                $port = config('powerbank.mqtt_port', 1883);
                $username = config('powerbank.mqtt_username');
                $password = config('powerbank.mqtt_password');
                
                $cmd = escapeshellarg($mosquittoPub) . 
                       ' -h ' . escapeshellarg($host) .
                       ' -p ' . escapeshellarg($port) .
                       ' -t ' . escapeshellarg($topic) .
                       ' -m ' . escapeshellarg($message) .
                       ' -q ' . $qos;
                
                if ($username) {
                    $cmd .= ' -u ' . escapeshellarg($username);
                }
                if ($password) {
                    $cmd .= ' -P ' . escapeshellarg($password);
                }
                
                exec($cmd . ' 2>&1', $output, $returnVar);
                
                if ($returnVar === 0) {
                    Log::info("MQTT Published to {$topic}: {$message}");
                    return true;
                } else {
                    Log::error('MQTT Publish Error: ' . implode("\n", $output));
                }
            }
            
            // Fallback: Log the message (for development/testing)
            Log::info("MQTT Message (not sent - no broker configured): Topic: {$topic}, Message: {$message}");
            return true; // Return true for development
            
        } catch (\Exception $e) {
            Log::error('MQTT Publish Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send check command to device
     */
    public function sendCheckCommand(Device $device): bool
    {
        $topic = "powerbank/{$device->mqtt_client_id}/check";
        return $this->publish($topic, 'check', 1);
    }

    /**
     * Send popup command to device slot
     */
    public function sendPopupCommand(Device $device, int $slotNumber): bool
    {
        $topic = "powerbank/{$device->mqtt_client_id}/popup";
        $message = json_encode(['slot' => $slotNumber]);
        return $this->publish($topic, $message, 1);
    }

    /**
     * Send popup by SN command
     */
    public function sendPopupSnCommand(Device $device, string $sn): bool
    {
        $topic = "powerbank/{$device->mqtt_client_id}/popup_sn";
        $message = json_encode(['sn' => $sn]);
        return $this->publish($topic, $message, 1);
    }

    /**
     * Send upgrade command
     */
    public function sendUpgradeCommand(Device $device, string $version): bool
    {
        $topic = "powerbank/{$device->mqtt_client_id}/push_version_publish";
        $message = json_encode(['version' => $version]);
        return $this->publish($topic, $message, 1);
    }

    /**
     * Send load ad command
     */
    public function sendLoadAdCommand(Device $device): bool
    {
        $topic = "powerbank/{$device->mqtt_client_id}/load_ad";
        return $this->publish($topic, 'load_ad', 1);
    }

    /**
     * Subscribe to device topics
     * Note: This requires a proper MQTT client library for real-time subscription
     * For now, devices will send data via HTTP API endpoints
     */
    public function subscribe(Device $device, callable $callback): void
    {
        // For production, implement using php-mqtt/client or similar library
        // This would typically run as a background worker/daemon
        Log::info("MQTT Subscribe requested for device: {$device->uuid}");
        
        // Devices should send updates via HTTP API instead
        // See: /api/rentbox/device/upload endpoint
    }
}

