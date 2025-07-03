<?php

namespace App\Http\Controllers\Evervault;

use App\Http\Controllers\Controller;
use App\Models\Tenant\MemberTransfer;
use App\Services\Evervault\MemberTransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MemberTransferController extends Controller
{
    private $transferService;

    public function __construct(MemberTransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    /**
     * Créer un nouveau transfert entre membres
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_organization_id' => 'required|exists:organizations,id',
            'recipient_organization_id' => 'required|exists:organizations,id|different:sender_organization_id',
            'cardholder_first_name' => 'required|string|max:255',
            'cardholder_last_name' => 'required|string|max:255',
            'cardholder_phone' => 'required|string|max:20',
            'cardholder_email' => 'required|email|max:255',
            'max_amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|size:3',
            'notes' => 'nullable|string|max:1000',
            'expires_in_days' => 'nullable|integer|min:1|max:90',
            // Données de carte
            'card_data.card_number' => 'required|string',
            'card_data.exp_month' => 'required|integer|min:1|max:12',
            'card_data.exp_year' => 'required|integer|min:' . date('Y'),
            'card_data.cvc' => 'required|string|min:3|max:4',
            'card_data.cardholder_name' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $transfer = $this->transferService->createMemberTransfer($request->all());

            return response()->json([
                'success' => true,
                'data' => $transfer->load(['cardToken', 'senderOrganization', 'recipientOrganization'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du transfert',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lister les transferts disponibles pour une organisation
     */
    public function available(Request $request)
    {
        try {
            $organizationId = $request->get('organization_id');
            
            if (!$organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'organization_id requis'
                ], 400);
            }

            $transfers = $this->transferService->getAvailableTransfersForOrganization($organizationId);

            return response()->json([
                'success' => true,
                'data' => $transfers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des transferts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lister les transferts envoyés par une organisation
     */
    public function sent(Request $request)
    {
        try {
            $organizationId = $request->get('organization_id');

            if (!$organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'organization_id requis'
                ], 400);
            }

            $transfers = MemberTransfer::with(['cardToken', 'recipientOrganization', 'transactions'])
                ->where('sender_organization_id', $organizationId)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $transfers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des transferts envoyés',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lister les transferts reçus par une organisation
     */
    public function received(Request $request)
    {
        try {
            $organizationId = $request->get('organization_id');

            if (!$organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'organization_id requis'
                ], 400);
            }

            $transfers = MemberTransfer::with(['cardToken', 'senderOrganization', 'transactions'])
                ->where('recipient_organization_id', $organizationId)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json([
                'success' => true,
                'data' => $transfers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des transferts reçus',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vérifier si un transfert peut être utilisé
     */
    public function canUse(Request $request, int $transferId)
    {
        try {
            $organizationId = $request->get('organization_id');
            
            if (!$organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'organization_id requis'
                ], 400);
            }

            $transfer = MemberTransfer::findOrFail($transferId);

            $canUse = $this->transferService->canMemberUseTransfer($transfer, $organizationId);

            return response()->json([
                'success' => true,
                'can_use' => $canUse,
                'transfer' => $transfer->load(['cardToken', 'senderOrganization']),
                'reasons' => $this->transferService->getUsageReasons($transfer, $organizationId)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la vérification du transfert',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les détails d'un transfert
     */
    public function show(Request $request, int $transferId)
    {
        try {
            $transfer = MemberTransfer::with([
                'cardToken', 
                'senderOrganization', 
                'recipientOrganization', 
                'transactions'
            ])->findOrFail($transferId);

            return response()->json([
                'success' => true,
                'data' => $transfer
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transfert non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Marquer un transfert comme utilisé
     */
    public function markAsUsed(Request $request, int $transferId)
    {
        $validator = Validator::make($request->all(), [
            'organization_id' => 'required|exists:organizations,id',
            'amount_used' => 'required|numeric|min:0.01',
            'transaction_reference' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $transfer = MemberTransfer::findOrFail($transferId);

            if (!$this->transferService->canMemberUseTransfer($transfer, $request->organization_id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à utiliser ce transfert'
                ], 403);
            }

            $result = $this->transferService->markTransferAsUsed(
                $transfer,
                $request->amount_used,
                $request->transaction_reference
            );

            return response()->json([
                'success' => true,
                'message' => 'Transfert marqué comme utilisé',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du transfert',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Annuler un transfert
     */
    public function cancel(Request $request, int $transferId)
    {
        try {
            $transfer = MemberTransfer::findOrFail($transferId);

            // Vérifier que l'utilisateur peut annuler ce transfert
            $organizationId = $request->get('organization_id');
            if ($transfer->sender_organization_id != $organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seul l\'expéditeur peut annuler un transfert'
                ], 403);
            }

            if ($transfer->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les transferts actifs peuvent être annulés'
                ], 400);
            }

            $transfer->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transfert annulé avec succès'
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
     * Obtenir les statistiques des transferts pour une organisation
     */
    public function stats(Request $request)
    {
        try {
            $organizationId = $request->get('organization_id');

            if (!$organizationId) {
                return response()->json([
                    'success' => false,
                    'message' => 'organization_id requis'
                ], 400);
            }

            $stats = $this->transferService->getTransferStatsForOrganization($organizationId);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

