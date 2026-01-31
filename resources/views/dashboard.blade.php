@extends('layouts.app')

@section('title', 'Tableau de bord')

@push('vendor-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
@endpush

@section('content')
<div class="row">
    <!-- Welcome Card -->
    <div class="col-lg-12 mb-4 order-0">
        <div class="card">
            <div class="d-flex align-items-end row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Bienvenue sur PowerBank Admin! 🎉</h5>
                        <p class="mb-4">
                            Vous avez <span class="fw-bold">{{ $activeRentals }}</span> locations actives en ce moment.
                        </p>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-4">
                        <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" height="140" alt="View Badge User" data-app-dark-img="illustrations/man-with-laptop-dark.png" data-app-light-img="illustrations/man-with-laptop-light.png" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('assets/img/icons/unicons/chart-success.png') }}" alt="chart success" class="rounded" />
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Revenu Total</span>
                <h3 class="card-title mb-2">{{ number_format($totalRevenue, 0, ',', ' ') }} FCFA</h3>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('assets/img/icons/unicons/wallet-info.png') }}" alt="Credit Card" class="rounded" />
                    </div>
                </div>
                <span>Locations Actives</span>
                <h3 class="card-title text-nowrap mb-1">{{ $activeRentals }}</h3>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <img src="{{ asset('assets/img/icons/unicons/cc-primary.png') }}" alt="Credit Card" class="rounded" />
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Total Appareils</span>
                <h3 class="card-title mb-2">{{ $totalDevices }}</h3>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="card-title d-flex align-items-start justify-content-between">
                    <div class="avatar flex-shrink-0">
                        <i class="bx bx-wifi text-success" style="font-size: 2rem;"></i>
                    </div>
                </div>
                <span class="fw-semibold d-block mb-1">Appareils en ligne</span>
                <h3 class="card-title mb-2">{{ $onlineDevices }}</h3>
            </div>
        </div>
    </div>
</div>
@endsection

@push('vendor-js')
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
@endpush

@push('page-js')
<script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>
@endpush
