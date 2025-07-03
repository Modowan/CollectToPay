<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PaymentToken;
use App\Models\Tenant\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentTokenApiController extends Controller
{
    /**
     * Récupérer la liste des tokens de paiement
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $customerId = $request->input('customer_id', '');
        $status = $request->input('status', '');

        $query = PaymentToken::with('customer');

        // Filtrer par client si fourni
        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        // Filtrer par statut si fourni
        if ($status) {
            $query->where('status', $status);
        }

        // Appliquer le tri
        if ($sortBy === 'customer.name') {
            $query->join('customers', 'payment_tokens.customer_id', '=', 'customers.id')
                  ->orderBy('customers.name', $sortDir)
                  ->select('payment_tokens.*');
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        // Paginer les résultats
        $tokens = $query->paginate($perPage);

        return response()->json($tokens);
    }

    /**
     * Récupérer un token de paiement spécifique
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $token = PaymentToken::with('customer')->findOrFail($id);
        return response()->json($token);
    }

    /**
     * Créer un nouveau token de paiement
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'card_type' => 'required|string|max:50',
            'last_four' => 'required|string|size:4',
            'expiry_month' => 'required|integer|min:1|max:12',
            'expiry_year' => 'required|integer|min:' . date('Y'),
            'cardholder_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Vérifier que le client n'a pas déjà un token actif
        $existingToken = PaymentToken::where('customer_id', $request->input('customer_id'))
            ->where('status', 'active')
            ->first();

        if ($existingToken) {
            return response()->json(['error' => 'Le client possède déjà un token de paiement actif'], 422);
        }

        // Créer le token de paiement
        $token = new PaymentToken($request->all());
        $token->token = 'tok_' . Str::random(24);
        $token->status = 'active';
        $token->save();

        // Mettre à jour le statut de tokenisation du client
        $customer = Customer::findOrFail($request->input('customer_id'));
        $customer->token_status = 'tokenized';
        $customer->save();

        return response()->json($token, 201);
    }

    /**
     * Mettre à jour un token de paiement existant
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $token = PaymentToken::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'card_type' => 'required|string|max:50',
            'last_four' => 'required|string|size:4',
            'expiry_month' => 'required|integer|min:1|max:12',
            'expiry_year' => 'required|integer|min:' . date('Y'),
            'cardholder_name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive,expired',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $token->update($request->all());

        // Si le token est désactivé, mettre à jour le statut de tokenisation du client
        if ($request->input('status') !== 'active') {
            $customer = Customer::findOrFail($token->customer_id);
            $customer->token_status = 'none';
            $customer->save();
        }

        return response()->json($token);
    }

    /**
     * Désactiver un token de paiement
     *
     * @param int $id
     * @return JsonResponse
     */
    public function deactivate(int $id): JsonResponse
    {
        $token = PaymentToken::findOrFail($id);

        if ($token->status !== 'active') {
            return response()->json(['error' => 'Le token est déjà inactif'], 422);
        }

        $token->status = 'inactive';
        $token->save();

        // Mettre à jour le statut de tokenisation du client
        $customer = Customer::findOrFail($token->customer_id);
        $customer->token_status = 'none';
        $customer->save();

        return response()->json($token);
    }

    /**
     * Vérifier la validité d'un token de paiement
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $token = PaymentToken::where('token', $request->input('token'))->first();

        if (!$token) {
            return response()->json(['valid' => false, 'error' => 'Token introuvable'], 404);
        }

        $isValid = $token->status === 'active';
        $isExpired = false;

        // Vérifier si le token est expiré
        if ($isValid) {
            $expiryDate = \DateTime::createFromFormat('Y-m', $token->expiry_year . '-' . $token->expiry_month);
            $expiryDate->modify('last day of this month');
            $now = new \DateTime();
            
            if ($expiryDate < $now) {
                $isValid = false;
                $isExpired = true;
                
                // Mettre à jour le statut du token
                $token->status = 'expired';
                $token->save();
                
                // Mettre à jour le statut de tokenisation du client
                $customer = Customer::findOrFail($token->customer_id);
                $customer->token_status = 'none';
                $customer->save();
            }
        }

        return response()->json([
            'valid' => $isValid,
            'expired' => $isExpired,
            'token' => $isValid ? $token : null
        ]);
    }

    /**
     * Obtenir des statistiques sur les tokens de paiement
     *
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        // Nombre total de tokens par statut
        $byStatus = PaymentToken::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();
        
        // Nombre total de tokens par type de carte
        $byCardType = PaymentToken::selectRaw('card_type, count(*) as count')
            ->groupBy('card_type')
            ->get();
        
        // Tokens créés par mois
        $byMonth = PaymentToken::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, count(*) as count')
            ->groupBy('month')
            ->orderBy('month')
            ->limit(12)
            ->get();

        return response()->json([
            'by_status' => $byStatus,
            'by_card_type' => $byCardType,
            'by_month' => $byMonth,
            'total_tokens' => PaymentToken::count(),
            'active_tokens' => PaymentToken::where('status', 'active')->count()
        ]);
    }
}
