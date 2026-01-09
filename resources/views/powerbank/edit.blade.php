@extends('layouts.app')

@section('title', 'Edit Device')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Device: {{ $device->uuid }}</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('powerbank.update', $device) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="uuid" class="form-label">UUID (IMEI)</label>
                            <input type="text" class="form-control" value="{{ $device->uuid }}" disabled>
                            <small class="form-text text-muted">UUID cannot be changed</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="device_id" class="form-label">Device ID</label>
                            <input type="text" class="form-control" id="device_id" name="device_id" value="{{ old('device_id', $device->device_id) }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Device Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $device->name) }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                   id="location" name="location" value="{{ old('location', $device->location) }}">
                            @error('location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="sim_uuid" class="form-label">SIM UUID (ICCID)</label>
                            <input type="text" class="form-control" id="sim_uuid" name="sim_uuid" value="{{ old('sim_uuid', $device->sim_uuid) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sim_mobile" class="form-label">SIM Mobile</label>
                            <input type="text" class="form-control" id="sim_mobile" name="sim_mobile" value="{{ old('sim_mobile', $device->sim_mobile) }}">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save"></i> Update Device
                        </button>
                        <a href="{{ route('powerbank.show', $device) }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

