<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\Branch;
use App\Models\Tenant\Customer;
use App\Models\Tenant\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Afficher le tableau de bord du tenant.
     */
    public function index()
    {
        $user = Auth::user();
        $branch = $user->branch;

        // Si l'utilisateur est un administrateur de filiale, il voit les données de sa filiale
        // Sinon, on récupère toutes les données de l'entreprise
        if ($user->role === 'branch_admin') {
            $totalCustomers = Customer::where('branch_id', $branch->id)->count();
            $tokenizedCustomers = Customer::where('branch_id', $branch->id)
                ->where('token_status', 'tokenized')
                ->count();
            $pendingRequests = Customer::where('branch_id', $branch->id)
                ->whereIn('token_status', ['pending', 'sent'])
                ->count();
            $recentTransactions = PaymentTransaction::whereHas('customer', function ($query) use ($branch) {
                $query->where('branch_id', $branch->id);
            })->latest()->take(5)->get();
        } else {
            $totalCustomers = Customer::count();
            $tokenizedCustomers = Customer::where('token_status', 'tokenized')->count();
            $pendingRequests = Customer::whereIn('token_status', ['pending', 'sent'])->count();
            $recentTransactions = PaymentTransaction::latest()->take(5)->get();
        }

        $recentLogs = ActivityLog::latest('created_at')->take(10)->get();
        $branches = Branch::all();

        return view('tenant.dashboard', compact(
            'totalCustomers',
            'tokenizedCustomers',
            'pendingRequests',
            'recentTransactions',
            'recentLogs',
            'branches'
        ));
    }
}
