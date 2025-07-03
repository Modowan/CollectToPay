<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SetPasswordController extends Controller
{
    /**
     * Generate correct database name from tenant name
     */
    private function getDatabaseName($tenantName)
    {
        // First remove "Hôtel " and "Hotel " prefixes completely
        $cleanName = str_replace(['Hôtel ', 'Hotel ', 'hôtel ', 'hotel '], '', $tenantName);
        
        // Then replace spaces and accents
        return 'collect_hotel_' . strtolower(str_replace([
            ' ',
            'é', 'è', 'ê', 'ë', 'à', 'â', 'ä', 'ù', 'û', 'ü', 'î', 'ï', 'ô', 'ö', 'ç'
        ], [
            '_',
            'e', 'e', 'e', 'e', 'a', 'a', 'a', 'u', 'u', 'u', 'i', 'i', 'o', 'o', 'c'
        ], $cleanName));
    }

    /**
     * Show the set password form with token in URL path
     * MATCHES YOUR ROUTE: Route::get('/{token}', [SetPasswordController::class, 'showForm'])
     */
    public function showForm($token)
    {
        Log::info('showForm called with token: ' . substr($token, 0, 10) . '...');
        return $this->processTokenAndShowForm($token);
    }

    /**
     * Show the set password form with token in query parameter
     * MATCHES YOUR ROUTE: Route::get('/set-password', [SetPasswordController::class, 'showSetPasswordForm'])
     */
    public function showSetPasswordForm(Request $request)
    {
        $token = $request->get('token');
        Log::info('showSetPasswordForm called with token: ' . ($token ? substr($token, 0, 10) . '...' : 'NULL'));
        return $this->processTokenAndShowForm($token);
    }

    /**
     * Common method to process token and show form
     * FIXED: Multi-tenant token search with correct database naming
     */
    private function processTokenAndShowForm($token)
    {
        try {
            Log::info('=== PROCESSING TOKEN ===', ['token' => $token ? substr($token, 0, 10) . '...' : 'NULL']);

            // Check if token is provided
            if (!$token) {
                Log::warning('Set password form accessed without token');
                return view('auth.set-password-error', [
                    'error' => 'Lien invalide. Le token de sécurité est manquant.',
                    'token' => null
                ]);
            }

            Log::info('Searching for token in all tenant databases', ['token' => substr($token, 0, 10) . '...']);

            // Get all tenants
            $tenants = Tenant::all();
            Log::info('Found tenants: ' . $tenants->count());

            $resetRecord = null;
            $foundTenant = null;

            foreach ($tenants as $tenant) {
                try {
                    Log::info('Checking tenant: ' . $tenant->name);

                    // FIXED: Use correct database naming algorithm
                    $databaseName = $this->getDatabaseName($tenant->name);
                    Log::info('Database name: ' . $databaseName);
                    
                    // Test direct MySQL connection first
                    $host = env('DB_HOST', '127.0.0.1');
                    $port = env('DB_PORT', '3306');
                    $username = env('DB_USERNAME', 'root');
                    $password = env('DB_PASSWORD', 'password');
                    
                    Log::info('Connection params', [
                        'host' => $host,
                        'port' => $port,
                        'username' => $username,
                        'database' => $databaseName
                    ]);

                    // Create direct PDO connection
                    $dsn = "mysql:host={$host};port={$port};dbname={$databaseName};charset=utf8mb4";
                    $pdo = new \PDO($dsn, $username, $password, [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
                    ]);

                    Log::info('PDO connection successful for: ' . $databaseName);

                    // Check if table exists
                    $stmt = $pdo->prepare("SHOW TABLES LIKE 'password_reset_tokens'");
                    $stmt->execute();
                    $tableExists = $stmt->fetch();

                    if (!$tableExists) {
                        Log::warning('Table password_reset_tokens does not exist in: ' . $databaseName);
                        continue;
                    }

                    Log::info('Table password_reset_tokens exists in: ' . $databaseName);

                    // Search for token (exact match first)
                    $stmt = $pdo->prepare("
                        SELECT * FROM password_reset_tokens 
                        WHERE token = ? 
                        AND expires_at > NOW() 
                        AND used_at IS NULL
                    ");
                    $stmt->execute([$token]);
                    $resetRecord = $stmt->fetch();

                    if ($resetRecord) {
                        $foundTenant = $tenant;
                        Log::info('Token found in tenant database', [
                            'tenant' => $tenant->name,
                            'database' => $databaseName,
                            'email' => $resetRecord->email
                        ]);
                        break;
                    } else {
                        Log::info('Token not found in: ' . $databaseName);
                        
                        // Try hashed token as fallback
                        $hashedToken = hash('sha256', $token);
                        $stmt = $pdo->prepare("
                            SELECT * FROM password_reset_tokens 
                            WHERE token = ? 
                            AND expires_at > NOW() 
                            AND used_at IS NULL
                        ");
                        $stmt->execute([$hashedToken]);
                        $resetRecord = $stmt->fetch();

                        if ($resetRecord) {
                            $foundTenant = $tenant;
                            Log::info('Hashed token found in tenant database', [
                                'tenant' => $tenant->name,
                                'database' => $databaseName,
                                'email' => $resetRecord->email
                            ]);
                            break;
                        }
                    }

                } catch (\Exception $e) {
                    Log::warning('Error searching in tenant database', [
                        'tenant' => $tenant->name,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            // If token not found in any tenant database
            if (!$resetRecord) {
                Log::warning('Token not found in any tenant database', ['token' => substr($token, 0, 10) . '...']);
                return view('auth.set-password-error', [
                    'error' => 'Ce lien de création de mot de passe est invalide ou a expiré.',
                    'token' => substr($token, 0, 10) . '...'
                ]);
            }

            // Check if token is expired
            if (Carbon::parse($resetRecord->expires_at)->isPast()) {
                Log::warning('Expired token accessed', [
                    'token' => substr($token, 0, 10) . '...',
                    'expires_at' => $resetRecord->expires_at
                ]);
                return view('auth.set-password-expired', [
                    'email' => $resetRecord->email,
                    'created_at' => $resetRecord->created_at,
                    'expires_at' => $resetRecord->expires_at
                ]);
            }

            Log::info('Set password form displayed successfully', [
                'token' => substr($token, 0, 10) . '...',
                'email' => $resetRecord->email,
                'tenant' => $foundTenant->name
            ]);

            return view('auth.set-password', [
                'token' => $token,
                'email' => $resetRecord->email,
                'tenant' => $foundTenant
            ]);

        } catch (\Exception $e) {
            Log::error('Error in processTokenAndShowForm', [
                'token' => $token ? substr($token, 0, 10) . '...' : null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return view('auth.set-password-error', [
                'error' => 'Une erreur technique est survenue. Veuillez réessayer plus tard.',
                'token' => $token ? substr($token, 0, 10) . '...' : null
            ]);
        }
    }

    /**
     * Process password setting
     * MATCHES YOUR ROUTES: Both /{token} and /set-password POST routes
     */
    public function setPassword(Request $request, $token = null)
    {
        try {
            // Get token from URL parameter or request
            if (!$token) {
                $token = $request->get('token');
            }

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token de sécurité manquant.'
                ], 400);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                ],
                'password_confirmation' => 'required|same:password',
            ], [
                'password.required' => 'Le mot de passe est obligatoire.',
                'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
                'password.regex' => 'Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.',
                'password_confirmation.required' => 'La confirmation du mot de passe est obligatoire.',
                'password_confirmation.same' => 'Les mots de passe ne correspondent pas.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            Log::info('Processing password setup', ['token' => substr($token, 0, 10) . '...']);

            // Search for token in all tenant databases using direct PDO
            $tenants = Tenant::all();
            $resetRecord = null;
            $foundTenant = null;
            $pdo = null;

            foreach ($tenants as $tenant) {
                try {
                    $databaseName = $this->getDatabaseName($tenant->name);
                    
                    $host = env('DB_HOST', '127.0.0.1');
                    $port = env('DB_PORT', '3306');
                    $username = env('DB_USERNAME', 'root');
                    $password = env('DB_PASSWORD', 'password');
                    
                    $dsn = "mysql:host={$host};port={$port};dbname={$databaseName};charset=utf8mb4";
                    $pdo = new \PDO($dsn, $username, $password, [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
                    ]);

                    // Search for token
                    $stmt = $pdo->prepare("
                        SELECT * FROM password_reset_tokens 
                        WHERE token = ? 
                        AND expires_at > NOW() 
                        AND used_at IS NULL
                    ");
                    $stmt->execute([$token]);
                    $resetRecord = $stmt->fetch();

                    if (!$resetRecord) {
                        $hashedToken = hash('sha256', $token);
                        $stmt = $pdo->prepare("
                            SELECT * FROM password_reset_tokens 
                            WHERE token = ? 
                            AND expires_at > NOW() 
                            AND used_at IS NULL
                        ");
                        $stmt->execute([$hashedToken]);
                        $resetRecord = $stmt->fetch();
                    }

                    if ($resetRecord) {
                        $foundTenant = $tenant;
                        break;
                    }

                } catch (\Exception $e) {
                    Log::warning('Error processing in tenant database', [
                        'tenant' => $tenant->name,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            if (!$resetRecord) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token invalide ou expiré.'
                ], 400);
            }

            // Find customer in the tenant database
            $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
            $stmt->execute([$resetRecord->email]);
            $customer = $stmt->fetch();

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé.'
                ], 404);
            }

            // Update customer password and status
            $stmt = $pdo->prepare("
                UPDATE customers 
                SET password = ?, status = 'active', updated_at = NOW() 
                WHERE email = ?
            ");
            $stmt->execute([Hash::make($request->password), $resetRecord->email]);

            // Mark token as used
            $stmt = $pdo->prepare("
                UPDATE password_reset_tokens 
                SET used_at = NOW() 
                WHERE email = ?
            ");
            $stmt->execute([$resetRecord->email]);

            Log::info('Password set successfully', [
                'email' => $resetRecord->email,
                'tenant' => $foundTenant->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe créé avec succès !',
                'redirect_url' => route('login')
            ]);

        } catch (\Exception $e) {
            Log::error('Error in setPassword', [
                'token' => $token ? substr($token, 0, 10) . '...' : null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Une erreur technique est survenue. Veuillez réessayer.'
            ], 500);
        }
    }

    /**
     * Check token status (for your existing route)
     */
    public function checkTokenStatus(Request $request)
    {
        try {
            $token = $request->get('token');
            
            if (!$token) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Token manquant'
                ]);
            }

            // Search in all tenant databases using direct PDO
            $tenants = Tenant::all();
            foreach ($tenants as $tenant) {
                try {
                    $databaseName = $this->getDatabaseName($tenant->name);
                    
                    $host = env('DB_HOST', '127.0.0.1');
                    $port = env('DB_PORT', '3306');
                    $username = env('DB_USERNAME', 'root');
                    $password = env('DB_PASSWORD', 'password');
                    
                    $dsn = "mysql:host={$host};port={$port};dbname={$databaseName};charset=utf8mb4";
                    $pdo = new \PDO($dsn, $username, $password, [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
                    ]);

                    $stmt = $pdo->prepare("
                        SELECT * FROM password_reset_tokens 
                        WHERE token = ? 
                        AND expires_at > NOW() 
                        AND used_at IS NULL
                    ");
                    $stmt->execute([$token]);
                    $resetRecord = $stmt->fetch();

                    if (!$resetRecord) {
                        $hashedToken = hash('sha256', $token);
                        $stmt = $pdo->prepare("
                            SELECT * FROM password_reset_tokens 
                            WHERE token = ? 
                            AND expires_at > NOW() 
                            AND used_at IS NULL
                        ");
                        $stmt->execute([$hashedToken]);
                        $resetRecord = $stmt->fetch();
                    }

                    if ($resetRecord) {
                        return response()->json([
                            'valid' => true,
                            'email' => $resetRecord->email,
                            'tenant' => $tenant->name
                        ]);
                    }

                } catch (\Exception $e) {
                    continue;
                }
            }

            return response()->json([
                'valid' => false,
                'message' => 'Token invalide ou expiré'
            ]);

        } catch (\Exception $e) {
            Log::error('Error in checkTokenStatus', ['error' => $e->getMessage()]);
            
            return response()->json([
                'valid' => false,
                'message' => 'Erreur de validation'
            ]);
        }
    }
}

