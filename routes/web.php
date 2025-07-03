<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\SetPasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\CustomerImportController;
use App\Http\Controllers\Customer\CustomerProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Ici sont définies les routes web pour l'application. Ces routes sont chargées
| par le RouteServiceProvider et toutes seront assignées au groupe de middleware "web".
|
*/

// Page d'accueil publique - redirection vers login
Route::get('/', function() {
    return redirect('login');
});

// Routes d'authentification
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);

// Route de déconnexion
// Remplacez votre route de déconnexion actuelle par celle-ci
//Route::post('logout', [App\Http\Controllers\Auth\CustomLogoutController::class, 'logout'])->name('logout');
Route::post('logout', [App\Http\Controllers\Auth\LogoutDirectController::class, 'logout'])->name('logout');

/* Route::post('logout', function(Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('/login')->withHeaders([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'Location' => url('/login')
        ]);
})->name('logout'); */

// Routes d'enregistrement
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

// Routes de réinitialisation de mot de passe
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Password setup routes (public - no auth required) - CORRECTED FOR TOKEN HANDLING
Route::prefix('set-password')->name('set-password.')->group(function () {
    // Route principale avec token obligatoire
    Route::get('/{token}', [SetPasswordController::class, 'showSetPasswordForm'])
        ->name('form')
        ->where('token', '[a-zA-Z0-9]+');
    
    // Route de soumission avec token obligatoire
    Route::post('/{token}', [SetPasswordController::class, 'setPassword'])
        ->name('submit')
        ->where('token', '[a-zA-Z0-9]+');
    
    // Route alternative sans token (pour compatibilité)
    Route::get('/', [SetPasswordController::class, 'showSetPasswordFormNoToken'])
        ->name('form.notoken');
    
    // Route de validation de token (AJAX)
    Route::post('/validate-token', [SetPasswordController::class, 'validateToken'])
        ->name('validate');
    
    // Route de succès
    Route::get('/success', [SetPasswordController::class, 'success'])
        ->name('success');
});

// Legacy password setup routes (for backward compatibility)
Route::get('/set-password', [SetPasswordController::class, 'showSetPasswordForm'])->name('password.set.form');
Route::post('/set-password', [SetPasswordController::class, 'setPassword'])->name('password.set');
Route::get('/check-token', [SetPasswordController::class, 'checkTokenStatus'])->name('password.check.token');

// Route pour Tenants
Route::get('/tenant-setup/{domain}', [TenantController::class, 'setup'])->name('tenant.setup');
// added recently by Mohammad OLWAN 13/06/2025
// Routes Hotel Manager
Route::middleware(['hotel.manager.auth'])->prefix('hotel')->name('hotel.')->group(function () {
    // Dans votre groupe de routes Hotel Manager
Route::match(['get', 'post'], '/dashboard', function() {
    // Si c'est une requête POST, redirigez vers la page de connexion
    if (request()->isMethod('post')) {
        return redirect('/login');
    }
    
    // Sinon, affichez le tableau de bord normalement
    return view('hotel.dashboard');
})->name('dashboard');

    Route::get('/dashboard', function() {
        return view('hotel.dashboard');
    })->name('dashboard');
    
    // Autres routes hotel manager...
    Route::prefix('branches')->name('branches.')->group(function () {
        Route::get('/', function() {
            return view('hotel_branches.index');
        })->name('index');
        
        Route::get('/create', function() {
            return view('hotel_branches.create');
        })->name('create');
        
        Route::get('/{id}', function($id) {
            return view('hotel_branches.show', compact('id'));
        })->name('show');
        
            Route::get('/{id}/edit', function($id) {
                return view('hotel.branches.edit', compact('id'));
            })->name('edit');
        });
});

// Routes Branch Manager
Route::middleware(['branch.manager.auth'])->prefix('branch')->name('branch.')->group(function () {
    Route::get('/dashboard', function() {
        return view('branch.dashboard');
    })->name('dashboard');
    
    // Autres routes branch manager...
});
// fin added recently by Mohammad OLWAN 13/06/2025
// Routes protégées par authentification
Route::middleware(['auth'])->group(function () {
    
    // Dashboard principal - redirection selon le rôle
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    
    // Dashboard pour chaque type d'utilisateur
    Route::get('/admin/dashboard', function() {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    
   /*  Route::get('/hotel/dashboard', function() {
        return view('hotel_manager.dashboard');
    })->name('hotel.dashboard'); */
});

// Routes protégées par authentification BRANCH MANAGER
/* Route::middleware(['branch_manager.auth'])->group(function () {
    Route::get('/branch/dashboard', function() {
        return view('branch_manager.dashboard'); 
    })->name('branch.dashboard');
}); */

// Routes protégées par authentification CLIENT
Route::middleware(['customer.auth'])->group(function () {
    Route::get('/customer/dashboard', function() {
        return view('customer.dashboard');
    })->name('customer.dashboard');
    
    // Routes du profil client
    Route::get('/customer/profile', [CustomerProfileController::class, 'show'])->name('customer.profile');
    Route::put('/customer/profile', [CustomerProfileController::class, 'update'])->name('customer.profile.update');
    
    // Routes des paiements et historique
    Route::get('/customer/payments', [CustomerProfileController::class, 'payments'])->name('customer.payments');
    Route::get('/customer/history', [CustomerProfileController::class, 'history'])->name('customer.history');
    
    // Route des paramètres client
    Route::get('/customer/settings', function() {
        return view('customer.settings');
    })->name('customer.settings');
});

// Routes d'administration
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    
    // Customer Import Routes - Complete Dynamic System with FIXED PREVIEW
    Route::prefix('customer-import')->name('customer-import.')->group(function () {
        
        // Main import pages
        Route::get('/', [CustomerImportController::class, 'index'])->name('index');
        Route::get('/create', [CustomerImportController::class, 'create'])->name('create');
        
        // Dynamic branches retrieval from tenant databases
        Route::get('/branches', [CustomerImportController::class, 'getBranches'])->name('branches');
        
        // File upload and processing
        Route::post('/upload', [CustomerImportController::class, 'upload'])->name('upload');
        
        // Preview functionality - FIXED ROUTE (POST for file upload)
        Route::post('/preview', [CustomerImportController::class, 'preview'])->name('preview');
        
        // Import management
        Route::get('/{id}/preview', [CustomerImportController::class, 'show'])->name('preview.show');
        Route::post('/{id}/confirm', [CustomerImportController::class, 'confirm'])->name('confirm');
        Route::get('/{id}', [CustomerImportController::class, 'show'])->name('show');
        Route::get('/{id}/status', [CustomerImportController::class, 'status'])->name('status');
        Route::delete('/{id}', [CustomerImportController::class, 'destroy'])->name('destroy');
        
        // Import monitoring and statistics
        Route::get('/stats', [CustomerImportController::class, 'getImportStats'])->name('stats');
        
        // Cache management
        Route::post('/clear-cache/{tenant_id?}', [CustomerImportController::class, 'clearBranchesCache'])->name('clear-cache');
        
    });

    // ===== NOUVELLES ROUTES POUR L'INTERFACE ADMIN =====
    
    // Gestion des Clients
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', function() { return view('admin.customers.index'); })->name('index');
        Route::get('/create', function() { return view('admin.customers.create'); })->name('create');
        Route::post('/', function() { return redirect()->route('admin.customers.index'); })->name('store');
        Route::get('/{id}', function($id) { return view('admin.customers.show', compact('id')); })->name('show');
        Route::get('/{id}/edit', function($id) { return view('admin.customers.edit', compact('id')); })->name('edit');
        Route::put('/{id}', function($id) { return redirect()->route('admin.customers.index'); })->name('update');
        Route::delete('/{id}', function($id) { return redirect()->route('admin.customers.index'); })->name('destroy');
    });

    // Gestion des Hôtels
    Route::prefix('hotels')->name('hotels.')->group(function () {
        Route::get('/', function() { return view('admin.hotels.index'); })->name('index');
        Route::get('/create', function() { return view('admin.hotels.create'); })->name('create');
        Route::post('/', function() { return redirect()->route('admin.hotels.index'); })->name('store');
        Route::get('/{id}', function($id) { return view('admin.hotels.show', compact('id')); })->name('show');
        Route::get('/{id}/edit', function($id) { return view('admin.hotels.edit', compact('id')); })->name('edit');
        Route::put('/{id}', function($id) { return redirect()->route('admin.hotels.index'); })->name('update');
        Route::delete('/{id}', function($id) { return redirect()->route('admin.hotels.index'); })->name('destroy');
        Route::get('/settings', function() { return view('admin.hotels.settings'); })->name('settings');
    });

    // Gestion des Locataires
    Route::prefix('tenants')->name('tenants.')->group(function () {
        Route::get('/', function() { return view('admin.tenants.index'); })->name('index');
        Route::get('/create', function() { return view('admin.tenants.create'); })->name('create');
        Route::post('/', function() { return redirect()->route('admin.tenants.index'); })->name('store');
        Route::get('/{id}', function($id) { return view('admin.tenants.show', compact('id')); })->name('show');
        Route::get('/{id}/edit', function($id) { return view('admin.tenants.edit', compact('id')); })->name('edit');
        Route::put('/{id}', function($id) { return redirect()->route('admin.tenants.index'); })->name('update');
        Route::delete('/{id}', function($id) { return redirect()->route('admin.tenants.index'); })->name('destroy');
    });

    // Rapports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/statistics', function() { return view('admin.reports.statistics'); })->name('statistics');
        Route::get('/export', function() { return view('admin.reports.export'); })->name('export');
    });

    // Journaux d'Activité
    Route::get('/logs', function() { return view('admin.logs.index'); })->name('logs');

    // Paramètres
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/general', function() { return view('admin.settings.general'); })->name('general');
        Route::get('/email', function() { return view('admin.settings.email'); })->name('email');
        Route::get('/security', function() { return view('admin.settings.security'); })->name('security');
        Route::post('/general', function() { return redirect()->route('admin.settings.general')->with('success', 'Paramètres mis à jour'); })->name('general.update');
        Route::post('/email', function() { return redirect()->route('admin.settings.email')->with('success', 'Configuration email mise à jour'); })->name('email.update');
        Route::post('/security', function() { return redirect()->route('admin.settings.security')->with('success', 'Paramètres de sécurité mis à jour'); })->name('security.update');
    });

    // Gestion des Utilisateurs
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', function() { return view('admin.users.index'); })->name('index');
        Route::get('/create', function() { return view('admin.users.create'); })->name('create');
        Route::post('/', function() { return redirect()->route('admin.users.index'); })->name('store');
        Route::get('/{id}', function($id) { return view('admin.users.show', compact('id')); })->name('show');
        Route::get('/{id}/edit', function($id) { return view('admin.users.edit', compact('id')); })->name('edit');
        Route::put('/{id}', function($id) { return redirect()->route('admin.users.index'); })->name('update');
        Route::delete('/{id}', function($id) { return redirect()->route('admin.users.index'); })->name('destroy');
    });

    // Sauvegardes
    Route::get('/backups', function() { return view('admin.backups.index'); })->name('backups');

    // Profil Administrateur
    Route::get('/profile', function() { return view('admin.profile.index'); })->name('profile');
    Route::put('/profile', function() { return redirect()->route('admin.profile')->with('success', 'Profil mis à jour'); })->name('profile.update');
    
});

// Routes de paiement
Route::get('/tokenization-callback', function () {
    return view('tokenization.callback');
})->name('tokenization.callback');

Route::get('/payment-success', function () {
    return view('payment.success');
})->name('payment.success');

Route::get('/payment-failed', function () {
    return view('payment.failed');
})->name('payment.failed');

// Testing and Development Routes - ENHANCED WITH NEW FEATURES
Route::prefix('test')->name('test.')->group(function () {
    
    // Test tenant database connection
    Route::get('/tenant-connection/{tenant_id}', function ($tenantId) {
        try {
            $tenant = \App\Models\Tenant::find($tenantId);
            
            if (!$tenant) {
                return response()->json(['error' => 'Tenant not found']);
            }
            
            $databaseName = $tenant->database_name ?: 'collect_hotel_' . strtolower(str_replace(' ', '_', $tenant->name));
            
            config(['database.connections.test_tenant' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => $databaseName,
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]]);
            
            $connection = DB::connection('test_tenant');
            $tables = $connection->select('SHOW TABLES');
            
            return response()->json([
                'tenant' => $tenant->name,
                'database' => $databaseName,
                'tables_count' => count($tables),
                'tables' => $tables
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    })->name('tenant.connection');
    
    // Show all databases
    Route::get('/show-databases', function () {
        try {
            $databases = DB::select('SHOW DATABASES');
            $collectDatabases = array_filter($databases, function($db) {
                $dbName = array_values((array)$db)[0];
                return strpos($dbName, 'collect_') === 0;
            });
            
            return response()->json([
                'all_databases' => $databases,
                'collect_databases' => array_values($collectDatabases)
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    })->name('show.databases');
    
    // Test customer count for a tenant - ENHANCED
    Route::get('/customer-count/{tenant_id}', function ($tenantId) {
        try {
            $tenant = \App\Models\Tenant::find($tenantId);
            if (!$tenant) {
                return response()->json(['error' => 'Tenant not found'], 404);
            }
            
            $databaseName = $tenant->database_name ?: 'collect_hotel_' . strtolower(str_replace(['hôtel', 'hotel', ' '], ['', '', '_'], preg_replace('/[^a-z0-9_ ]/', '', strtolower($tenant->name))));
            
            // Create temporary connection
            config(['database.connections.temp_tenant' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => $databaseName,
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]]);
            
            $connection = \DB::connection('temp_tenant');
            
            if (!$connection->getSchemaBuilder()->hasTable('customers')) {
                return response()->json([
                    'tenant' => $tenant->name,
                    'database' => $databaseName,
                    'customers_table_exists' => false,
                    'customer_count' => 0,
                    'pending_password_count' => 0,
                    'active_count' => 0
                ]);
            }
            
            $customerCount = $connection->table('customers')->count();
            $pendingPasswordCount = $connection->table('customers')->where('status', 'pending_password')->count();
            $activeCount = $connection->table('customers')->where('status', 'active')->count();
            
            $recentCustomers = $connection->table('customers')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            return response()->json([
                'tenant' => $tenant->name,
                'database' => $databaseName,
                'customers_table_exists' => true,
                'customer_count' => $customerCount,
                'pending_password_count' => $pendingPasswordCount,
                'active_count' => $activeCount,
                'recent_customers' => $recentCustomers
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('customer.count');
    
    // Test database structure for a tenant - NEW
    Route::get('/database-structure/{tenant_id}', function ($tenantId) {
        try {
            $tenant = \App\Models\Tenant::find($tenantId);
            if (!$tenant) {
                return response()->json(['error' => 'Tenant not found'], 404);
            }
            
            $databaseName = 'collect_hotel_' . strtolower(str_replace(['hôtel', 'hotel', ' '], ['', '', '_'], preg_replace('/[^a-z0-9_ ]/', '', strtolower($tenant->name))));
            
            config(['database.connections.tenant_test' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => $databaseName,
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]]);
            
            $connection = \DB::connection('tenant_test');
            
            if (!$connection->getSchemaBuilder()->hasTable('customers')) {
                return response()->json([
                    'tenant_name' => $tenant->name,
                    'database' => $databaseName,
                    'table_exists' => false,
                    'message' => 'Customers table does not exist'
                ]);
            }
            
            $columns = $connection->select("DESCRIBE customers");
            
            return response()->json([
                'tenant_name' => $tenant->name,
                'database' => $databaseName,
                'table_exists' => true,
                'columns' => $columns
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'tenant_id' => $tenantId
            ], 500);
        }
    })->name('database.structure');
    
    // Test email configuration - ENHANCED
Qwsé:    Route::get('/test-email', function () {
        try {
            $testEmail = 'test@example.com';
            
            \Mail::raw('Test email from CollectToPay system - ' . now()->toDateTimeString(), function ($message) use ($testEmail) {
                $message->to($testEmail)
                        ->subject('Test Email Configuration - CollectToPay');
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully to ' . $testEmail,
                'timestamp' => now()->toDateTimeString(),
                'mail_config' => [
                    'mailer' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'username' => config('mail.mailers.smtp.username'),
                    'from' => config('mail.from.address'),
                    'from_name' => config('mail.from.name')
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'mail_config' => [
                    'mailer' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'from' => config('mail.from.address')
                ]
            ], 500);
        }
    })->name('test.email');
    
    // Test password reset token generation - ENHANCED
    Route::get('/test-token/{token}', function ($token) {
        try {
            return response()->json([
                'token' => $token,
                'url_generated' => route('set-password.form', ['token' => $token]),
                'test_url' => url('/set-password/' . $token),
                'expires_at' => now()->addHours(24)->toDateTimeString(),
                'created_at' => now()->toDateTimeString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    })->name('test.token');
    
    // General debug route - ENHANCED
    Route::get('/debug', function () {
        $output = "<h2>CollectToPay System Debug</h2>";
        $output .= "Test de Laravel : OK<br>";
        
        try {
            DB::connection()->getPdo();
            $output .= "Connexion DB : OK<br>";
        } catch (\Exception $e) {
            $output .= "Connexion DB : ERREUR - " . $e->getMessage() . "<br>";
        }
        
        try {
            $tenants = \App\Models\Tenant::count();
            $output .= "Nombre de tenants : " . $tenants . "<br>";
        } catch (\Exception $e) {
            $output .= "Erreur tenants : " . $e->getMessage() . "<br>";
        }
        
        $output .= "Environnement : " . app()->environment() . "<br>";
        $output .= "URL de base : " . url('/') . "<br>";
        $output .= "Timestamp : " . now()->toDateTimeString() . "<br>";
        
        return $output;
    })->name('debug');
    
});