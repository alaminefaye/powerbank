@extends('layouts.app')

@section('title', 'Test de Location & Paiement')

@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Outils /</span> Simulateur de Location</h4>

<div class="row">
    <div class="col-md-6 offset-md-3">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lancer un test de paiement Wave</h5>
                <small class="text-muted">Environnement: {{ app()->environment() }}</small>
            </div>
            <div class="card-body">
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="alert alert-warning">
                    <i class='bx bx-info-circle'></i> Attention : En production, ce test débitera réellement votre compte Wave (100 FCFA).
                </div>

                <form method="POST" action="{{ route('rentals.test.store') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label class="form-label" for="device_id">Choisir la Station (Kiosque)</label>
                        <select name="device_id" id="device_id" class="form-select" required>
                            @foreach($devices as $device)
                                <option value="{{ $device->id }}">
                                    {{ $device->device_id }} - {{ $device->location ?? 'Sans emplacement' }} ({{ $device->status }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="slot_id">Numéro du Slot (Casier)</label>
                        <select name="slot_id" id="slot_id" class="form-select" required>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}">Slot {{ $i }}</option>
                            @endfor
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="amount">Montant (FCFA)</label>
                        <input type="number" name="amount" id="amount" class="form-control" value="100" min="10" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class='bx bx-credit-card'></i> Initier le Paiement & Tester
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
