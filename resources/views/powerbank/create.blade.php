@extends('layouts.app')

@section('title', 'Add Device')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Add New Device</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('powerbank.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="uuid" class="form-label">UUID (IMEI) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('uuid') is-invalid @enderror" 
                                   id="uuid" name="uuid" value="{{ old('uuid') }}" required>
                            @error('uuid')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">4G通讯模块的IMEI号</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="device_id" class="form-label">Device ID</label>
                            <input type="text" class="form-control" id="device_id" name="device_id" value="{{ old('device_id', '0') }}">
                            <small class="form-text text-muted">默认: 0</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Device Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" value="{{ old('location') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="sim_uuid" class="form-label">SIM UUID (ICCID)</label>
                            <input type="text" class="form-control" id="sim_uuid" name="sim_uuid" value="{{ old('sim_uuid') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sim_mobile" class="form-label">SIM Mobile</label>
                            <input type="text" class="form-control" id="sim_mobile" name="sim_mobile" value="{{ old('sim_mobile') }}">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save"></i> Create Device
                        </button>
                        <a href="{{ route('powerbank.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

