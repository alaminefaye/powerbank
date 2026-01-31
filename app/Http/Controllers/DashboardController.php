<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rental;
use App\Models\Device;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistiques
        $totalRevenue = Rental::whereIn('status', ['paid', 'active', 'completed'])->sum('amount');
        $activeRentals = Rental::where('status', 'active')->count();
        $totalDevices = Device::count();
        $onlineDevices = Device::where('status', 'online')->count();

        return view('dashboard', compact(
            'totalRevenue', 
            'activeRentals', 
            'totalDevices', 
            'onlineDevices'
        ));
    }
}

