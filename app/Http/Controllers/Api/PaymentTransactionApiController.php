<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PaymentTransaction;
use App\Models\Tenant\Customer;
use App\Models\Tenant\PaymentToken;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PaymentTransactionApiController extends Controller
{
    /**
     * Récupérer la liste des transactions de paiement
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
        $dateFrom = $request->input('date_from', '');
        $dateTo = $request->input('date_to', '');

        $query = PaymentTransaction::with(['customer', 'paymentToken']);

        // Filtrer par client si fourni
        if ($customerId) {
            $query->where('customer_id', $customerId);
        }

        // Filtrer par statut si fourni
        if ($status) {
            $query->where('status', $status);
        }

        // Filtrer par date de début si fournie
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        // Filtrer par date de fin si fournie
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Appliquer le tri
        if ($sortBy === 'customer.name') {
            $query->join('customers', 'payment_transactions.customer_id', '=', 'customers.id')
                  ->orderBy('customers.name', $sortDir)
                  ->select('payment_transactions.*');
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        // Paginer les résultats
        $transactions = $query->paginate($perPage);

        return response()->json($transactions);
    }

    /**
     * Récupérer une transaction de paiement spécifique
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $transaction = PaymentTransaction::with(['customer', 'paymentToken'])->findOrFail($id);
        return response()->json($transaction);
    }

    /**
     * Créer une nouvelle transaction de paiement
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'payment_token_id' => 'required|exists:payment_tokens,id',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Vérifier que le token appartient bien au client
        $token = PaymentToken::findOrFail($request->input('payment_token_id'));
        if ($token->customer_id != $request->input('customer_id')) {
            return response()->json(['error' => 'Le token de paiement n\'appartient pas à ce client'], 422);
        }

        // Vérifier que le token est actif
        if ($token->status !== 'active') {
            return response()->json(['error' => 'Le token de paiement n\'est pas actif'], 422);
        }

        // Créer la transaction avec statut initial "pending"
        $transaction = new PaymentTransaction($request->all());
        $transaction->status = 'pending';
        $transaction->reference = 'txn_' . Str::random(16);
        $transaction->save();

        // Simuler une autorisation de paiement
        // Dans un cas réel, vous appelleriez l'API ixopay ici
        $transaction->status = 'authorized';
        $transaction->authorized_at = now();
        $transaction->save();

        return response()->json($transaction, 201);
    }

    /**
     * Capturer une transaction de paiement autorisée
     *
     * @param int $id
     * @return JsonResponse
     */
    public function capture(int $id): JsonResponse
    {
        $transaction = PaymentTransaction::findOrFail($id);

        if ($transaction->status !== 'authorized') {
            return response()->json(['error' => 'Seules les transactions autorisées peuvent être capturées'], 422);
        }

        // Simuler une capture de paiement
        // Dans un cas réel, vous appelleriez l'API ixopay ici
        $transaction->status = 'captured';
        $transaction->captured_at = now();
        $transaction->save();

        return response()->json($transaction);
    }

    /**
     * Annuler une transaction de paiement autorisée
     *
     * @param int $id
     * @return JsonResponse
     */
    public function void(int $id): JsonResponse
    {
        $transaction = PaymentTransaction::findOrFail($id);

        if ($transaction->status !== 'authorized') {
            return response()->json(['error' => 'Seules les transactions autorisées peuvent être annulées'], 422);
        }

        // Simuler une annulation de paiement
        // Dans un cas réel, vous appelleriez l'API ixopay ici
        $transaction->status = 'voided';
        $transaction->voided_at = now();
        $transaction->save();

        return response()->json($transaction);
    }

    /**
     * Rembourser une transaction de paiement capturée
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function refund(Request $request, int $id): JsonResponse
    {
        $transaction = PaymentTransaction::findOrFail($id);

        if ($transaction->status !== 'captured') {
            return response()->json(['error' => 'Seules les transactions capturées peuvent être remboursées'], 422);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'nullable|numeric|min:0.01|max:' . $transaction->amount,
            'reason' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Déterminer le montant du remboursement
        $refundAmount = $request->input('amount', $transaction->amount);

        // Simuler un remboursement
        // Dans un cas réel, vous appelleriez l'API ixopay ici
        $transaction->status = 'refunded';
        $transaction->refunded_amount = $refundAmount;
        $transaction->refund_reason = $request->input('reason');
        $transaction->refunded_at = now();
        $transaction->save();

        return response()->json($transaction);
    }

    /**
     * Obtenir des statistiques sur les transactions de paiement
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function statistics(Request $request): JsonResponse
    {
        $period = $request->input('period', 'month');
        $dateFrom = null;
        $dateTo = now();

        // Déterminer la période
        switch ($period) {
            case 'week':
                $dateFrom = now()->subWeek();
                break;
            case 'month':
                $dateFrom = now()->subMonth();
                break;
            case 'quarter':
                $dateFrom = now()->subMonths(3);
                break;
            case 'year':
                $dateFrom = now()->subYear();
                break;
            default:
                $dateFrom = now()->subMonth();
        }

        // Statistiques globales
        $totalTransactions = PaymentTransaction::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $totalAmount = PaymentTransaction::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'captured')
            ->sum('amount');
        
        // Transactions par statut
        $byStatus = PaymentTransaction::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();
        
        // Transactions par jour
        $byDay = PaymentTransaction::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(created_at) as date, count(*) as count, sum(case when status = "captured" then amount else 0 end) as amount')
            ->groupBy('date')
            ->get();

        return response()->json([
            'total_transactions' => $totalTransactions,
            'total_amount' => $totalAmount,
            'by_status' => $byStatus,
            'by_day' => $byDay,
            'period' => [
                'from' => $dateFrom->toDateString(),
                'to' => $dateTo->toDateString(),
            ]
        ]);
    }
}
