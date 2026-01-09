<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Services\MqttService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PowerBankController extends Controller
{
    protected $mqttService;

    public function __construct(MqttService $mqttService)
    {
        $this->mqttService = $mqttService;
    }

    /**
     * Display a listing of devices
     */
    public function index()
    {
        $devices = Device::with(['slots', 'connection'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('powerbank.index', compact('devices'));
    }

    /**
     * Show the form for creating a new device
     */
    public function create()
    {
        return view('powerbank.create');
    }

    /**
     * Store a newly created device
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'uuid' => 'required|string|unique:devices,uuid',
            'name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'device_id' => 'nullable|string',
            'sim_uuid' => 'nullable|string',
            'sim_mobile' => 'nullable|string',
        ]);

        $device = Device::create($validated);

        return redirect()->route('powerbank.index')
            ->with('success', 'Device created successfully.');
    }

    /**
     * Display the specified device
     */
    public function show(Device $device)
    {
        $device->load(['slots', 'connection']);
        return view('powerbank.show', compact('device'));
    }

    /**
     * Show the form for editing the device
     */
    public function edit(Device $device)
    {
        return view('powerbank.edit', compact('device'));
    }

    /**
     * Update the device
     */
    public function update(Request $request, Device $device)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'device_id' => 'nullable|string',
            'sim_uuid' => 'nullable|string',
            'sim_mobile' => 'nullable|string',
        ]);

        $device->update($validated);

        return redirect()->route('powerbank.show', $device)
            ->with('success', 'Device updated successfully.');
    }

    /**
     * Remove the device
     */
    public function destroy(Device $device)
    {
        $device->delete();

        return redirect()->route('powerbank.index')
            ->with('success', 'Device deleted successfully.');
    }

    /**
     * Send check command to device
     */
    public function check(Device $device)
    {
        try {
            $success = $this->mqttService->sendCheckCommand($device);
            
            if ($success) {
                return back()->with('success', 'Check command sent successfully.');
            } else {
                return back()->with('error', 'Failed to send check command.');
            }
        } catch (\Exception $e) {
            Log::error('Check command error: ' . $e->getMessage());
            return back()->with('error', 'Error sending check command: ' . $e->getMessage());
        }
    }

    /**
     * Send popup command to device slot
     */
    public function popup(Request $request, Device $device)
    {
        $request->validate([
            'slot' => 'required|integer|min:1',
        ]);

        try {
            $success = $this->mqttService->sendPopupCommand($device, $request->input('slot'));
            
            if ($success) {
                return back()->with('success', 'Popup command sent successfully.');
            } else {
                return back()->with('error', 'Failed to send popup command.');
            }
        } catch (\Exception $e) {
            Log::error('Popup command error: ' . $e->getMessage());
            return back()->with('error', 'Error sending popup command: ' . $e->getMessage());
        }
    }

    /**
     * Send popup by SN command
     */
    public function popupSn(Request $request, Device $device)
    {
        $request->validate([
            'sn' => 'required|string',
        ]);

        try {
            $success = $this->mqttService->sendPopupSnCommand($device, $request->input('sn'));
            
            if ($success) {
                return back()->with('success', 'Popup SN command sent successfully.');
            } else {
                return back()->with('error', 'Failed to send popup SN command.');
            }
        } catch (\Exception $e) {
            Log::error('Popup SN command error: ' . $e->getMessage());
            return back()->with('error', 'Error sending popup SN command: ' . $e->getMessage());
        }
    }

    /**
     * Refresh device status
     */
    public function refresh(Device $device)
    {
        try {
            $this->mqttService->sendCheckCommand($device);
            return back()->with('success', 'Refresh command sent. Status will update shortly.');
        } catch (\Exception $e) {
            Log::error('Refresh error: ' . $e->getMessage());
            return back()->with('error', 'Error refreshing device: ' . $e->getMessage());
        }
    }
}

