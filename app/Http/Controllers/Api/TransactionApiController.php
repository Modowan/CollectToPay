<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Transaction;
use App\Models\Tenant\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TransactionApiController extends Controller
{
    /**
     * Récupérer la liste des transactions
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

        $query = Transaction::with('customer');

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
            $query->join('customers', 'transactions.customer_id', '=', 'customers.id')
                  ->orderBy('customers.name', $sortDir)
                  ->select('transactions.*');
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        // Paginer les résultats
        $transactions = $query->paginate($perPage);

        return response()->json($transactions);
    }

    /**
     * Récupérer une transaction spécifique
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $transaction = Transaction::with('customer')->findOrFail($id);
        return response()->json($transaction);
    }

    /**
     * Créer une nouvelle transaction
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Vérifier que le client a un token
        $customer = Customer::findOrFail($request->input('customer_id'));
        if ($customer->token_status !== 'tokenized') {
            return response()->json(['error' => 'Le client doit être tokenisé pour effectuer une transaction'], 422);
        }

        // Créer la transaction avec statut initial "pending"
        $transaction = new Transaction($request->all());
        $transaction->status = 'pending';
        $transaction->save();

        // Simuler une autorisation de paiement
        // Dans un cas réel, vous appelleriez l'API ixopay ici
        $transaction->status = 'authorized';
        $transaction->reference = 'AUTH-' . uniqid();
        $transaction->save();

        return response()->json($transaction, 201);
    }

    /**
     * Capturer une transaction autorisée
     *
     * @param int $id
     * @return JsonResponse
     */
    public function capture(int $id): JsonResponse
    {
        $transaction = Transaction::findOrFail($id);

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
     * Rembourser une transaction capturée
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function refund(Request $request, int $id): JsonResponse
    {
        $transaction = Transaction::findOrFail($id);

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
     * Obtenir des statistiques sur les transactions
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
        $totalTransactions = Transaction::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $totalAmount = Transaction::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'captured')
            ->sum('amount');
        
        // Transactions par statut
        $byStatus = Transaction::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get();
        
        // Transactions par jour
        $byDay = Transaction::whereBetween('created_at', [$dateFrom, $dateTo])
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
