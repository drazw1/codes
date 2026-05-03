<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SupplierApiController extends Controller
{
    public function index(): JsonResponse
    {
        $suppliers = Supplier::withCount('medicines')
                             ->orderBy('supplier_name')
                             ->paginate(15);

        return response()->json([
            'data'       => SupplierResource::collection($suppliers->items()),
            'pagination' => [
                'total'        => $suppliers->total(),
                'per_page'     => $suppliers->perPage(),
                'current_page' => $suppliers->currentPage(),
                'last_page'    => $suppliers->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_name'  => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:100',
            'email'          => 'nullable|email|unique:suppliers,email',
            'phone'          => 'nullable|string|max:30',
            'address'        => 'nullable|string|max:255',
        ]);

        $supplier = Supplier::create($validated);

        return response()->json([
            'message' => 'Supplier created.',
            'data'    => new SupplierResource($supplier),
        ], 201);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        $supplier->load(['medicines.category']);
        return response()->json(['data' => new SupplierResource($supplier)]);
    }

    public function update(Request $request, Supplier $supplier): JsonResponse
    {
        $validated = $request->validate([
            'supplier_name'  => 'sometimes|required|string|max:150',
            'contact_person' => 'nullable|string|max:100',
            'email'          => "nullable|email|unique:suppliers,email,{$supplier->supplier_id},supplier_id",
            'phone'          => 'nullable|string|max:30',
            'address'        => 'nullable|string|max:255',
        ]);

        $supplier->update($validated);

        return response()->json([
            'message' => 'Supplier updated.',
            'data'    => new SupplierResource($supplier),
        ]);
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        $supplier->delete();
        return response()->json(['message' => 'Supplier deleted.']);
    }
}
