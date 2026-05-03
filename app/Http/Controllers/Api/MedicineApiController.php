<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MedicineResource;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * MedicineApiController
 * ──────────────────────
 * RESTful API controller for medicines.
 * Registered in api.php as: Route::apiResource('medicines', MedicineApiController::class)
 *
 * Endpoints (all under /api/):
 *   GET    /api/medicines              → index()   (paginated list)
 *   POST   /api/medicines              → store()   (create)
 *   GET    /api/medicines/{medicine}   → show()    (single record)
 *   PUT    /api/medicines/{medicine}   → update()  (full update)
 *   PATCH  /api/medicines/{medicine}   → update()  (partial update)
 *   DELETE /api/medicines/{medicine}   → destroy() (delete)
 *
 * Protected by: auth:sanctum middleware (set in api.php)
 */
class MedicineApiController extends Controller
{
    // ── GET /api/medicines ────────────────────────────────────
    /**
     * Returns paginated list of medicines.
     * Supports query params: search, category_id, rx, low_stock, per_page
     */
    public function index(Request $request): JsonResponse
    {
        $query = Medicine::with(['category', 'suppliers'])
                         ->orderBy('medicine_name');

        if ($request->filled('search')) {
            $query->where('medicine_name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }
        if ($request->filled('rx')) {
            $query->where('prescription_required', $request->rx);
        }
        if ($request->boolean('low_stock')) {
            $query->lowStock();   // uses the scope defined in Medicine model
        }

        $perPage   = min($request->integer('per_page', 15), 100);
        $medicines = $query->paginate($perPage);

        // MedicineResource::collection wraps each item through toArray()
        return response()->json([
            'data'       => MedicineResource::collection($medicines->items()),
            'pagination' => [
                'total'        => $medicines->total(),
                'per_page'     => $medicines->perPage(),
                'current_page' => $medicines->currentPage(),
                'last_page'    => $medicines->lastPage(),
                'from'         => $medicines->firstItem(),
                'to'           => $medicines->lastItem(),
            ],
        ]);
    }

    // ── POST /api/medicines ───────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'medicine_name'         => 'required|string|max:150',
            'category_id'           => 'nullable|exists:categories,category_id',
            'price'                 => 'required|numeric|min:0',
            'stock'                 => 'required|integer|min:0',
            'prescription_required' => 'required|in:YES,NO',
            'suppliers'             => 'nullable|array',
            'suppliers.*.id'        => 'required|exists:suppliers,supplier_id',
            'suppliers.*.unit_cost' => 'nullable|numeric|min:0',
            'suppliers.*.quantity'  => 'nullable|integer|min:0',
            'suppliers.*.last_supplied_at' => 'nullable|date',
        ]);

        $medicine = Medicine::create([
            'medicine_name'         => $validated['medicine_name'],
            'category_id'           => $validated['category_id'] ?? null,
            'price'                 => $validated['price'],
            'stock'                 => $validated['stock'],
            'prescription_required' => $validated['prescription_required'],
        ]);

        // Attach many-to-many suppliers
        if (!empty($validated['suppliers'])) {
            $pivotData = collect($validated['suppliers'])->mapWithKeys(fn($s) => [
                $s['id'] => [
                    'unit_cost'        => $s['unit_cost']        ?? 0,
                    'quantity'         => $s['quantity']         ?? 0,
                    'last_supplied_at' => $s['last_supplied_at'] ?? null,
                ],
            ])->all();

            $medicine->suppliers()->attach($pivotData);
        }

        $medicine->load(['category', 'suppliers']);

        return response()->json([
            'message' => 'Medicine created successfully.',
            'data'    => new MedicineResource($medicine),
        ], 201);
    }

    // ── GET /api/medicines/{medicine} ─────────────────────────
    public function show(Medicine $medicine): JsonResponse
    {
        $medicine->load(['category', 'suppliers']);

        return response()->json([
            'data' => new MedicineResource($medicine),
        ]);
    }

    // ── PUT/PATCH /api/medicines/{medicine} ───────────────────
    public function update(Request $request, Medicine $medicine): JsonResponse
    {
        $validated = $request->validate([
            'medicine_name'         => 'sometimes|required|string|max:150',
            'category_id'           => 'nullable|exists:categories,category_id',
            'price'                 => 'sometimes|required|numeric|min:0',
            'stock'                 => 'sometimes|required|integer|min:0',
            'prescription_required' => 'sometimes|required|in:YES,NO',
            'suppliers'             => 'nullable|array',
            'suppliers.*.id'        => 'required|exists:suppliers,supplier_id',
            'suppliers.*.unit_cost' => 'nullable|numeric|min:0',
            'suppliers.*.quantity'  => 'nullable|integer|min:0',
            'suppliers.*.last_supplied_at' => 'nullable|date',
        ]);

        $medicine->update(array_filter([
            'medicine_name'         => $validated['medicine_name']         ?? null,
            'category_id'           => $validated['category_id']           ?? null,
            'price'                 => $validated['price']                 ?? null,
            'stock'                 => $validated['stock']                 ?? null,
            'prescription_required' => $validated['prescription_required'] ?? null,
        ], fn($v) => $v !== null));

        if (array_key_exists('suppliers', $validated)) {
            $pivotData = collect($validated['suppliers'] ?? [])->mapWithKeys(fn($s) => [
                $s['id'] => [
                    'unit_cost'        => $s['unit_cost']        ?? 0,
                    'quantity'         => $s['quantity']         ?? 0,
                    'last_supplied_at' => $s['last_supplied_at'] ?? null,
                ],
            ])->all();

            $medicine->suppliers()->sync($pivotData);
        }

        $medicine->load(['category', 'suppliers']);

        return response()->json([
            'message' => 'Medicine updated successfully.',
            'data'    => new MedicineResource($medicine),
        ]);
    }

    // ── DELETE /api/medicines/{medicine} ─────────────────────
    public function destroy(Medicine $medicine): JsonResponse
    {
        $medicine->delete();

        return response()->json([
            'message' => 'Medicine deleted successfully.',
        ], 200);
    }
}
