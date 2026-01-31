<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Rental;
use App\Services\MqttService;
use App\Services\WaveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RentalController extends Controller
{
    protected $mqttService;
    protected $waveService;

    public function __construct(MqttService $mqttService, WaveService $waveService)
    {
        $this->mqttService = $mqttService;
        $this->waveService = $waveService;
    }

    /**
     * Démarrer une location (Étape 1: Création et Lien de Paiement)
     */
    public function start(Request $request)
    {
        $request->validate([
            'device_uuid' => 'required|string|exists:devices,uuid',
            'slot_id' => 'required|integer',
            'amount' => 'required|numeric|min:100',
        ]);

        $device = Device::where('uuid', $request->device_uuid)->firstOrFail();
        $slotId = $request->slot_id;

        // 1. Créer la transaction "Pending"
        $rental = Rental::create([
            'device_id' => $device->id,
            'slot_id' => $slotId,
            'status' => 'pending',
            'payment_method' => 'wave',
            'amount' => $request->amount,
            'currency' => 'XOF',
        ]);

        // 2. Initier le paiement Wave
        try {
            $paymentResponse = $this->waveService->initiatePayment($rental);

            if (!$paymentResponse) {
                $rental->update(['status' => 'failed']);
                return response()->json(['error' => 'Erreur lors de l\'initialisation du paiement Wave'], 500);
            }

            return response()->json([
                'message' => 'Paiement initié',
                'rental_id' => $rental->id,
                'payment_url' => $paymentResponse['payment_url'],
                'transaction_id' => $paymentResponse['transaction_id']
            ]);

        } catch (\Exception $e) {
            Log::error("Rental Error: " . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    /**
     * Webhook Wave (Appelé par Wave après paiement)
     */
    public function webhook(Request $request)
    {
        Log::info('Webhook Wave reçu', $request->all());

        $signature = $request->header('wave-signature');
        // On passe le contenu brut pour la validation de signature
        $success = $this->waveService->handleWebhook($request->all(), $signature, $request->getContent());

        if ($success) {
            // Récupérer la location associée via la référence
            $data = $request->input('data', []);
            $clientReference = $data['client_reference'] ?? '';
            
            if ($clientReference && str_starts_with($clientReference, 'RENTAL_')) {
                $parts = explode('_', $clientReference);
                $rentalId = $parts[1] ?? null;

                if ($rentalId) {
                    $rental = Rental::find($rentalId);
                    
                    // Si payé, on déclenche l'éjection !
                    if ($rental && $rental->status === 'paid') {
                        $this->releasePowerBank($rental);
                    }
                }
            }
            
            return response()->json(['message' => 'Webhook processed']);
        }

        return response()->json(['error' => 'Webhook processing failed'], 400);
    }

    /**
     * Callback de succès (Redirection après paiement réussi sur Wave)
     */
    public function callbackSuccess(Request $request)
    {
        $rentalId = $request->query('rental_id');
        $rental = Rental::find($rentalId);

        if (!$rental) {
            return response()->json(['error' => 'Location introuvable'], 404);
        }

        // Idéalement, rediriger vers une page frontend
        // return redirect("https://mon-frontend.com/success?rental_id={$rentalId}");
        
        return response()->json([
            'status' => 'success',
            'message' => 'Paiement réussi. Votre batterie est en cours d\'éjection.',
            'rental' => $rental
        ]);
    }

    /**
     * Callback d'erreur (Redirection après annulation/échec)
     */
    public function callbackError(Request $request)
    {
        $rentalId = $request->query('rental_id');
        
        return response()->json([
            'status' => 'error',
            'message' => 'Le paiement a été annulé ou a échoué.',
            'rental_id' => $rentalId
        ]);
    }

    /**
     * Logique d'éjection de la batterie
     */
    protected function releasePowerBank(Rental $rental)
    {
        Log::info("Tentative d'éjection pour la location #{$rental->id}");
        
        try {
            $device = $rental->device;
            $success = $this->mqttService->sendPopupCommand($device, $rental->slot_id);

            if ($success) {
                $rental->update([
                    'status' => 'active',
                    'started_at' => now(),
                ]);
                Log::info("Batterie éjectée avec succès pour location #{$rental->id}");
            } else {
                Log::error("Échec commande MQTT pour location #{$rental->id}");
                // TODO: Notifier l'admin ou prévoir remboursement
            }
        } catch (\Exception $e) {
            Log::error("Erreur éjection: " . $e->getMessage());
        }
    }
}
