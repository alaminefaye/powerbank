<?php

namespace App\Services;

use App\Models\Rental;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WaveService
{
    protected $apiKey;
    protected $baseUrl;
    protected $webhookSecret;

    public function __construct()
    {
        // Configuration Wave depuis .env
        $this->apiKey = config('services.wave.api_key');
        $this->baseUrl = config('services.wave.base_url', 'https://api.wave.com/v1');
        $this->webhookSecret = config('services.wave.webhook_secret');
    }

    /**
     * Initier un paiement Wave pour une location
     * 
     * @param Rental $rental
     * @return array|null
     */
    public function initiatePayment(Rental $rental)
    {
        Log::info('Initiation paiement Wave pour location', [
            'rental_id' => $rental->id,
            'amount' => $rental->amount,
        ]);

        if (empty($this->apiKey)) {
            Log::warning('API Wave non configurée');
            return null;
        }

        try {
            // Construire les URLs de callback
            $baseUrl = config('app.url');
            
            // Forcer HTTPS si nécessaire (Wave exige HTTPS)
            if (strpos($baseUrl, 'http://') === 0 && app()->environment('production')) {
                $baseUrl = str_replace('http://', 'https://', $baseUrl);
            }
            
            // URL de succès (le client est redirigé ici après paiement)
            $successUrl = $baseUrl . '/api/rental/callback/success?rental_id=' . $rental->id;
            // URL d'erreur/annulation
            $errorUrl = $baseUrl . '/api/rental/callback/error?rental_id=' . $rental->id;
            
            // Référence unique pour le webhook
            $clientReference = 'RENTAL_' . $rental->id . '_' . uniqid();

            $requestData = [
                'amount' => (string)$rental->amount, // Wave attend souvent une string pour le montant
                'currency' => $rental->currency,
                'success_url' => $successUrl,
                'error_url' => $errorUrl,
                'client_reference' => $clientReference,
            ];

            Log::info('Données requête Wave', $requestData);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/checkout/sessions', $requestData);

            if ($response->successful()) {
                $data = $response->json();
                
                // Mettre à jour la location avec la référence de paiement
                $rental->update([
                    'payment_reference' => $data['id'] ?? $data['transaction_id'] ?? null,
                ]);

                return [
                    'transaction_id' => $data['id'] ?? $data['transaction_id'],
                    'payment_url' => $data['wave_launch_url'] ?? $data['payment_url'] ?? $data['checkout_url'],
                ];
            }

            Log::error('Erreur API Wave', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Exception Wave Payment: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Traiter le webhook de confirmation Wave
     */
    public function handleWebhook(array $webhookData, $signature = null, $rawBody = null)
    {
        // Validation de la signature si le secret est configuré
        if ($this->webhookSecret) {
            if (!$signature || !$rawBody) {
                Log::warning('Webhook Wave: Signature ou corps manquant');
                return false;
            }

            $computedSignature = hash_hmac('sha256', $rawBody, $this->webhookSecret);
            
            if (!hash_equals($computedSignature, $signature)) {
                Log::error('Webhook Wave: Signature invalide', [
                    'received' => $signature,
                    'computed' => $computedSignature
                ]);
                return false;
            }
        }
        
        $data = $webhookData['data'] ?? [];
        $clientReference = $data['client_reference'] ?? null;
        $paymentStatus = $data['payment_status'] ?? null;

        Log::info("Webhook Wave reçu", ['ref' => $clientReference, 'status' => $paymentStatus]);

        if (!$clientReference || !str_starts_with($clientReference, 'RENTAL_')) {
            return false;
        }

        // Extraire l'ID de la location (Format: RENTAL_{id}_{uniqid})
        $parts = explode('_', $clientReference);
        if (count($parts) < 2) return false;
        
        $rentalId = $parts[1];
        $rental = Rental::find($rentalId);

        if (!$rental) {
            Log::error("Rental introuvable pour le webhook: $rentalId");
            return false;
        }

        if ($paymentStatus === 'succeeded' || $paymentStatus === 'successful') {
            // Mettre à jour le statut si pas déjà payé
            if ($rental->status !== 'paid' && $rental->status !== 'active') {
                $rental->update(['status' => 'paid']);
                
                // Ici, on pourrait déclencher l'éjection de la batterie si ce n'est pas déjà fait
                // Mais idéalement, cela devrait être géré par un Job ou un appel direct si possible
                // Pour l'instant, on marque juste comme payé.
            }
        } elseif ($paymentStatus === 'failed' || $paymentStatus === 'cancelled') {
             $rental->update(['status' => 'failed']);
        }

        return true;
    }
}
