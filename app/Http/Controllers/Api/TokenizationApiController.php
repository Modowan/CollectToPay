<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TokenizationRequest;
use App\Models\Tenant\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TokenizationApiController extends Controller
{
    /**
     * Récupérer la liste des demandes de tokenisation
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $status = $request->input('status', '');

        $query = TokenizationRequest::with('customers');

        // Filtrer par statut si fourni
        if ($status) {
            $query->where('status', $status);
        }

        // Appliquer le tri
        $query->orderBy($sortBy, $sortDir);

        // Paginer les résultats
        $batches = $query->paginate($perPage);

        return response()->json(['batches' => $batches]);
    }

    /**
     * Récupérer une demande de tokenisation spécifique
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $batch = TokenizationRequest::with('customers')->findOrFail($id);
        return response()->json($batch);
    }

    /**
     * Créer une nouvelle demande de tokenisation
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_ids' => 'required|array',
            'customer_ids.*' => 'exists:customers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Créer la demande de tokenisation
        $batch = new TokenizationRequest();
        $batch->status = 'pending';
        $batch->save();

        // Récupérer les clients
        $customerIds = $request->input('customer_ids');
        $customers = Customer::whereIn('id', $customerIds)->get();

        // Associer les clients à la demande
        $batch->customers()->attach($customers, ['status' => 'pending']);

        // Mettre à jour le statut de tokenisation des clients
        Customer::whereIn('id', $customerIds)->update(['token_status' => 'pending']);

        return response()->json([
            'id' => $batch->id,
            'count' => count($customerIds)
        ], 201);
    }

    /**
     * Envoyer une demande de tokenisation à ixopay
     *
     * @param int $id
     * @return JsonResponse
     */
    public function send(int $id): JsonResponse
    {
        $batch = TokenizationRequest::with('customers')->findOrFail($id);

        if ($batch->status !== 'pending') {
            return response()->json(['error' => 'Seules les demandes en attente peuvent être envoyées'], 422);
        }

        // Simuler l'envoi à ixopay
        // Dans un cas réel, vous appelleriez l'API ixopay ici
        $batch->status = 'sent';
        $batch->sent_at = now();
        $batch->save();

        // Mettre à jour le statut des clients
        foreach ($batch->customers as $customer) {
            $customer->token_status = 'sent';
            $customer->save();
            
            // Mettre à jour la relation pivot
            $batch->customers()->updateExistingPivot($customer->id, ['status' => 'sent']);
        }

        return response()->json($batch);
    }

    /**
     * Vérifier le statut d'une demande de tokenisation
     *
     * @param int $id
     * @return JsonResponse
     */
    public function checkStatus(int $id): JsonResponse
    {
        $batch = TokenizationRequest::with('customers')->findOrFail($id);

        if ($batch->status === 'pending') {
            return response()->json(['error' => 'La demande n\'a pas encore été envoyée'], 422);
        }

        // Simuler la vérification du statut auprès d'ixopay
        // Dans un cas réel, vous appelleriez l'API ixopay ici
        
        // Simuler des résultats aléatoires pour les clients
        $statuses = ['tokenized', 'failed'];
        $allTokenized = true;
        $allFailed = true;

        foreach ($batch->customers as $customer) {
            if ($customer->pivot->status === 'sent') {
                // 80% de chance de succès
                $newStatus = (rand(1, 100) <= 80) ? 'tokenized' : 'failed';
                
                $customer->token_status = $newStatus;
                $customer->save();
                
                // Mettre à jour la relation pivot
                $batch->customers()->updateExistingPivot($customer->id, ['status' => $newStatus]);
                
                if ($newStatus === 'tokenized') {
                    $allFailed = false;
                } else {
                    $allTokenized = false;
                }
            } else if ($customer->pivot->status === 'tokenized') {
                $allFailed = false;
            } else if ($customer->pivot->status === 'failed') {
                $allTokenized = false;
            }
        }

        // Mettre à jour le statut du lot
        if ($allTokenized) {
            $batch->status = 'completed';
        } else if ($allFailed) {
            $batch->status = 'failed';
        }
        
        $batch->last_checked_at = now();
        $batch->save();

        return response()->json($batch);
    }

    /**
     * Obtenir des statistiques sur les tokenisations
     *
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        // Nombre total de clients par statut de tokenisation
        $byStatus = Customer::selectRaw('token_status, count(*) as count')
            ->groupBy('token_status')
            ->get();
        
        // Taux de réussite des tokenisations
        $totalRequests = TokenizationRequest::count();
        $completedRequests = TokenizationRequest::where('status', 'completed')->count();
        $successRate = $totalRequests > 0 ? ($completedRequests / $totalRequests) * 100 : 0;
        
        // Tokenisations par mois
        $byMonth = TokenizationRequest::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->limit(12)
            ->get();

        return response()->json([
            'by_status' => $byStatus,
            'success_rate' => $successRate,
            'by_month' => $byMonth,
            'total_requests' => $totalRequests,
            'completed_requests' => $completedRequests
        ]);
    }
}
