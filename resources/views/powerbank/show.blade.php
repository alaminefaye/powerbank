@extends('layouts.app')

@section('title', 'Device Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Device Details: {{ $device->name ?? $device->uuid }}</h5>
                <div>
                    <a href="{{ route('powerbank.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-arrow-back"></i> Back
                    </a>
                    <a href="{{ route('powerbank.edit', $device) }}" class="btn btn-sm btn-primary">
                        <i class="bx bx-edit"></i> Edit
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Device Information</h6>
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">UUID (IMEI):</th>
                                <td><code>{{ $device->uuid }}</code></td>
                            </tr>
                            <tr>
                                <th>Device ID:</th>
                                <td>{{ $device->device_id }}</td>
                            </tr>
                            <tr>
                                <th>Name:</th>
                                <td>{{ $device->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Location:</th>
                                <td>{{ $device->location ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if($device->isOnline())
                                        <span class="badge bg-success">Online</span>
                                    @else
                                        <span class="badge bg-secondary">Offline</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Last Heartbeat:</th>
                                <td>
                                    @if($device->last_heartbeat)
                                        {{ $device->last_heartbeat->format('Y-m-d H:i:s') }}<br>
                                        <small class="text-muted">{{ $device->last_heartbeat->diffForHumans() }}</small>
                                    @else
                                        Never
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-3">Hardware Information</h6>
                        <table class="table table-borderless">
                            <tr>
                                <th width="40%">Hardware Version:</th>
                                <td>{{ $device->hardware_version ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Software Version:</th>
                                <td>{{ $device->software_version ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>SIM UUID (ICCID):</th>
                                <td>{{ $device->sim_uuid ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>SIM Mobile:</th>
                                <td>{{ $device->sim_mobile ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Total Slots:</th>
                                <td>{{ $device->total_slots }}</td>
                            </tr>
                            <tr>
                                <th>MQTT Host:</th>
                                <td>{{ $device->mqtt_host ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-3">Device Actions</h6>
                        <div class="btn-group" role="group">
                            <form action="{{ route('powerbank.check', $device) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-check"></i> Check Device
                                </button>
                            </form>
                            <form action="{{ route('powerbank.refresh', $device) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-info">
                                    <i class="bx bx-refresh"></i> Refresh Status
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <h6 class="text-muted mb-3">Device Slots</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Slot #</th>
                                        <th>Status</th>
                                        <th>PowerBank SN</th>
                                        <th>Battery Level</th>
                                        <th>Last Update</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($device->slots as $slot)
                                        <tr>
                                            <td><strong>{{ $slot->slot_number }}</strong></td>
                                            <td>
                                                @if($slot->status === 'empty')
                                                    <span class="badge bg-success">Empty</span>
                                                @elseif($slot->status === 'occupied')
                                                    <span class="badge bg-primary">Occupied</span>
                                                @elseif($slot->status === 'fault')
                                                    <span class="badge bg-danger">Fault</span>
                                                @else
                                                    <span class="badge bg-warning">Maintenance</span>
                                                @endif
                                            </td>
                                            <td>{{ $slot->powerbank_sn ?? 'N/A' }}</td>
                                            <td>
                                                @if($slot->battery_level !== null)
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar {{ $slot->battery_level > 50 ? 'bg-success' : ($slot->battery_level > 20 ? 'bg-warning' : 'bg-danger') }}" 
                                                             role="progressbar" 
                                                             style="width: {{ $slot->battery_level }}%">
                                                            {{ $slot->battery_level }}%
                                                        </div>
                                                    </div>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>
                                                @if($slot->last_update)
                                                    {{ $slot->last_update->diffForHumans() }}
                                                @else
                                                    Never
                                                @endif
                                            </td>
                                            <td>
                                                @if($slot->isOccupied())
                                                    <form action="{{ route('powerbank.popup', $device) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="slot" value="{{ $slot->slot_number }}">
                                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Popup slot {{ $slot->slot_number }}?')">
                                                            <i class="bx bx-eject"></i> Popup
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No slots data available. Send a check command to update.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <h6 class="text-muted mb-3">Popup by SN</h6>
                        <form action="{{ route('powerbank.popup-sn', $device) }}" method="POST" class="row g-3">
                            @csrf
                            <div class="col-md-8">
                                <input type="text" name="sn" class="form-control" placeholder="Enter PowerBank SN" required>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="bx bx-eject"></i> Popup by SN
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

