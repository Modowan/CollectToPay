<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CustomerApiController extends Controller
{
    /**
     * Récupérer la liste des clients
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $sortBy = $request->input('sort_by', 'name');
        $sortDir = $request->input('sort_dir', 'asc');
        $search = $request->input('search', '');
        $branchId = $request->input('branch_id', '');
        $tokenStatus = $request->input('token_status', '');

        $query = Customer::with('branch');

        // Appliquer la recherche si fournie
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filtrer par filiale si fournie
        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        // Filtrer par statut de tokenisation si fourni
        if ($tokenStatus) {
            $query->where('token_status', $tokenStatus);
        }

        // Appliquer le tri
        if ($sortBy === 'branch.name') {
            $query->join('branches', 'customers.branch_id', '=', 'branches.id')
                  ->orderBy('branches.name', $sortDir)
                  ->select('customers.*');
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        // Paginer les résultats
        $customers = $query->paginate($perPage);

        return response()->json($customers);
    }

    /**
     * Récupérer un client spécifique
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $customer = Customer::with('branch')->findOrFail($id);
        return response()->json($customer);
    }

    /**
     * Créer un nouveau client
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'branch_id' => 'required|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customer = Customer::create($request->all());

        return response()->json($customer, 201);
    }

    /**
     * Mettre à jour un client existant
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'branch_id' => 'required|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customer->update($request->all());

        return response()->json($customer);
    }

    /**
     * Supprimer un client
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return response()->json(null, 204);
    }

    /**
     * Prévisualiser un fichier d'importation
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function previewImport(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt,xlsx,xls',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $file = $request->file('file');
        $path = $file->store('temp');

        // Logique pour lire le fichier et extraire un aperçu
        // Ceci est une simulation, dans un cas réel, vous utiliseriez une bibliothèque comme PhpSpreadsheet
        $preview = [
            ['Nom', 'Email', 'Téléphone', 'Adresse'],
            ['John Doe', 'john@example.com', '123456789', '123 Main St'],
            ['Jane Smith', 'jane@example.com', '987654321', '456 Oak Ave'],
            ['Bob Johnson', 'bob@example.com', '555123456', '789 Pine Rd'],
        ];

        // Supprimer le fichier temporaire
        Storage::delete($path);

        return response()->json(['preview' => $preview]);
    }

    /**
     * Importer des clients depuis un fichier
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function import(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt,xlsx,xls',
            'branch_id' => 'required|exists:branches,id',
            'has_header' => 'required|boolean',
            'column_mapping' => 'required|json',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $file = $request->file('file');
        $branchId = $request->input('branch_id');
        $hasHeader = $request->input('has_header');
        $columnMapping = json_decode($request->input('column_mapping'), true);

        // Logique pour importer les clients
        // Ceci est une simulation, dans un cas réel, vous utiliseriez une bibliothèque comme PhpSpreadsheet
        
        // Simuler des résultats d'importation
        $results = [
            'successful_records' => 3,
            'failed_records' => 1,
            'errors' => [
                [
                    'row' => 4,
                    'message' => 'Email invalide'
                ]
            ]
        ];

        return response()->json($results);
    }
}
