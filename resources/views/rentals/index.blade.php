@extends('layouts.app')

@section('title', 'Historique des Locations & Paiements')

@section('content')
<h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Gestion /</span> Locations & Paiements</h4>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filtres de recherche</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('rentals.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Recherche (Réf / ID)</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Ex: RENTAL_123...">
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Statut Location</label>
                <select name="status" class="form-select">
                    <option value="">Tous</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>En cours</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Terminée</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Payé (Non éjecté)</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Échoué</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Statut Paiement</label>
                <select name="payment_status" class="form-select">
                    <option value="">Tous</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Payé (Succès)</option>
                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Échoué</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Station (Kiosque)</label>
                <select name="device_id" class="form-select">
                    <option value="">Toutes les stations</option>
                    @foreach($devices as $device)
                        <option value="{{ $device->id }}" {{ request('device_id') == $device->id ? 'selected' : '' }}>
                            {{ $device->serial_number }} ({{ $device->location ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Liste des Transactions</h5>
        <span class="badge bg-primary">{{ $rentals->total() }} Résultats</span>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Station</th>
                    <th>Slot</th>
                    <th>Client / Réf</th>
                    <th>Montant</th>
                    <th>Statut Paiement</th>
                    <th>Statut Location</th>
                    <th>Durée</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($rentals as $rental)
                <tr>
                    <td><strong>#{{ $rental->id }}</strong></td>
                    <td>
                        {{ $rental->created_at->format('d/m/Y') }}<br>
                        <small class="text-muted">{{ $rental->created_at->format('H:i') }}</small>
                    </td>
                    <td>
                        @if($rental->device)
                            <span class="badge bg-label-info">{{ $rental->device->serial_number }}</span>
                        @else
                            <span class="badge bg-label-secondary">N/A</span>
                        @endif
                    </td>
                    <td>{{ $rental->slot_id }}</td>
                    <td>
                        <span class="d-inline-block text-truncate" style="max-width: 150px;" title="{{ $rental->payment_reference }}">
                            {{ $rental->payment_reference ?? 'N/A' }}
                        </span>
                    </td>
                    <td>{{ number_format($rental->amount, 0, ',', ' ') }} FCFA</td>
                    <td>
                        @if(in_array($rental->status, ['paid', 'active', 'completed']))
                            <span class="badge bg-label-success">Payé</span>
                        @elseif($rental->status === 'pending')
                            <span class="badge bg-label-warning">En attente</span>
                        @elseif($rental->status === 'failed')
                            <span class="badge bg-label-danger">Échoué</span>
                        @else
                            <span class="badge bg-label-secondary">{{ $rental->status }}</span>
                        @endif
                    </td>
                    <td>
                        @if($rental->status === 'active')
                            <span class="badge bg-label-primary">En cours</span>
                        @elseif($rental->status === 'completed')
                            <span class="badge bg-label-success">Terminée</span>
                        @elseif($rental->status === 'paid')
                            <span class="badge bg-label-info">Prêt à éjecter</span>
                        @elseif($rental->status === 'pending')
                            <span class="badge bg-label-warning">En attente</span>
                        @elseif($rental->status === 'failed')
                            <span class="badge bg-label-danger">Échoué</span>
                        @else
                            <span class="badge bg-label-secondary">{{ $rental->status }}</span>
                        @endif
                    </td>
                    <td>
                        @if($rental->started_at && $rental->ended_at)
                            {{ $rental->started_at->diffForHumans($rental->ended_at, true) }}
                        @elseif($rental->started_at)
                            En cours...
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="bx bx-search fs-1 text-muted mb-2"></i>
                        <p class="text-muted">Aucune transaction trouvée avec ces filtres.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-center">
        {{ $rentals->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
