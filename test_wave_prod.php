<?php

use App\Models\Rental;
use App\Models\Device;
use App\Services\WaveService;
use Illuminate\Support\Facades\Log;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing Wave Production Integration...\n";

    // Ensure a device exists
    $device = Device::first();
    if (!$device) {
        $device = Device::create([
            'device_id' => 'TEST_DEVICE_' . uniqid(),
            'status' => 'online',
            'slots_total' => 8,
            'slots_available' => 8
        ]);
        echo "Created test device: {$device->device_id}\n";
    }

    // Create a dummy rental
    $rental = Rental::create([
        'device_id' => $device->id,
        'slot_id' => 1,
        'powerbank_sn' => 'PB_TEST_' . uniqid(),
        'started_at' => now(),
        'status' => 'pending',
        'amount' => 100, // 100 FCFA for test
        'currency' => 'XOF'
    ]);

    echo "Created rental ID: {$rental->id}\n";

    $waveService = new WaveService();
    $result = $waveService->initiatePayment($rental);

    if ($result) {
        echo "\nSUCCESS! Wave Payment Initiated.\n";
        echo "Transaction ID: " . $result['transaction_id'] . "\n";
        echo "Payment URL: " . $result['payment_url'] . "\n";
        echo "You can open the URL to see the Wave payment page.\n";
    } else {
        echo "\nFAILED to initiate payment.\n";
        echo "Check laravel.log for details.\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
