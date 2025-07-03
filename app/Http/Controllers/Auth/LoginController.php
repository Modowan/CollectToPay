<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Models\Tenant;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        Log::info('=== DÉBUT LOGIN DEBUG ===');
        
        $email = $request->input('email');
        $password = $request->input('password');
        
        // Lire les champs selon le format de votre formulaire
        $accountType = $request->input('role', $request->input('account_type', 'admin'));
        $hotelName = $request->input('domain', $request->input('hotel_name', ''));

        Log::info('Données reçues', [
            'email' => $email,
            'account_type_extracted' => $accountType,
            'hotel_name_extracted' => $hotelName,
            'has_password' => !empty($password),
            'role_field' => $request->input('role'),
            'domain_field' => $request->input('domain')
        ]);

        // Validation des champs requis
        try {
            Log::info('Début validation des champs');
            Log::info('Tous les champs reçus', $request->all());
            
            // Validation plus permissive pour debug
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);
            
            // Vérification manuelle de account_type
            if (empty($accountType)) {
                Log::error('account_type vide après extraction', [
                    'account_type_raw' => $request->input('account_type'),
                    'all_inputs' => $request->all()
                ]);
                return back()->withErrors(['account_type' => 'Le type de compte est requis.']);
            }
            
            // Vérifier que account_type est valide
            $validTypes = ['admin', 'hotel_manager', 'branch_manager', 'customer'];
            if (!in_array($accountType, $validTypes)) {
                Log::error('account_type invalide', [
                    'account_type' => $accountType,
                    'valid_types' => $validTypes
                ]);
                return back()->withErrors(['account_type' => 'Type de compte invalide.']);
            }
            
            Log::info('Validation réussie', ['account_type' => $accountType]);
        } catch (\Exception $e) {
            Log::error('Erreur de validation', [
                'error' => $e->getMessage(),
                'all_inputs' => $request->all()
            ]);
            return back()->withErrors(['validation' => 'Erreur de validation: ' . $e->getMessage()]);
        }

        try {
            Log::info('Début switch account_type', ['account_type' => $accountType]);
            
            switch ($accountType) {
                case 'admin':
                    Log::info('=== APPEL handleAdminLogin ===');
                    return $this->handleAdminLogin($email, $password, $request);
                
                case 'hotel_manager':
                    Log::info('=== APPEL handleHotelManagerLogin ===');
                    return $this->handleHotelManagerLogin($email, $password, $request);
                
                case 'branch_manager':
                    Log::info('=== APPEL handleBranchManagerLogin ===');
                    return $this->handleBranchManagerLogin($email, $password, $hotelName, $request);
                
                case 'customer':
                    Log::info('=== APPEL handleCustomerLogin ===');
                    return $this->handleCustomerLogin($email, $password, $hotelName, $request);
                
                default:
                    Log::error('Type de compte invalide', ['account_type' => $accountType]);
                    return back()->withErrors(['account_type' => 'Type de compte invalide.']);
            }
        } catch (\Exception $e) {
            Log::error('Erreur dans switch/méthodes de connexion', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $email,
                'account_type' => $accountType
            ]);
            
            return back()->withErrors(['email' => 'Erreur système: ' . $e->getMessage()]);
        }
    }

    /**
     * Gestion de la connexion Admin (base centrale)
     */
    private function handleAdminLogin($email, $password, $request)
    {
        Log::info('=== DÉBUT handleAdminLogin ===', ['email' => $email]);
        
        try {
            // Vérifier que la base centrale est accessible
            Log::info('Test connexion base centrale');
            $userCount = DB::table('users')->where('role', 'admin')->count();
            Log::info('Nombre d\'admins trouvés', ['count' => $userCount]);
            
            // Chercher l'utilisateur admin
            Log::info('Recherche utilisateur admin', ['email' => $email]);
            $user = DB::table('users')
                ->where('email', $email)
                ->where('role', 'admin')
                ->first();
            
            if (!$user) {
                Log::warning('Utilisateur admin non trouvé', ['email' => $email]);
                return back()->withErrors(['email' => 'Compte admin non trouvé.']);
            }
            
            Log::info('Utilisateur admin trouvé', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email
            ]);
            
            // Vérifier le mot de passe
            Log::info('Vérification du mot de passe');
            if (!Hash::check($password, $user->password)) {
                Log::warning('Mot de passe incorrect pour admin', ['email' => $email]);
                return back()->withErrors(['password' => 'Mot de passe admin incorrect.']);
            }
            
            Log::info('Mot de passe correct, tentative Auth::attempt');
            
            // Authentification Laravel standard
            if (Auth::attempt(['email' => $email, 'password' => $password, 'role' => 'admin'])) {
                $authUser = Auth::user();
                Log::info('Auth::attempt réussi', [
                    'auth_user_id' => $authUser->id,
                    'auth_user_email' => $authUser->email
                ]);
                
                $request->session()->regenerate();
                Log::info('Session régénérée, redirection FORCÉE vers /admin/dashboard');
                
                return redirect('/admin/dashboard');
            } else {
                Log::error('Auth::attempt a échoué malgré la vérification manuelle');
                return back()->withErrors(['email' => 'Échec de l\'authentification Laravel.']);
            }
            
        } catch (\Exception $e) {
            Log::error('Exception dans handleAdminLogin', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'email' => $email
            ]);
            
            return back()->withErrors(['email' => 'Erreur admin: ' . $e->getMessage()]);
        }
    }

    /**
     * Gestion de la connexion Hotel Manager (base centrale)
     */
    private function handleHotelManagerLogin($email, $password, $request)
    {
        Log::info('=== DÉBUT handleHotelManagerLogin ===', ['email' => $email]);
        
        // Authentification standard Laravel pour hotel manager
        if (Auth::attempt(['email' => $email, 'password' => $password, 'role' => 'hotel_manager'])) {
            $user = Auth::user();
            Log::info('Connexion hotel manager réussie', ['user_id' => $user->id, 'email' => $user->email]);
            
            $request->session()->regenerate();
            return redirect('/hotel/dashboard');
        }
        
        Log::warning('Échec connexion hotel manager', ['email' => $email]);
        return back()->withErrors(['email' => 'Identifiants hotel manager incorrects.']);
    }

    /**
     * Gestion de la connexion Branch Manager (base tenant)
     */
    private function handleBranchManagerLogin($email, $password, $hotelName, $request)
    {
        Log::info('=== DÉBUT handleBranchManagerLogin ===', ['email' => $email, 'hotel' => $hotelName]);
        
        if (empty($hotelName)) {
            return back()->withErrors(['hotel_name' => 'Le nom de l\'hôtel est requis pour les branch managers.']);
        }

        // Trouver le tenant
        $tenant = $this->findTenantByHotelName($hotelName);
        if (!$tenant) {
            Log::warning('Tenant non trouvé pour branch manager', ['hotel' => $hotelName]);
            return back()->withErrors(['hotel_name' => 'Hôtel non trouvé.']);
        }

        // Créer la connexion tenant
        $this->createTenantConnection('tenant', $tenant->database_name);
        
        // Chercher dans la table tenant_users
        $user = DB::connection('tenant')->table('tenant_users')
            ->where('email', $email)
            ->where('is_active', 1)
            ->first();

        if (!$user) {
            Log::warning('Branch manager non trouvé', ['email' => $email]);
            return back()->withErrors(['email' => 'Branch manager non trouvé.']);
        }

        // Vérifier le mot de passe
        if (!Hash::check($password, $user->password)) {
            Log::warning('Mot de passe incorrect pour branch manager', ['email' => $email]);
            return back()->withErrors(['password' => 'Mot de passe incorrect.']);
        }

        // Créer la session branch manager
        $this->createBranchManagerSession($user, $tenant, $request);
        
        Log::info('Connexion branch manager réussie', ['email' => $email, 'tenant' => $tenant->name]);
        return redirect('/branch/dashboard');
    }

    /**
     * Gestion de la connexion Client (base tenant)
     */
    private function handleCustomerLogin($email, $password, $hotelName, $request)
    {
        Log::info('=== DÉBUT handleCustomerLogin ===', ['email' => $email, 'hotel' => $hotelName]);
        
        if (empty($hotelName)) {
            return back()->withErrors(['hotel_name' => 'Le nom de l\'hôtel est requis pour les clients.']);
        }

        // Trouver le tenant
        $tenant = $this->findTenantByHotelName($hotelName);
        if (!$tenant) {
            Log::warning('Tenant non trouvé pour client', ['hotel' => $hotelName]);
            return back()->withErrors(['hotel_name' => 'Hôtel non trouvé.']);
        }

        // Créer la connexion tenant
        $this->createTenantConnection('tenant', $tenant->database_name);
        
        // Chercher dans la table customers
        $customer = DB::connection('tenant')->table('customers')
            ->where('email', $email)
            ->first();

        if (!$customer) {
            Log::warning('Client non trouvé', ['email' => $email]);
            return back()->withErrors(['email' => 'Client non trouvé.']);
        }

        // Vérifier le statut du client
        if ($customer->status === 'pending_password') {
            Log::info('Client avec mot de passe en attente', ['email' => $email]);
            return back()->withErrors(['password' => 'Veuillez d\'abord définir votre mot de passe via le lien reçu par email.']);
        }

        // Vérifier le mot de passe
        if (!$customer->password || !Hash::check($password, $customer->password)) {
            Log::warning('Mot de passe incorrect pour client', ['email' => $email]);
            return back()->withErrors(['password' => 'Mot de passe incorrect.']);
        }

        // Créer la session client
        $this->createCustomerSession($customer, $tenant, $request);
        
        Log::info('Connexion client réussie', ['email' => $email, 'tenant' => $tenant->name]);
        return redirect('/customer/dashboard');
    }

    /**
     * Créer une session pour un client
     */
    private function createCustomerSession($customer, $tenant, $request)
    {
        $customerData = [
            'id' => $customer->id,
            'email' => $customer->email,
            'name' => trim(($customer->first_name ?? '') . ' ' . ($customer->last_name ?? '')),
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
            'branch_id' => $customer->branch_id
        ];

        $request->session()->put('customer_auth', $customerData);
        $request->session()->put('auth.customer_authenticated', true);
        $request->session()->put('auth.password_confirmed_at', time());
        
        Log::info('Session client créée', ['customer_id' => $customer->id, 'email' => $customer->email]);
    }

    /**
     * Créer une session pour un branch manager
     */
    private function createBranchManagerSession($user, $tenant, $request)
    {
        $userData = [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'role' => $user->role,
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
            'branch_id' => $user->branch_id
        ];

        $request->session()->put('branch_manager_auth', $userData);
        $request->session()->put('auth.branch_manager_authenticated', true);
        $request->session()->put('auth.password_confirmed_at', time());
        
        Log::info('Session branch manager créée', ['user_id' => $user->id, 'email' => $user->email]);
    }

    /**
     * Rechercher un tenant par nom d'hôtel
     */
    private function findTenantByHotelName($hotelName)
    {
        Log::info('Recherche tenant pour: ' . $hotelName);
        
        // 1. Recherche par domaine exact
        $tenant = Tenant::where('domain', $hotelName)->first();
        if ($tenant) {
            Log::info('Tenant trouvé par domaine: ' . $tenant->name);
            return $tenant;
        }
        
        // 2. Recherche par database_name exact
        $tenant = Tenant::where('database_name', $hotelName)->first();
        if ($tenant) {
            Log::info('Tenant trouvé par database_name: ' . $tenant->name);
            return $tenant;
        }
        
        // 3. Recherche par nom similaire (sans utiliser slug)
        $cleanName = $this->sanitizeHotelName($hotelName);
        $tenant = Tenant::where('name', 'like', '%' . $cleanName . '%')
                       ->orWhere('domain', 'like', '%' . $cleanName . '%')
                       ->first();
        
        if ($tenant) {
            Log::info('Tenant trouvé par similarité: ' . $tenant->name);
            return $tenant;
        }
        
        Log::warning('Aucun tenant trouvé pour: ' . $hotelName);
        return null;
    }

    /**
     * Nettoyer le nom d'hôtel pour la recherche
     */
    private function sanitizeHotelName($hotelName)
    {
        return strtolower(preg_replace('/[^a-z0-9]/', '', strtolower($hotelName)));
    }

    /**
     * Créer une connexion tenant complète
     */
    private function createTenantConnection($connectionName, $databaseName)
    {
        $config = [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $databaseName,
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ];

        config(['database.connections.' . $connectionName => $config]);
        DB::purge($connectionName);
        DB::reconnect($connectionName);
        
        Log::info('Connexion tenant créée: ' . $connectionName . ' pour base: ' . $databaseName);
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        Log::info('=== DÉBUT LOGOUT ===');
        
        // Déconnecter l'authentification standard
        Auth::logout();
        
        // Nettoyer toutes les sessions personnalisées
        $request->session()->forget(['customer_auth', 'branch_manager_auth']);
        $request->session()->forget(['auth.customer_authenticated', 'auth.branch_manager_authenticated']);
        $request->session()->forget('auth.password_confirmed_at');
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        Log::info('Déconnexion effectuée');
        return redirect('/login');
    }
}

