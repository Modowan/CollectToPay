<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\CustomerController;
use App\Http\Controllers\Tenant\PaymentTokenController;
use App\Http\Controllers\Tenant\PaymentTransactionController;

/*
|--------------------------------------------------------------------------
| Routes Tenant
|--------------------------------------------------------------------------
|
| Ces routes sont spécifiques aux tenants et ne sont accessibles que lorsqu'un
| tenant est identifié. Elles sont automatiquement préfixées par le domaine
| du tenant ou par le préfixe de chemin configuré.
|
*/

// Routes protégées par authentification
Route::middleware(['web', 'auth'])->group(function () {
    // Tableau de bord du tenant
    Route::get('/dashboard', function () {
        return view('tenant.dashboard');
    })->name('tenant.dashboard');
    
    // Routes pour les clients
    Route::get('/branches/{branch}/customers', [CustomerController::class, 'index'])->name('branches.customers.index');
    Route::get('/branches/{branch}/customers/create', [CustomerController::class, 'create'])->name('branches.customers.create');
    Route::post('/branches/{branch}/customers', [CustomerController::class, 'store'])->name('branches.customers.store');
    Route::get('/branches/{branch}/customers/{customerId}', [CustomerController::class, 'show'])->name('branches.customers.show');
    Route::get('/branches/{branch}/customers/{customerId}/edit', [CustomerController::class, 'edit'])->name('branches.customers.edit');
    Route::put('/branches/{branch}/customers/{customerId}', [CustomerController::class, 'update'])->name('branches.customers.update');
    Route::delete('/branches/{branch}/customers/{customerId}', [CustomerController::class, 'destroy'])->name('branches.customers.destroy');
    Route::post('/branches/{branch}/customers/import', [CustomerController::class, 'import'])->name('branches.customers.import');
    
    // Routes pour les tokens de paiement
    Route::get('/branches/{branch}/customers/{customerId}/payment-tokens', [PaymentTokenController::class, 'index'])->name('branches.customers.payment-tokens.index');
    Route::get('/branches/{branch}/customers/{customerId}/payment-tokens/create', [PaymentTokenController::class, 'create'])->name('branches.customers.payment-tokens.create');
    Route::post('/branches/{branch}/customers/{customerId}/payment-tokens', [PaymentTokenController::class, 'store'])->name('branches.customers.payment-tokens.store');
    Route::get('/branches/{branch}/customers/{customerId}/payment-tokens/{tokenId}', [PaymentTokenController::class, 'show'])->name('branches.customers.payment-tokens.show');
    Route::post('/branches/{branch}/customers/{customerId}/payment-tokens/{tokenId}/set-default', [PaymentTokenController::class, 'setDefault'])->name('branches.customers.payment-tokens.set-default');
    Route::post('/branches/{branch}/customers/{customerId}/payment-tokens/{tokenId}/revoke', [PaymentTokenController::class, 'revoke'])->name('branches.customers.payment-tokens.revoke');
    
    // Routes pour les transactions
    Route::get('/branches/{branch}/customers/{customerId}/payment-transactions', [PaymentTransactionController::class, 'index'])->name('branches.customers.payment-transactions.index');
    Route::get('/branches/{branch}/customers/{customerId}/payment-transactions/create', [PaymentTransactionController::class, 'create'])->name('branches.customers.payment-transactions.create');
    Route::post('/branches/{branch}/customers/{customerId}/payment-transactions', [PaymentTransactionController::class, 'store'])->name('branches.customers.payment-transactions.store');
    Route::get('/branches/{branch}/customers/{customerId}/payment-transactions/{transactionId}', [PaymentTransactionController::class, 'show'])->name('branches.customers.payment-transactions.show');
    Route::post('/branches/{branch}/customers/{customerId}/payment-transactions/{transactionId}/refund', [PaymentTransactionController::class, 'refund'])->name('branches.customers.payment-transactions.refund');
});

// Routes API pour les tenants
Route::prefix('api')->middleware(['api', 'auth:sanctum'])->group(function () {
    // API pour les clients
    Route::get('/customers', [App\Http\Controllers\Api\Tenant\CustomerApiController::class, 'index']);
    Route::get('/customers/{customer}', [App\Http\Controllers\Api\Tenant\CustomerApiController::class, 'show']);
    Route::post('/customers', [App\Http\Controllers\Api\Tenant\CustomerApiController::class, 'store']);
    Route::put('/customers/{customer}', [App\Http\Controllers\Api\Tenant\CustomerApiController::class, 'update']);
    Route::delete('/customers/{customer}', [App\Http\Controllers\Api\Tenant\CustomerApiController::class, 'destroy']);
    
    // API pour les tokens de paiement
    Route::get('/customers/{customer}/payment-tokens', [App\Http\Controllers\Api\Tenant\PaymentTokenApiController::class, 'index']);
    Route::post('/customers/{customer}/payment-tokens', [App\Http\Controllers\Api\Tenant\PaymentTokenApiController::class, 'store']);
    Route::post('/payment-tokens/{token}/set-default', [App\Http\Controllers\Api\Tenant\PaymentTokenApiController::class, 'setDefault']);
    Route::post('/payment-tokens/{token}/revoke', [App\Http\Controllers\Api\Tenant\PaymentTokenApiController::class, 'revoke']);
    
    // API pour les transactions
    Route::get('/customers/{customer}/payment-transactions', [App\Http\Controllers\Api\Tenant\PaymentTransactionApiController::class, 'index']);
    Route::post('/customers/{customer}/payment-transactions', [App\Http\Controllers\Api\Tenant\PaymentTransactionApiController::class, 'store']);
    Route::get('/payment-transactions/{transaction}', [App\Http\Controllers\Api\Tenant\PaymentTransactionApiController::class, 'show']);
    Route::post('/payment-transactions/{transaction}/refund', [App\Http\Controllers\Api\Tenant\PaymentTransactionApiController::class, 'refund']);
});
