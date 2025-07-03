<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IxopayController;
use App\Http\Controllers\Api\TenantApiController;
use App\Http\Controllers\Api\BranchApiController;
use App\Http\Controllers\Api\CustomerApiController;
use App\Http\Controllers\Api\PaymentTokenApiController;
use App\Http\Controllers\Api\PaymentTransactionApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Ici sont définies les routes API pour l'application. Ces routes sont chargées
| par le RouteServiceProvider et toutes seront assignées au groupe de middleware "api".
| Créez votre API RESTful !
|
*/

// Routes d'authentification API
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:sanctum');

// Routes protégées par authentification API
Route::middleware('auth:sanctum')->group(function () {
    // Informations sur l'utilisateur connecté
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Routes pour les tenants
    Route::get('/tenants', [TenantApiController::class, 'index']);
    Route::get('/tenants/{tenant}', [TenantApiController::class, 'show']);
    Route::post('/tenants', [TenantApiController::class, 'store'])->middleware('role:admin');
    Route::put('/tenants/{tenant}', [TenantApiController::class, 'update'])->middleware('role:admin');
    Route::delete('/tenants/{tenant}', [TenantApiController::class, 'destroy'])->middleware('role:admin');
    
    // Routes pour les branches
    Route::get('/tenants/{tenant}/branches', [BranchApiController::class, 'index']);
    Route::get('/branches/{branch}', [BranchApiController::class, 'show']);
    Route::post('/tenants/{tenant}/branches', [BranchApiController::class, 'store'])->middleware('role:admin,tenant_admin');
    Route::put('/branches/{branch}', [BranchApiController::class, 'update'])->middleware('role:admin,tenant_admin');
    Route::delete('/branches/{branch}', [BranchApiController::class, 'destroy'])->middleware('role:admin,tenant_admin');
    
    // Routes pour les clients
    Route::get('/branches/{branch}/customers', [CustomerApiController::class, 'index']);
    Route::get('/customers/{customer}', [CustomerApiController::class, 'show']);
    Route::post('/branches/{branch}/customers', [CustomerApiController::class, 'store']);
    Route::put('/customers/{customer}', [CustomerApiController::class, 'update']);
    Route::delete('/customers/{customer}', [CustomerApiController::class, 'destroy']);
    
    // Routes pour les tokens de paiement
    Route::get('/customers/{customer}/payment-tokens', [PaymentTokenApiController::class, 'index']);
    Route::post('/payment-tokens/{token}/set-default', [PaymentTokenApiController::class, 'setDefault']);
    Route::post('/payment-tokens/{token}/revoke', [PaymentTokenApiController::class, 'revoke']);
    
    // Routes pour les transactions
    Route::get('/customers/{customer}/payment-transactions', [PaymentTransactionApiController::class, 'index']);
    Route::post('/customers/{customer}/payment-transactions', [PaymentTransactionApiController::class, 'store']);
    Route::get('/payment-transactions/{transaction}', [PaymentTransactionApiController::class, 'show']);
    Route::post('/payment-transactions/{transaction}/refund', [PaymentTransactionApiController::class, 'refund'])->middleware('role:admin,tenant_admin');
});

// Routes pour l'intégration ixopay
Route::prefix('ixopay')->group(function () {
    // Route pour générer un lien de tokenisation
    Route::post('/tokenization-link', [IxopayController::class, 'generateTokenizationLink'])->middleware('auth:sanctum');
    
    // Webhooks ixopay (non protégés par authentification)
    Route::post('/webhook/tokenization', [IxopayController::class, 'tokenizationWebhook']);
    Route::post('/webhook/transaction', [IxopayController::class, 'transactionWebhook']);
});
