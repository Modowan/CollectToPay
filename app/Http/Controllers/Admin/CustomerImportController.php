<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Mail\SetPasswordMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CustomerImportController extends Controller
{
    public function index()
    {
        return view('admin.customer-import.index');
    }

    public function create()
    {
        try {
            $tenants = Tenant::whereIn('status', ['active', 'maintenance'])
                ->select('id', 'name', 'domain', 'database_name', 'status')
                ->orderBy('name')
                ->get();

            Log::info('Retrieved ' . $tenants->count() . ' tenants from main database (including maintenance)');

            return view('admin.customer-import.create', compact('tenants'));
            
        } catch (\Exception $e) {
            Log::error('Error retrieving tenants: ' . $e->getMessage());
            
            $tenants = collect();
            return view('admin.customer-import.create', compact('tenants'))
                ->with('error', 'Error loading hotels list');
        }
    }

    public function getBranches(Request $request)
    {
        try {
            $tenantId = $request->get('tenant_id');
            
            if (!$tenantId) {
                Log::warning('No tenant ID provided');
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant ID is required',
                    'branches' => []
                ]);
            }

            $tenant = Tenant::find($tenantId);
            
            if (!$tenant) {
                Log::error('Tenant not found: ' . $tenantId);
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant not found',
                    'branches' => []
                ]);
            }

            Log::info('Processing branches request for tenant: ' . $tenant->name . ' (status: ' . $tenant->status . ')');

            $branches = $this->fetchBranchesFromTenantDatabase($tenant);

            if ($branches->isEmpty()) {
                Log::warning('No branches found in tenant database: ' . $tenant->name);
                
                return response()->json([
                    'success' => false,
                    'message' => 'No branches available for this hotel',
                    'branches' => []
                ]);
            }

            Log::info('Returned ' . $branches->count() . ' branches for tenant: ' . $tenant->name);

            return response()->json([
                'success' => true,
                'message' => 'Branches loaded successfully',
                'branches' => $branches->toArray()
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving branches: ' . $e->getMessage());
            Log::error('Error details: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving branches: ' . $e->getMessage(),
                'branches' => []
            ], 500);
        }
    }

    private function fetchBranchesFromTenantDatabase($tenant)
    {
        $databaseName = $this->getTenantDatabaseName($tenant);
        
        if (!$databaseName) {
            Log::error('No database found for tenant: ' . $tenant->name);
            return collect();
        }

        Log::info('Attempting to connect to database: ' . $databaseName);

        try {
            if (!$this->databaseExists($databaseName)) {
                Log::warning('Database does not exist: ' . $databaseName);
                return collect();
            }

            $connectionName = 'tenant_' . $tenant->id;
            $this->createTenantConnection($connectionName, $databaseName);

            $tenantConnection = DB::connection($connectionName);
            
            $tenantConnection->getPdo();
            Log::info('Successfully connected to tenant database: ' . $databaseName);
            
            if (!$tenantConnection->getSchemaBuilder()->hasTable('branches')) {
                Log::warning('Branches table does not exist in database: ' . $databaseName);
                return collect();
            }

            $cacheKey = 'branches_' . $tenant->id;
            
            $branches = Cache::remember($cacheKey, 300, function () use ($tenantConnection, $databaseName) {
                Log::info('Fetching branches from database: ' . $databaseName);
                $result = $tenantConnection->table('branches')
                    ->select('id', 'name', 'address', 'city', 'phone', 'email')
                    ->orderBy('name')
                    ->get();
                Log::info('Found ' . $result->count() . ' branches in database: ' . $databaseName);
                return $result;
            });

            return $branches;

        } catch (\Exception $e) {
            Log::error('Error connecting to tenant database ' . $databaseName . ': ' . $e->getMessage());
            Log::error('Connection error details: ' . $e->getTraceAsString());
            return collect();
        }
    }

    private function getTenantDatabaseName($tenant)
    {
        if (!empty($tenant->database_name)) {
            Log::info('Using existing database name: ' . $tenant->database_name);
            return $tenant->database_name;
        }

        $hotelName = $this->sanitizeHotelName($tenant->name);
        $databaseName = 'collect_hotel_' . $hotelName;
        
        Log::info('Generated database name: ' . $databaseName . ' for tenant: ' . $tenant->name);
        
        try {
            $tenant->update(['database_name' => $databaseName]);
            Log::info('Updated database name for tenant ' . $tenant->name . ': ' . $databaseName);
        } catch (\Exception $e) {
            Log::error('Error updating database name: ' . $e->getMessage());
        }
        
        return $databaseName;
    }

    private function sanitizeHotelName($name)
    {
        $name = strtolower($name);
        $name = str_replace(['hôtel', 'hotel', ' '], ['', '', '_'], $name);
        $name = preg_replace('/[^a-z0-9_]/', '', $name);
        
        return $name;
    }

    private function databaseExists($databaseName)
    {
        try {
            $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?";
            $databases = DB::select($query, [$databaseName]);
            $exists = !empty($databases);
            
            Log::info('Database existence check for ' . $databaseName . ': ' . ($exists ? 'EXISTS' : 'NOT FOUND'));
            
            return $exists;
        } catch (\Exception $e) {
            Log::error('Error checking database existence for ' . $databaseName . ': ' . $e->getMessage());
            return false;
        }
    }

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
            'strict' => false, // DISABLE STRICT MODE to avoid field errors
            'engine' => null,
            'modes' => [
                'ONLY_FULL_GROUP_BY',
                'ERROR_FOR_DIVISION_BY_ZERO',
                'NO_AUTO_CREATE_USER',
                'NO_ENGINE_SUBSTITUTION'
            ],
        ];
        
        config(['database.connections.' . $connectionName => $config]);
        
        Log::info('Created tenant connection: ' . $connectionName . ' for database: ' . $databaseName);
    }

    public function upload(Request $request)
    {
        // Enhanced validation with better error messages
        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'branch_id' => 'required|integer|min:1',
            'import_file' => 'required|file|mimes:csv,txt|max:10240',
        ], [
            'tenant_id.required' => 'Please select a hotel',
            'tenant_id.exists' => 'Selected hotel does not exist',
            'branch_id.required' => 'Please select a branch',
            'branch_id.integer' => 'Invalid branch selection',
            'import_file.required' => 'Please select a CSV file to upload',
            'import_file.mimes' => 'File must be a CSV format (.csv)',
            'import_file.max' => 'File size must not exceed 10MB'
        ]);

        try {
            $tenant = Tenant::find($request->tenant_id);
            $branchId = $request->branch_id;
            $file = $request->file('import_file');

            Log::info('=== STARTING CSV IMPORT DEBUG ===');
            Log::info('Tenant: ' . $tenant->name . ' (ID: ' . $tenant->id . ')');
            Log::info('Branch ID: ' . $branchId);
            Log::info('File: ' . $file->getClientOriginalName() . ' (' . $file->getSize() . ' bytes)');
            Log::info('File MIME: ' . $file->getClientMimeType());
            Log::info('File Extension: ' . $file->getClientOriginalExtension());

            // Validate branch exists in tenant database
            $branchValidation = $this->validateBranchExists($tenant, $branchId);
            if (!$branchValidation['valid']) {
                Log::error('Branch validation failed: ' . $branchValidation['message']);
                return response()->json([
                    'success' => false,
                    'message' => $branchValidation['message'],
                    'errors' => ['branch_id' => [$branchValidation['message']]]
                ], 400);
            }

            // Process the simplified CSV file with enhanced debugging
            $importResult = $this->processSimplifiedCSVWithDebug($file, $tenant, $branchId);

            if ($importResult['success']) {
                Log::info('CSV processing completed successfully. Imported: ' . $importResult['imported_count'] . ' customers');
                
                // Send password setup emails to all imported customers
                $emailResult = $this->sendPasswordSetupEmails($importResult['imported_customers'], $tenant);
                
                return response()->json([
                    'success' => true,
                    'message' => 'File uploaded and processed successfully',
                    'imported_count' => $importResult['imported_count'],
                    'errors_count' => $importResult['errors_count'],
                    'emails_sent' => $emailResult['sent_count'],
                    'email_errors' => $emailResult['error_count'],
                    'details' => $importResult['details'] . '. ' . $emailResult['message'],
                    'errors' => $importResult['errors'] ?? [],
                    'debug_info' => $importResult['debug_info'] ?? []
                ]);
            } else {
                Log::error('CSV processing failed: ' . $importResult['message']);
                
                return response()->json([
                    'success' => false,
                    'message' => 'File processing failed: ' . $importResult['message'],
                    'errors' => $importResult['errors'] ?? [],
                    'debug_info' => $importResult['debug_info'] ?? []
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error uploading file: ' . $e->getMessage());
            Log::error('Upload error details: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error uploading file: ' . $e->getMessage(),
                'debug_info' => [
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }

    private function validateBranchExists($tenant, $branchId)
    {
        try {
            $databaseName = $this->getTenantDatabaseName($tenant);
            $connectionName = 'tenant_' . $tenant->id;
            $this->createTenantConnection($connectionName, $databaseName);
            
            $tenantConnection = DB::connection($connectionName);
            
            if (!$tenantConnection->getSchemaBuilder()->hasTable('branches')) {
                return ['valid' => false, 'message' => 'Branches table does not exist for this hotel'];
            }

            $branch = $tenantConnection->table('branches')->where('id', $branchId)->first();
            
            if (!$branch) {
                return ['valid' => false, 'message' => 'Selected branch does not exist in this hotel'];
            }

            Log::info('Branch validation successful: ' . $branch->name . ' (ID: ' . $branchId . ')');
            return ['valid' => true, 'message' => 'Branch valid', 'branch' => $branch];

        } catch (\Exception $e) {
            Log::error('Error validating branch: ' . $e->getMessage());
            return ['valid' => false, 'message' => 'Error validating branch: ' . $e->getMessage()];
        }
    }

    private function processSimplifiedCSVWithDebug($file, $tenant, $branchId)
    {
        $debugInfo = [];
        
        try {
            $databaseName = $this->getTenantDatabaseName($tenant);
            $connectionName = 'tenant_' . $tenant->id;
            $this->createTenantConnection($connectionName, $databaseName);
            
            $tenantConnection = DB::connection($connectionName);
            
            // Ensure customers table exists with correct structure
            $this->ensureCustomersTableStructure($tenantConnection, $databaseName);
            $debugInfo[] = 'Ensured customers table has correct structure';

            // Ensure password_reset_tokens table exists
            if (!$tenantConnection->getSchemaBuilder()->hasTable('password_reset_tokens')) {
                Log::info('Creating password_reset_tokens table in database: ' . $databaseName);
                $this->createPasswordResetTokensTable($tenantConnection);
                $debugInfo[] = 'Created password_reset_tokens table';
            }

            // Read and parse CSV file with enhanced debugging
            $filePath = $file->getRealPath();
            Log::info('Reading CSV file from: ' . $filePath);
            
            if (!file_exists($filePath)) {
                throw new \Exception('Uploaded file not found at: ' . $filePath);
            }

            $fileContent = file_get_contents($filePath);
            Log::info('File content length: ' . strlen($fileContent) . ' bytes');
            Log::info('First 200 characters: ' . substr($fileContent, 0, 200));
            
            $csvData = array_map('str_getcsv', file($filePath));
            $debugInfo[] = 'Total CSV rows (including header): ' . count($csvData);
            
            if (empty($csvData)) {
                throw new \Exception('CSV file is empty or could not be read');
            }

            $header = array_shift($csvData); // Remove header row
            $debugInfo[] = 'CSV Header: ' . implode(', ', $header);
            
            Log::info('Processing simplified CSV with ' . count($csvData) . ' data rows');
            Log::info('CSV Header: ' . implode(', ', $header));
            
            // Validate header format
            if (count($header) < 3) {
                throw new \Exception('CSV must have at least 3 columns: first_name, last_name, email');
            }

            $importedCount = 0;
            $errorsCount = 0;
            $errors = [];
            $importedCustomers = [];
            
            foreach ($csvData as $rowIndex => $row) {
                $actualRowNumber = $rowIndex + 2; // +2 because we removed header and start from 1
                
                try {
                    Log::info('Processing row ' . $actualRowNumber . ': ' . implode(', ', $row));
                    
                    if (count($row) < 3) {
                        $error = "Row {$actualRowNumber}: Missing required fields (found " . count($row) . " columns, need 3)";
                        $errors[] = $error;
                        $errorsCount++;
                        Log::warning($error);
                        continue;
                    }
                    
                    $firstName = trim($row[0] ?? '');
                    $lastName = trim($row[1] ?? '');
                    $email = trim($row[2] ?? '');
                    
                    Log::info("Row {$actualRowNumber} data - First: '{$firstName}', Last: '{$lastName}', Email: '{$email}'");
                    
                    // Validate required fields
                    if (empty($firstName)) {
                        $error = "Row {$actualRowNumber}: First name is empty";
                        $errors[] = $error;
                        $errorsCount++;
                        Log::warning($error);
                        continue;
                    }
                    
                    if (empty($lastName)) {
                        $error = "Row {$actualRowNumber}: Last name is empty";
                        $errors[] = $error;
                        $errorsCount++;
                        Log::warning($error);
                        continue;
                    }
                    
                    if (empty($email)) {
                        $error = "Row {$actualRowNumber}: Email is empty";
                        $errors[] = $error;
                        $errorsCount++;
                        Log::warning($error);
                        continue;
                    }
                    
                    // Validate email format
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $error = "Row {$actualRowNumber}: Invalid email format: {$email}";
                        $errors[] = $error;
                        $errorsCount++;
                        Log::warning($error);
                        continue;
                    }
                    
                    // Check if email already exists
                    $existingCustomer = $tenantConnection->table('customers')
                        ->where('email', $email)
                        ->first();
                    
                    if ($existingCustomer) {
                        $error = "Row {$actualRowNumber}: Email already exists: {$email}";
                        $errors[] = $error;
                        $errorsCount++;
                        Log::warning($error);
                        continue;
                    }
                    
                    // FIXED: Include 'name' field to avoid MySQL error
                    $customerData = [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'name' => $firstName . ' ' . $lastName, // ADDED: Combined name field
                        'email' => $email,
                        'branch_id' => $branchId,
                        'status' => 'pending_password',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    
                    Log::info("Inserting customer data: " . json_encode($customerData));
                    
                    $customerId = $tenantConnection->table('customers')->insertGetId($customerData);
                    $customerData['id'] = $customerId;
                    $importedCustomers[] = $customerData;
                    $importedCount++;
                    
                    Log::info("Successfully imported customer ID {$customerId}: {$firstName} {$lastName} ({$email})");
                    
                } catch (\Exception $e) {
                    $error = "Row {$actualRowNumber}: " . $e->getMessage();
                    $errors[] = $error;
                    $errorsCount++;
                    Log::error('Error processing row ' . $actualRowNumber . ': ' . $e->getMessage());
                }
            }
            
            $debugInfo[] = "Processed {$importedCount} customers successfully";
            $debugInfo[] = "Encountered {$errorsCount} errors";
            
            return [
                'success' => true,
                'imported_count' => $importedCount,
                'errors_count' => $errorsCount,
                'details' => "Successfully imported {$importedCount} customers" . ($errorsCount > 0 ? " with {$errorsCount} errors" : ""),
                'errors' => $errors,
                'imported_customers' => $importedCustomers,
                'debug_info' => $debugInfo
            ];
            
        } catch (\Exception $e) {
            Log::error('Error processing simplified CSV file: ' . $e->getMessage());
            Log::error('CSV processing error trace: ' . $e->getTraceAsString());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'imported_count' => 0,
                'errors_count' => 0,
                'imported_customers' => [],
                'debug_info' => array_merge($debugInfo, ['Fatal error: ' . $e->getMessage()])
            ];
        }
    }

    private function ensureCustomersTableStructure($connection, $databaseName)
    {
        try {
            Log::info('Ensuring customers table structure in: ' . $databaseName);
            
            // Check if table exists
            if (!$connection->getSchemaBuilder()->hasTable('customers')) {
                Log::info('Creating customers table in: ' . $databaseName);
                $this->createCustomersTable($connection);
                return;
            }
            
            // Check if required columns exist and add them if missing
            $schema = $connection->getSchemaBuilder();
            
            if (!$schema->hasColumn('customers', 'first_name')) {
                Log::info('Adding first_name column to customers table');
                $connection->statement('ALTER TABLE customers ADD COLUMN first_name VARCHAR(100) NULL DEFAULT ""');
            }
            
            if (!$schema->hasColumn('customers', 'last_name')) {
                Log::info('Adding last_name column to customers table');
                $connection->statement('ALTER TABLE customers ADD COLUMN last_name VARCHAR(100) NULL DEFAULT ""');
            }
            
            // CRITICAL: Ensure 'name' column exists or has default value
            if (!$schema->hasColumn('customers', 'name')) {
                Log::info('Adding name column to customers table');
                $connection->statement('ALTER TABLE customers ADD COLUMN name VARCHAR(255) NULL DEFAULT ""');
            } else {
                // If name column exists but has no default, add one
                Log::info('Ensuring name column has default value');
                $connection->statement('ALTER TABLE customers MODIFY COLUMN name VARCHAR(255) NULL DEFAULT ""');
            }
            
            if (!$schema->hasColumn('customers', 'password')) {
                Log::info('Adding password column to customers table');
                $connection->statement('ALTER TABLE customers ADD COLUMN password VARCHAR(255) NULL');
            }
            
            if (!$schema->hasColumn('customers', 'branch_id')) {
                Log::info('Adding branch_id column to customers table');
                $connection->statement('ALTER TABLE customers ADD COLUMN branch_id INT NOT NULL DEFAULT 1');
            }
            
            if (!$schema->hasColumn('customers', 'status')) {
                Log::info('Adding status column to customers table');
                $connection->statement('ALTER TABLE customers ADD COLUMN status ENUM("pending_password", "active", "inactive") DEFAULT "pending_password"');
            }
            
            if (!$schema->hasColumn('customers', 'email_verified_at')) {
                Log::info('Adding email_verified_at column to customers table');
                $connection->statement('ALTER TABLE customers ADD COLUMN email_verified_at TIMESTAMP NULL');
            }
            
            Log::info('Successfully ensured customers table structure in: ' . $databaseName);
            
        } catch (\Exception $e) {
            Log::error('Error ensuring customers table structure in ' . $databaseName . ': ' . $e->getMessage());
            throw $e;
        }
    }

    private function createCustomersTable($connection)
    {
        $connection->statement('
            CREATE TABLE customers (
                id INT PRIMARY KEY AUTO_INCREMENT,
                first_name VARCHAR(100) NULL DEFAULT "",
                last_name VARCHAR(100) NULL DEFAULT "",
                name VARCHAR(255) NULL DEFAULT "",
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NULL,
                branch_id INT NOT NULL DEFAULT 1,
                status ENUM("pending_password", "active", "inactive") DEFAULT "pending_password",
                email_verified_at TIMESTAMP NULL,
                phone VARCHAR(20) NULL,
                address TEXT NULL,
                city VARCHAR(100) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_email (email),
                INDEX idx_branch (branch_id),
                INDEX idx_status (status)
            )
        ');
    }

    private function sendPasswordSetupEmails($customers, $tenant)
    {
        $sentCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($customers as $customer) {
            try {
                // Generate unique token for password setup
                $token = Str::random(64);
                
                // Store token in database
                $databaseName = $this->getTenantDatabaseName($tenant);
                $connectionName = 'tenant_' . $tenant->id;
                $tenantConnection = DB::connection($connectionName);
                
                $tenantConnection->table('password_reset_tokens')->updateOrInsert(
                    ['email' => $customer['email']],
                    [
                        'email' => $customer['email'],
                        'token' => $token, // FIXED: Store non-hashed token for compatibility
                        'created_at' => now(),
                        'expires_at' => now()->addHours(24),
                        'used_at' => null
                    ]
                );
                
                // Send email
                Mail::to($customer['email'])->send(new SetPasswordMail(
                    $customer['first_name'],
                    $customer['last_name'],
                    $token,
                    $tenant->name
                ));
                
                $sentCount++;
                Log::info('Password setup email sent to: ' . $customer['email']);
                
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = 'Failed to send email to ' . $customer['email'] . ': ' . $e->getMessage();
                Log::error('Error sending email to ' . $customer['email'] . ': ' . $e->getMessage());
            }
        }

        return [
            'sent_count' => $sentCount,
            'error_count' => $errorCount,
            'errors' => $errors,
            'message' => "Sent {$sentCount} password setup emails" . ($errorCount > 0 ? " with {$errorCount} email errors" : "")
        ];
    }

    private function createPasswordResetTokensTable($connection)
    {
        $connection->statement('
            CREATE TABLE password_reset_tokens (
                email VARCHAR(255) PRIMARY KEY,
                token VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                expires_at TIMESTAMP DEFAULT (CURRENT_TIMESTAMP + INTERVAL 24 HOUR),
                used_at TIMESTAMP NULL,
                INDEX idx_token (token),
                INDEX idx_expires (expires_at)
            )
        ');
    }

    public function preview(Request $request)
    {
        Log::info('=== PREVIEW REQUEST RECEIVED ===');
        Log::info('Request method: ' . $request->method());
        Log::info('Request data: ' . json_encode($request->all()));
        Log::info('Request files: ' . json_encode($request->allFiles()));
        
        try {
            $request->validate([
                'tenant_id' => 'required|exists:tenants,id',
                'branch_id' => 'required|integer|min:1',
                'import_file' => 'required|file|mimes:csv,txt|max:10240',
            ], [
                'tenant_id.required' => 'Please select a hotel',
                'branch_id.required' => 'Please select a branch',
                'import_file.required' => 'Please select a CSV file'
            ]);

            $tenant = Tenant::find($request->tenant_id);
            $branchId = $request->branch_id;
            $file = $request->file('import_file');

            Log::info('Starting CSV preview for tenant: ' . $tenant->name . ', branch: ' . $branchId);
            Log::info('Preview file: ' . $file->getClientOriginalName());

            // Get branch information
            $branchValidation = $this->validateBranchExists($tenant, $branchId);
            if (!$branchValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $branchValidation['message']
                ], 404);
            }

            $branch = $branchValidation['branch'];

            // Process CSV for preview (first 10 rows)
            $filePath = $file->getRealPath();
            $csvData = array_map('str_getcsv', file($filePath));
            $header = array_shift($csvData); // Remove header row
            
            Log::info('Preview CSV header: ' . implode(', ', $header));
            
            $previewData = [];
            $validCount = 0;
            $errorCount = 0;
            
            // Process only first 10 rows for preview
            $previewRows = array_slice($csvData, 0, 10);
            
            // Check existing emails in database
            $databaseName = $this->getTenantDatabaseName($tenant);
            $connectionName = 'tenant_' . $tenant->id;
            $this->createTenantConnection($connectionName, $databaseName);
            $tenantConnection = DB::connection($connectionName);
            
            $existingEmails = [];
            if ($tenantConnection->getSchemaBuilder()->hasTable('customers')) {
                $existingEmails = $tenantConnection->table('customers')
                    ->pluck('email')
                    ->toArray();
            }
            
            foreach ($previewRows as $rowIndex => $row) {
                $rowData = [
                    'row_number' => $rowIndex + 2, // +2 because we removed header and start from 1
                    'status' => 'valid',
                    'errors' => [],
                    'warnings' => [],
                    'data' => []
                ];
                
                if (count($row) < 3) {
                    $rowData['status'] = 'error';
                    $rowData['errors'][] = 'Missing required fields (found ' . count($row) . ' columns, need 3)';
                    $errorCount++;
                } else {
                    $firstName = trim($row[0] ?? '');
                    $lastName = trim($row[1] ?? '');
                    $email = trim($row[2] ?? '');
                    
                    $rowData['data'] = [
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                        'email' => $email,
                        'full_name' => $firstName . ' ' . $lastName // Show combined name
                    ];
                    
                    // Validate fields
                    if (empty($firstName) || empty($lastName) || empty($email)) {
                        $rowData['status'] = 'error';
                        $rowData['errors'][] = 'Empty required fields';
                        $errorCount++;
                    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $rowData['status'] = 'error';
                        $rowData['errors'][] = 'Invalid email format';
                        $errorCount++;
                    } else {
                        // Check if email already exists
                        if (in_array($email, $existingEmails)) {
                            $rowData['status'] = 'warning';
                            $rowData['warnings'][] = 'Email already exists - will be skipped';
                        } else {
                            $validCount++;
                        }
                    }
                }
                
                $previewData[] = $rowData;
            }
            
            Log::info('Preview completed - Valid: ' . $validCount . ', Errors: ' . $errorCount);
            
            return response()->json([
                'success' => true,
                'preview_data' => $previewData,
                'summary' => [
                    'total_rows' => count($csvData),
                    'preview_rows' => count($previewData),
                    'valid_count' => $validCount,
                    'error_count' => $errorCount,
                    'estimated_import' => max(0, count($csvData) - $errorCount)
                ],
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name
                ],
                'branch' => [
                    'id' => $branch->id,
                    'name' => $branch->name
                ],
                'file_info' => [
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'type' => $file->getClientMimeType()
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Preview validation failed: ' . json_encode($e->errors()));
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error generating preview: ' . $e->getMessage());
            Log::error('Preview error trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error generating preview: ' . $e->getMessage(),
                'debug_info' => [
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ], 500);
        }
    }

    public function getImportStats(Request $request)
    {
        try {
            $tenantId = $request->get('tenant_id');
            
            if (!$tenantId) {
                return response()->json(['error' => 'Tenant ID required'], 400);
            }
            
            $tenant = Tenant::find($tenantId);
            if (!$tenant) {
                return response()->json(['error' => 'Tenant not found'], 404);
            }
            
            $databaseName = $this->getTenantDatabaseName($tenant);
            $connectionName = 'tenant_' . $tenant->id;
            $this->createTenantConnection($connectionName, $databaseName);
            
            $tenantConnection = DB::connection($connectionName);
            
            if (!$tenantConnection->getSchemaBuilder()->hasTable('customers')) {
                return response()->json([
                    'tenant_name' => $tenant->name,
                    'total_customers' => 0,
                    'pending_password' => 0,
                    'active_customers' => 0,
                    'customers_by_branch' => [],
                    'recent_imports' => []
                ]);
            }
            
            $totalCustomers = $tenantConnection->table('customers')->count();
            $pendingPassword = $tenantConnection->table('customers')->where('status', 'pending_password')->count();
            $activeCustomers = $tenantConnection->table('customers')->where('status', 'active')->count();
            
            $customersByBranch = $tenantConnection->table('customers')
                ->join('branches', 'customers.branch_id', '=', 'branches.id')
                ->select('branches.name as branch_name', DB::raw('COUNT(*) as customer_count'))
                ->groupBy('branches.id', 'branches.name')
                ->get();
            
            $recentImports = $tenantConnection->table('customers')
                ->select(DB::raw('DATE(created_at) as import_date'), DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('import_date', 'desc')
                ->get();
            
            return response()->json([
                'tenant_name' => $tenant->name,
                'total_customers' => $totalCustomers,
                'pending_password' => $pendingPassword,
                'active_customers' => $activeCustomers,
                'customers_by_branch' => $customersByBranch,
                'recent_imports' => $recentImports
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting import stats: ' . $e->getMessage());
            return response()->json(['error' => 'Error retrieving statistics'], 500);
        }
    }

    public function clearBranchesCache($tenantId = null)
    {
        try {
            if ($tenantId) {
                Cache::forget('branches_' . $tenantId);
                Log::info('Cleared branches cache for tenant: ' . $tenantId);
            } else {
                $tenants = Tenant::pluck('id');
                foreach ($tenants as $id) {
                    Cache::forget('branches_' . $id);
                }
                Log::info('Cleared branches cache for all tenants');
            }

            return response()->json([
                'success' => true,
                'message' => 'Cache cleared successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error clearing cache: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error clearing cache'
            ], 500);
        }
    }
}

