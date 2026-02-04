<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Device;
use App\Services\WaveService;
use Illuminate\Http\Request;

class RentalController extends Controller
{
    protected $waveService;

    public function __construct(WaveService $waveService)
    {
        $this->waveService = $waveService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Rental::with('device');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            if ($request->payment_status === 'paid') {
                $query->whereIn('status', ['paid', 'active', 'completed']);
            } else {
                $query->where('status', $request->payment_status);
            }
        }

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('payment_reference', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        // Pagination
        $rentals = $query->latest()->paginate(20)->withQueryString();
        $devices = Device::all();

        return view('rentals.index', compact('rentals', 'devices'));
    }

    /**
     * Show the form for creating a test rental.
     */
    public function createTest()
    {
        $devices = Device::where('status', 'online')->get();
        if ($devices->isEmpty()) {
            $devices = Device::all(); // Fallback to show all even if offline for testing
        }
        return view('rentals.test', compact('devices'));
    }

    /**
     * Store a newly created test rental in storage and initiate payment.
     */
    public function storeTest(Request $request)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
            'slot_id' => 'required|integer|min:1|max:12',
            'amount' => 'required|numeric|min:10',
        ]);

        $device = Device::findOrFail($request->device_id);

        // 1. Create Pending Rental
        $rental = Rental::create([
            'device_id' => $device->id,
            'slot_id' => $request->slot_id,
            'status' => 'pending',
            'payment_method' => 'wave',
            'amount' => $request->amount,
            'currency' => 'XOF',
            'powerbank_sn' => 'TEST_PB_' . uniqid(), // Simulation SN
        ]);

        // 2. Initiate Wave Payment
        try {
            $result = $this->waveService->initiatePayment($rental);

            if ($result && isset($result['payment_url'])) {
                return redirect($result['payment_url']);
            }

            return back()->with('error', 'Erreur lors de l\'initialisation du paiement Wave. Vérifiez les logs.');

        } catch (\Exception $e) {
            return back()->with('error', 'Exception: ' . $e->getMessage());
        }
    }
}
