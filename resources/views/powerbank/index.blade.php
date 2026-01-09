@extends('layouts.app')

@section('title', 'PowerBank Devices')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">PowerBank Devices</h5>
                <a href="{{ route('powerbank.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus"></i> Add Device
                </a>
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

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>UUID (IMEI)</th>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Slots</th>
                                <th>Last Heartbeat</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($devices as $device)
                                <tr>
                                    <td><code>{{ $device->uuid }}</code></td>
                                    <td>{{ $device->name ?? 'N/A' }}</td>
                                    <td>{{ $device->location ?? 'N/A' }}</td>
                                    <td>
                                        @if($device->isOnline())
                                            <span class="badge bg-success">Online</span>
                                        @else
                                            <span class="badge bg-secondary">Offline</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>
                                            Total: {{ $device->total_slots }}<br>
                                            Available: {{ $device->getAvailableSlotsCount() }}<br>
                                            Occupied: {{ $device->getOccupiedSlotsCount() }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($device->last_heartbeat)
                                            {{ $device->last_heartbeat->diffForHumans() }}
                                        @else
                                            Never
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('powerbank.show', $device) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            <a href="{{ route('powerbank.edit', $device) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                            <form action="{{ route('powerbank.refresh', $device) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-info" title="Refresh">
                                                    <i class="bx bx-refresh"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('powerbank.destroy', $device) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No devices found. <a href="{{ route('powerbank.create') }}">Add your first device</a></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $devices->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

