<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Branch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BranchApiController extends Controller
{
    /**
     * Récupérer la liste des filiales
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
        $city = $request->input('city', '');

        $query = Branch::query()->withCount('customers');

        // Appliquer la recherche si fournie
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%");
            });
        }

        // Filtrer par ville si fournie
        if ($city) {
            $query->where('city', $city);
        }

        // Appliquer le tri
        $query->orderBy($sortBy, $sortDir);

        // Paginer les résultats
        $branches = $query->paginate($perPage);

        return response()->json($branches);
    }

    /**
     * Récupérer la liste des villes disponibles
     *
     * @return JsonResponse
     */
    public function cities(): JsonResponse
    {
        $cities = Branch::select('city')->distinct()->whereNotNull('city')->pluck('city');
        return response()->json($cities);
    }

    /**
     * Récupérer une filiale spécifique
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $branch = Branch::with('customers')->findOrFail($id);
        return response()->json($branch);
    }

    /**
     * Créer une nouvelle filiale
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $branch = Branch::create($request->all());

        return response()->json($branch, 201);
    }

    /**
     * Mettre à jour une filiale existante
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $branch = Branch::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $branch->update($request->all());

        return response()->json($branch);
    }

    /**
     * Supprimer une filiale
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $branch = Branch::findOrFail($id);
        $branch->delete();

        return response()->json(null, 204);
    }
}
