<?php

namespace App\Http\Controllers\Evervault;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PaymentRequest;
use App\Services\Evervault\CardDataCollectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentRequestController extends Controller
{
    private $cardDataService;

    public function __construct(CardDataCollectionService $cardDataService)
    {
        $this->cardDataService = $cardDataService;
    }

    /**
     * Créer une nouvelle demande de paiement
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'client_first_name' => 'required|string|max:255',
            'client_last_name' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'client_phone' => 'nullable|string|max:20',
            'max_amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'description' => 'nullable|string|max:1000',
            'expires_in_days' => 'nullable|integer|min:1|max:30'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $paymentRequest = $this->cardDataService->createPaymentRequest($request->all());

            return response()->json([
                'success' => true,
                'data' => [
                    'payment_request' => $paymentRequest,
                    'secure_url' => $paymentRequest->getSecureUrl()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la demande de paiement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher le formulaire de collecte sécurisé
     */
    public function showCollectionForm(string $token)
    {
        try {
            $paymentRequest = PaymentRequest::where('secure_token', $token)
                ->where('status', 'pending')
                ->first();

            if (!$paymentRequest || $paymentRequest->isExpired()) {
                return view('evervault.expired');
            }

            return view('evervault.collection-form', compact('paymentRequest'));

        } catch (\Exception $e) {
            return view('evervault.error', ['message' => 'Erreur lors du chargement du formulaire']);
        }
    }

    /**
     * Traiter les données de carte collectées
     */
    public function processCardData(Request $request, string $token)
    {
        $validator = Validator::make($request->all(), [
            'card_number' => 'required|string',
            'exp_month' => 'required|integer|min:1|max:12',
            'exp_year' => 'required|integer|min:' . date('Y'),
            'cvc' => 'required|string|min:3|max:4',
            'cardholder_name' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $paymentRequest = PaymentRequest::where('secure_token', $token)
                ->where('status', 'pending')
                ->first();

            if (!$paymentRequest || $paymentRequest->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Demande expirée ou invalide'
                ], 400);
            }

            $cardToken = $this->cardDataService->processCollectedCardData(
                $paymentRequest,
                $request->all()
            );

            return response()->json([
                'success' => true,
                'message' => 'Données bancaires collectées avec succès',
                'data' => [
                    'card_mask' => $cardToken->card_mask,
                    'card_type' => $cardToken->card_type
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement des données',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lister les demandes de paiement d'une organisation
     */
    public function index(Request $request)
    {
        try {
            $organizationId = $request->get('organization_id');
            $status = $request->get('status');

            $query = PaymentRequest::with(['cardToken', 'transactions'])
                ->where('organization_id', $organizationId);

            if ($status) {
                $query->where('status', $status);
            }

            $paymentRequests = $query->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $paymentRequests
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des demandes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les détails d'une demande de paiement
     */
    public function show(Request $request, int $paymentRequestId)
    {
        try {
            $paymentRequest = PaymentRequest::with(['cardToken', 'transactions', 'organization'])
                ->findOrFail($paymentRequestId);

            return response()->json([
                'success' => true,
                'data' => $paymentRequest
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Demande de paiement non trouvée',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Annuler une demande de paiement
     */
    public function cancel(Request $request, int $paymentRequestId)
    {
        try {
            $paymentRequest = PaymentRequest::findOrFail($paymentRequestId);

            if ($paymentRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seules les demandes en attente peuvent être annulées'
                ], 400);
            }

            $paymentRequest->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Demande de paiement annulée avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Renvoyer une demande de paiement
     */
    public function resend(Request $request, int $paymentRequestId)
    {
        try {
            $paymentRequest = PaymentRequest::findOrFail($paymentRequestId);

            if ($paymentRequest->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seules les demandes en attente peuvent être renvoyées'
                ], 400);
            }

            // Étendre la date d'expiration
            $paymentRequest->update([
                'expires_at' => now()->addDays(7),
                'resent_at' => now()
            ]);

            // Renvoyer la notification
            $this->cardDataService->resendPaymentRequestNotification($paymentRequest);

            return response()->json([
                'success' => true,
                'message' => 'Demande de paiement renvoyée avec succès',
                'data' => [
                    'new_expiry' => $paymentRequest->expires_at,
                    'secure_url' => $paymentRequest->getSecureUrl()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du renvoi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

