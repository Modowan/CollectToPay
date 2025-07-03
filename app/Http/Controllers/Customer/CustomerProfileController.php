<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class CustomerProfileController extends Controller
{
    /**
     * Afficher le formulaire de profil client
     */
    public function show()
    {
        try {
            // Récupérer les informations du client depuis la session
            $customerAuth = Session::get('customer_auth');
            
            if (!$customerAuth || !isset($customerAuth['id'])) {
                Log::warning('CustomerProfile - Session client non trouvée');
                return redirect()->route('login')->with('error', 'Session expirée. Veuillez vous reconnecter.');
            }

            $customerId = $customerAuth['id'];
            $tenantName = $customerAuth['tenant_name'] ?? 'Hôtel';
            
            // Récupérer les données du client depuis la base de données tenant
            $tenantDbName = $this->getTenantDatabase($tenantName);
            
            if (!$tenantDbName) {
                Log::error('CustomerProfile - Base de données tenant non trouvée', ['tenant' => $tenantName]);
                return redirect()->route('customer.dashboard')->with('error', 'Erreur de configuration.');
            }

            // CORRECTION : Configurer COMPLÈTEMENT la connexion tenant
            config(['database.connections.tenant' => [
                'driver' => env('DB_CONNECTION', 'mysql'),
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => $tenantDbName,
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ]]);
            
            // Purger la connexion pour forcer la reconfiguration
            DB::purge('tenant');
            
            // Récupérer les données du client
            $customer = DB::connection('tenant')
                ->table('customers')
                ->where('id', $customerId)
                ->first();

            if (!$customer) {
                Log::error('CustomerProfile - Client non trouvé', ['customer_id' => $customerId]);
                return redirect()->route('customer.dashboard')->with('error', 'Profil client non trouvé.');
            }

            // Décoder les préférences JSON
            $preferences = [];
            if ($customer->preferences) {
                $preferences = json_decode($customer->preferences, true) ?? [];
            }

            Log::info('CustomerProfile - Affichage du profil', [
                'customer_id' => $customerId,
                'tenant' => $tenantName,
                'tenant_db' => $tenantDbName
            ]);

            return view('customer.profile', compact('customer', 'preferences'));

        } catch (\Exception $e) {
            Log::error('CustomerProfile - Erreur lors de l\'affichage', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('customer.dashboard')->with('error', 'Erreur lors du chargement du profil.');
        }
    }

    /**
     * Mettre à jour le profil client
     */
    public function update(Request $request)
    {
        try {
            // Récupérer les informations du client depuis la session
            $customerAuth = Session::get('customer_auth');
            
            if (!$customerAuth || !isset($customerAuth['id'])) {
                Log::warning('CustomerProfile - Session client non trouvée pour mise à jour');
                return response()->json(['error' => 'Session expirée'], 401);
            }

            $customerId = $customerAuth['id'];
            $tenantName = $customerAuth['tenant_name'] ?? 'Hôtel';

            // Validation des données - OPTIMISÉE POUR LES CHAMPS SÉLECTIONNÉS
            $validator = Validator::make($request->all(), [
                // Champs obligatoires (Priorité 1)
                'first_name' => 'required|string|min:2|max:100',
                'last_name' => 'required|string|min:2|max:100',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:255',
                'address' => 'required|string|min:10|max:255',
                'city' => 'required|string|min:2|max:255',
                'country' => 'required|string|max:255',
                
                // Champs importants (Priorité 2)
                'postal_code' => 'nullable|string|max:20',
                'date_of_birth' => 'nullable|date|before:' . date('Y-m-d', strtotime('-16 years')),
                'gender' => 'nullable|in:male,female,other',
                'nationality' => 'nullable|string|max:100',
                'emergency_contact_name' => 'nullable|string|min:5|max:255',
                'emergency_contact_phone' => 'nullable|string|max:20',
                'emergency_contact_relation' => 'nullable|string|max:100',
                
                // Champs optionnels (Priorité 3)
                'id_number' => 'nullable|string|max:50',
                'special_requests' => 'nullable|string|max:1000',
                'notes' => 'nullable|string|max:500',
                'preferences' => 'nullable|array',
                 // Champ pour la tokenisation bancaire
                'accept_tokenisation' => 'boolean',
            ], [
                // Messages d'erreur personnalisés
                'first_name.required' => 'Le prénom est obligatoire',
                'first_name.min' => 'Le prénom doit contenir au moins 2 caractères',
                'last_name.required' => 'Le nom de famille est obligatoire',
                'last_name.min' => 'Le nom de famille doit contenir au moins 2 caractères',
                'email.required' => 'L\'adresse email est obligatoire',
                'email.email' => 'Format d\'email invalide',
                'phone.required' => 'Le numéro de téléphone est obligatoire',
                'address.required' => 'L\'adresse est obligatoire',
                'address.min' => 'L\'adresse doit contenir au moins 10 caractères',
                'city.required' => 'La ville est obligatoire',
                'city.min' => 'La ville doit contenir au moins 2 caractères',
                'country.required' => 'Le pays est obligatoire',
                'date_of_birth.before' => 'Vous devez avoir au moins 16 ans',
                'emergency_contact_name.min' => 'Le nom du contact doit contenir au moins 5 caractères',
                'special_requests.max' => 'Les demandes spéciales ne peuvent pas dépasser 1000 caractères',
                'notes.max' => 'Les notes ne peuvent pas dépasser 500 caractères'
            ]);

            if ($validator->fails()) {
                Log::warning('CustomerProfile - Validation échouée', [
                    'errors' => $validator->errors()->toArray(),
                    'customer_id' => $customerId
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Récupérer la base de données tenant
            $tenantDbName = $this->getTenantDatabase($tenantName);
            
            if (!$tenantDbName) {
                Log::error('CustomerProfile - Base de données tenant non trouvée pour mise à jour', ['tenant' => $tenantName]);
                return response()->json(['success' => false, 'message' => 'Erreur de configuration'], 500);
            }

            // Connexion à la base tenant
            config(['database.connections.tenant.database' => $tenantDbName]);
            DB::purge('tenant');

            // Préparer les données à mettre à jour
            $updateData = [
                // Champs obligatoires
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
                
                // Champs importants
                'postal_code' => $request->postal_code,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'nationality' => $request->nationality,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'emergency_contact_relation' => $request->emergency_contact_relation,
                
                // Champs optionnels
                'id_number' => $request->id_number,
                'special_requests' => $request->special_requests,
                'notes' => $request->notes,
                'preferences' => $request->preferences ? json_encode($request->preferences) : null,
                
                // Champ pour la tokenisation bancaire
                'accept_tokenisation' => $request->accept_tokenisation ? 1 : 0,
                
                // Champs système
                'last_profile_update' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];

            // Calculer le pourcentage de completion - ALGORITHME OPTIMISÉ
            $completionData = $this->calculateProfileCompletion($updateData);
            $updateData['profile_completed'] = $completionData['is_completed'] ? 1 : 0;

            // Mettre à jour le client
            $updated = DB::connection('tenant')
                ->table('customers')
                ->where('id', $customerId)
                ->update($updateData);

            if (!$updated) {
                Log::error('CustomerProfile - Échec de la mise à jour', ['customer_id' => $customerId]);
                return response()->json(['success' => false, 'message' => 'Erreur lors de la mise à jour'], 500);
            }

            // Mettre à jour la session avec les nouvelles données
            $customerAuth['name'] = $updateData['name'];
            $customerAuth['email'] = $updateData['email'];
            Session::put('customer_auth', $customerAuth);

            Log::info('CustomerProfile - Profil mis à jour avec succès', [
                'customer_id' => $customerId,
                'tenant' => $tenantName,
                'profile_completed' => $completionData['is_completed'],
                'completion_percentage' => $completionData['percentage']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès !',
                'profile_completed' => $completionData['is_completed'],
                'completion_percentage' => $completionData['percentage'],
                'completion_details' => $completionData['details']
            ]);

        } catch (\Exception $e) {
            Log::error('CustomerProfile - Erreur lors de la mise à jour', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du profil'
            ], 500);
        }
    }

    /**
     * Calculer le pourcentage de completion du profil - ALGORITHME OPTIMISÉ
     */
    private function calculateProfileCompletion($data)
    {
        // Champs obligatoires (60% du score)
        $requiredFields = [
            'first_name', 'last_name', 'email', 'phone', 
            'address', 'city', 'country'
        ];
        
        // Champs importants (30% du score)
        $importantFields = [
            'postal_code', 'date_of_birth', 'gender', 'nationality',
            'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation'
        ];
        
        // Champs optionnels (10% du score)
        $optionalFields = [
            'id_number', 'special_requests', 'notes'
        ];
        
        // Compter les champs complétés
        $completedRequired = 0;
        $completedImportant = 0;
        $completedOptional = 0;
        $completedPreferences = 0;
        
        // Vérifier les champs obligatoires
        foreach ($requiredFields as $field) {
            if (!empty($data[$field])) {
                $completedRequired++;
            }
        }
        
        // Vérifier les champs importants
        foreach ($importantFields as $field) {
            if (!empty($data[$field])) {
                $completedImportant++;
            }
        }
        
        // Vérifier les champs optionnels
        foreach ($optionalFields as $field) {
            if (!empty($data[$field])) {
                $completedOptional++;
            }
        }
        
        // Vérifier les préférences
        if (!empty($data['preferences'])) {
            $preferences = is_string($data['preferences']) ? json_decode($data['preferences'], true) : $data['preferences'];
            if (is_array($preferences)) {
                $completedPreferences = count(array_filter($preferences));
            }
        }
        
        // Calcul pondéré du pourcentage
        $requiredPercentage = (count($requiredFields) > 0) ? ($completedRequired / count($requiredFields)) * 60 : 0;
        $importantPercentage = (count($importantFields) > 0) ? ($completedImportant / count($importantFields)) * 30 : 0;
        $optionalPercentage = (count($optionalFields) > 0) ? ($completedOptional / count($optionalFields)) * 5 : 0;
        $preferencesPercentage = ($completedPreferences > 0) ? min(($completedPreferences / 5) * 5, 5) : 0; // Max 5 préférences
        
        $totalPercentage = round($requiredPercentage + $importantPercentage + $optionalPercentage + $preferencesPercentage);
        
        // Profil considéré comme complété si >= 85%
        $isCompleted = $totalPercentage >= 85;
        
        return [
            'percentage' => $totalPercentage,
            'is_completed' => $isCompleted,
            'details' => [
                'required' => [
                    'completed' => $completedRequired,
                    'total' => count($requiredFields),
                    'percentage' => round($requiredPercentage)
                ],
                'important' => [
                    'completed' => $completedImportant,
                    'total' => count($importantFields),
                    'percentage' => round($importantPercentage)
                ],
                'optional' => [
                    'completed' => $completedOptional,
                    'total' => count($optionalFields),
                    'percentage' => round($optionalPercentage)
                ],
                'preferences' => [
                    'completed' => $completedPreferences,
                    'percentage' => round($preferencesPercentage)
                ]
            ]
        ];
    }

    /**
     * Récupérer le nom de la base de données tenant
     */
    private function getTenantDatabase($tenantName)
    {
        try {
            // Rechercher le tenant dans la base centrale
            $tenant = DB::connection('mysql')
                ->table('tenants')
                ->where('name', $tenantName)
                ->first();

            if (!$tenant) {
                Log::error('CustomerProfile - Tenant non trouvé', ['tenant_name' => $tenantName]);
                return null;
            }

            return $tenant->database_name;

        } catch (\Exception $e) {
            Log::error('CustomerProfile - Erreur lors de la recherche du tenant', [
                'error' => $e->getMessage(),
                'tenant_name' => $tenantName
            ]);
            return null;
        }
    }

    /**
     * Afficher la page des paiements
     */
    public function payments()
    {
        try {
            $customerAuth = Session::get('customer_auth');
            
            if (!$customerAuth || !isset($customerAuth['id'])) {
                return redirect()->route('login')->with('error', 'Session expirée.');
            }

            // TODO: Récupérer les données de paiement depuis la base tenant
            $payments = []; // Placeholder
            
            return view('customer.payments', compact('payments'));

        } catch (\Exception $e) {
            Log::error('CustomerProfile - Erreur page paiements', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('customer.dashboard')->with('error', 'Erreur lors du chargement des paiements.');
        }
    }

    /**
     * Afficher la page d'historique
     */
    public function history()
    {
        try {
            $customerAuth = Session::get('customer_auth');
            
            if (!$customerAuth || !isset($customerAuth['id'])) {
                return redirect()->route('login')->with('error', 'Session expirée.');
            }

            // TODO: Récupérer l'historique depuis la base tenant
            $history = []; // Placeholder
            
            return view('customer.history', compact('history'));

        } catch (\Exception $e) {
            Log::error('CustomerProfile - Erreur page historique', [
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('customer.dashboard')->with('error', 'Erreur lors du chargement de l\'historique.');
        }
    }

    /**
     * Obtenir le statut de completion du profil (API)
     */
    public function getProfileStatus()
    {
        try {
            $customerAuth = Session::get('customer_auth');
            
            if (!$customerAuth || !isset($customerAuth['id'])) {
                return response()->json(['error' => 'Session expirée'], 401);
            }

            $customerId = $customerAuth['id'];
            $tenantName = $customerAuth['tenant_name'] ?? 'Hôtel';
            
            $tenantDbName = $this->getTenantDatabase($tenantName);
            
            if (!$tenantDbName) {
                return response()->json(['error' => 'Configuration invalide'], 500);
            }

            config(['database.connections.tenant.database' => $tenantDbName]);
            DB::purge('tenant');
            
            $customer = DB::connection('tenant')
                ->table('customers')
                ->where('id', $customerId)
                ->first();

            if (!$customer) {
                return response()->json(['error' => 'Client non trouvé'], 404);
            }

            $completionData = $this->calculateProfileCompletion((array) $customer);

            return response()->json([
                'success' => true,
                'completion_percentage' => $completionData['percentage'],
                'profile_completed' => $completionData['is_completed'],
                'details' => $completionData['details']
            ]);

        } catch (\Exception $e) {
            Log::error('CustomerProfile - Erreur statut profil', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Erreur serveur'], 500);
        }
    }
        /**
     * Récupérer le statut du profil (API AJAX)
     */
    public function status()
    {
        try {
            // Récupérer les informations du client depuis la session
            $customerAuth = Session::get('customer_auth');
            
            if (!$customerAuth || !isset($customerAuth['id'])) {
                Log::warning('CustomerProfile - Session client non trouvée pour status');
                return response()->json(['success' => false, 'message' => 'Session expirée'], 401);
            }

            $customerId = $customerAuth['id'];
            $tenantName = $customerAuth['tenant_name'] ?? 'Hôtel';
            
            // Récupérer la base de données tenant
            $tenantDbName = $this->getTenantDatabase($tenantName);
            
            if (!$tenantDbName) {
                Log::error('CustomerProfile - Base de données tenant non trouvée pour status', ['tenant' => $tenantName]);
                return response()->json(['success' => false, 'message' => 'Erreur de configuration'], 500);
            }

            // Connexion à la base tenant
            config(['database.connections.tenant.database' => $tenantDbName]);
            DB::purge('tenant');
            
            // Récupérer les données du client
            $customer = DB::connection('tenant')
                ->table('customers')
                ->where('id', $customerId)
                ->first();

            if (!$customer) {
                Log::error('CustomerProfile - Client non trouvé pour status', ['customer_id' => $customerId]);
                return response()->json(['success' => false, 'message' => 'Client non trouvé'], 404);
            }

            // Convertir l'objet en tableau pour le calcul
            $customerData = (array) $customer;
            
            // Calculer le pourcentage de completion avec votre méthode existante
            $completionData = $this->calculateProfileCompletion($customerData);

            Log::info('CustomerProfile - Status récupéré', [
                'customer_id' => $customerId,
                'completion_percentage' => $completionData['percentage']
            ]);

            return response()->json([
                'success' => true,
                'completion_percentage' => $completionData['percentage'],
                'profile_completed' => $completionData['is_completed'],
                'completion_details' => $completionData['details']
            ]);

        } catch (\Exception $e) {
            Log::error('CustomerProfile - Erreur lors de la récupération du status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur'
            ], 500);
        }
    }


}

