<?php

namespace App\Services;

use App\Models\Tenant\PaymentToken;
use App\Models\Tenant\PaymentTransaction;
use App\Models\Tenant\Customer;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service pour l'intégration avec l'API ixopay
 * 
 * Cette classe gère toutes les interactions avec l'API ixopay pour la tokenisation
 * des cartes bancaires et le traitement des paiements.
 */
class IxopayService
{
    /**
     * URL de base de l'API ixopay
     *
     * @var string
     */
    protected $apiUrl;

    /**
     * Clé API pour l'authentification
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Secret API pour la signature des requêtes
     *
     * @var string
     */
    protected $apiSecret;

    /**
     * Constructeur du service
     */
    public function __construct()
    {
        $this->apiUrl = config('services.ixopay.endpoint');
        $this->apiKey = config('services.ixopay.api_key');
        $this->apiSecret = config('services.ixopay.api_secret');
    }

    /**
     * Générer un lien de tokenisation pour un client
     *
     * @param Customer $customer Le client pour lequel générer le lien
     * @param string $returnUrl URL de retour après la tokenisation
     * @return string|null URL de tokenisation ou null en cas d'erreur
     */
    public function generateTokenizationLink(Customer $customer, string $returnUrl)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/tokenization/link', [
                'customer' => [
                    'id' => $customer->id,
                    'email' => $customer->email,
                    'name' => $customer->name,
                ],
                'returnUrl' => $returnUrl,
                'signature' => $this->generateSignature([
                    'customer_id' => $customer->id,
                    'email' => $customer->email,
                    'timestamp' => time(),
                ]),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['tokenizationUrl'] ?? null;
            }

            Log::error('Erreur lors de la génération du lien de tokenisation ixopay', [
                'customer_id' => $customer->id,
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Exception lors de la génération du lien de tokenisation ixopay', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Enregistrer un token reçu d'ixopay
     *
     * @param array $tokenData Données du token reçues d'ixopay
     * @param Customer $customer Le client associé au token
     * @return PaymentToken|null Le token créé ou null en cas d'erreur
     */
    public function saveToken(array $tokenData, Customer $customer)
    {
        try {
            // Vérifier la signature des données reçues
            if (!$this->verifySignature($tokenData)) {
                Log::error('Signature invalide pour les données de token ixopay', [
                    'customer_id' => $customer->id,
                    'token_data' => $tokenData,
                ]);
                return null;
            }

            // Créer le token dans la base de données
            $token = new PaymentToken([
                'customer_id' => $customer->id,
                'token' => $tokenData['token'],
                'card_type' => $tokenData['cardType'] ?? null,
                'last_four' => $tokenData['lastFour'] ?? null,
                'expiry_month' => $tokenData['expiryMonth'] ?? null,
                'expiry_year' => $tokenData['expiryYear'] ?? null,
                'is_default' => !$customer->paymentTokens()->exists(), // Premier token par défaut
                'status' => 'active',
            ]);

            $token->save();
            return $token;
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'enregistrement du token ixopay', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Traiter une transaction de paiement
     *
     * @param PaymentTransaction $transaction La transaction à traiter
     * @return bool Succès ou échec de la transaction
     */
    public function processTransaction(PaymentTransaction $transaction)
    {
        try {
            // Récupérer le token associé à la transaction
            $token = $transaction->paymentToken;
            
            if (!$token || $token->status !== 'active') {
                Log::error('Token invalide pour la transaction', [
                    'transaction_id' => $transaction->id,
                    'token_id' => $transaction->token_id,
                ]);
                
                $transaction->update([
                    'status' => 'failed',
                ]);
                
                return false;
            }

            // Appeler l'API ixopay pour traiter la transaction
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/transactions/debit', [
                'token' => $token->token,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
                'description' => $transaction->description,
                'reference' => $transaction->reference,
                'signature' => $this->generateSignature([
                    'token' => $token->token,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'timestamp' => time(),
                ]),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'approved') {
                    $transaction->update([
                        'status' => 'completed',
                        'transaction_id' => $data['transactionId'] ?? null,
                    ]);
                    
                    return true;
                } else {
                    $transaction->update([
                        'status' => 'failed',
                        'transaction_id' => $data['transactionId'] ?? null,
                    ]);
                    
                    Log::error('Transaction refusée par ixopay', [
                        'transaction_id' => $transaction->id,
                        'ixopay_transaction_id' => $data['transactionId'] ?? null,
                        'reason' => $data['reason'] ?? 'Raison inconnue',
                    ]);
                    
                    return false;
                }
            }

            Log::error('Erreur lors du traitement de la transaction ixopay', [
                'transaction_id' => $transaction->id,
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            $transaction->update([
                'status' => 'failed',
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception lors du traitement de la transaction ixopay', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            $transaction->update([
                'status' => 'failed',
            ]);

            return false;
        }
    }

    /**
     * Rembourser une transaction
     *
     * @param PaymentTransaction $transaction La transaction à rembourser
     * @return bool Succès ou échec du remboursement
     */
    public function refundTransaction(PaymentTransaction $transaction)
    {
        try {
            if ($transaction->status !== 'completed') {
                Log::error('Impossible de rembourser une transaction non complétée', [
                    'transaction_id' => $transaction->id,
                    'status' => $transaction->status,
                ]);
                
                return false;
            }

            // Appeler l'API ixopay pour rembourser la transaction
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl . '/transactions/refund', [
                'transactionId' => $transaction->transaction_id,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
                'reason' => 'Remboursement demandé par l\'administrateur',
                'signature' => $this->generateSignature([
                    'transactionId' => $transaction->transaction_id,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                    'timestamp' => time(),
                ]),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'approved') {
                    $transaction->update([
                        'status' => 'refunded',
                    ]);
                    
                    return true;
                } else {
                    Log::error('Remboursement refusé par ixopay', [
                        'transaction_id' => $transaction->id,
                        'reason' => $data['reason'] ?? 'Raison inconnue',
                    ]);
                    
                    return false;
                }
            }

            Log::error('Erreur lors du remboursement de la transaction ixopay', [
                'transaction_id' => $transaction->id,
                'response' => $response->json(),
                'status' => $response->status(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception lors du remboursement de la transaction ixopay', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Générer une signature pour les requêtes API
     *
     * @param array $data Données à signer
     * @return string Signature générée
     */
    protected function generateSignature(array $data)
    {
        // Trier les données par clé pour assurer la cohérence
        ksort($data);
        
        // Concaténer les valeurs
        $stringToSign = implode('|', $data);
        
        // Ajouter le secret API
        $stringToSign .= '|' . $this->apiSecret;
        
        // Générer la signature HMAC
        return hash_hmac('sha256', $stringToSign, $this->apiSecret);
    }

    /**
     * Vérifier la signature des données reçues d'ixopay
     *
     * @param array $data Données reçues avec signature
     * @return bool Validité de la signature
     */
    protected function verifySignature(array $data)
    {
        if (!isset($data['signature'])) {
            return false;
        }
        
        $receivedSignature = $data['signature'];
        unset($data['signature']);
        
        $calculatedSignature = $this->generateSignature($data);
        
        return hash_equals($calculatedSignature, $receivedSignature);
    }
}
