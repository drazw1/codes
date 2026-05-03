<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    // ── READ ALL (paginated, with eager-loading) ──────────────
    public function index(Request $request)
    {
        $query = Medicine::with(['category', 'suppliers'])   // eager load both relationships
                         ->orderBy('medicine_name');

        // Optional search filter
        if ($request->filled('search')) {
            $query->where('medicine_name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('rx')) {
            $query->where('prescription_required', $request->rx);
        }

        $medicines  = $query->paginate(10)->withQueryString(); // keeps filters on paginator links
        $categories = Category::orderBy('category_name')->get();

        return view('medicines.index', compact('medicines', 'categories'));
    }

    // ── CREATE FORM ───────────────────────────────────────────
    public function create()
    {
        $categories = Category::orderBy('category_name')->get();
        $suppliers  = Supplier::orderBy('supplier_name')->get();
        return view('medicines.create', compact('categories', 'suppliers'));
    }

    // ── STORE + attach suppliers (many-to-many) ───────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'medicine_name'         => 'required|string|max:150',
            'category_id'           => 'nullable|exists:categories,category_id',
            'price'                 => 'required|numeric|min:0',
            'stock'                 => 'required|integer|min:0',
            'prescription_required' => 'required|in:YES,NO',
            // Pivot columns for each supplier
            'suppliers'             => 'nullable|array',
            'suppliers.*.id'        => 'exists:suppliers,supplier_id',
            'suppliers.*.unit_cost' => 'nullable|numeric|min:0',
            'suppliers.*.quantity'  => 'nullable|integer|min:0',
            'suppliers.*.last_supplied_at' => 'nullable|date',
        ]);

        // Create medicine
        $medicine = Medicine::create([
            'medicine_name'         => $validated['medicine_name'],
            'category_id'           => $validated['category_id'] ?? null,
            'price'                 => $validated['price'],
            'stock'                 => $validated['stock'],
            'prescription_required' => $validated['prescription_required'],
        ]);

        // Attach suppliers with pivot data (many-to-many)
        if (!empty($validated['suppliers'])) {
            $pivotData = [];
            foreach ($validated['suppliers'] as $sup) {
                $pivotData[$sup['id']] = [
                    'unit_cost'        => $sup['unit_cost']        ?? 0,
                    'quantity'         => $sup['quantity']         ?? 0,
                    'last_supplied_at' => $sup['last_supplied_at'] ?? null,
                ];
            }
            $medicine->suppliers()->attach($pivotData);
        }

        return redirect()
            ->route('medicines.show', $medicine)
            ->with('success', 'Medicine added successfully.');
    }

    // ── SHOW SINGLE (both relationships displayed) ────────────
    public function show(Medicine $medicine)
    {
        // Eager-load category (one-to-many inverse) and suppliers (many-to-many)
        $medicine->load(['category', 'suppliers']);
        return view('medicines.show', compact('medicine'));
    }

    // ── EDIT FORM ─────────────────────────────────────────────
    public function edit(Medicine $medicine)
    {
        $medicine->load('suppliers');
        $categories = Category::orderBy('category_name')->get();
        $suppliers  = Supplier::orderBy('supplier_name')->get();
        return view('medicines.edit', compact('medicine', 'categories', 'suppliers'));
    }

    // ── UPDATE + sync suppliers ────────────────────────────────
    public function update(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'medicine_name'         => 'required|string|max:150',
            'category_id'           => 'nullable|exists:categories,category_id',
            'price'                 => 'required|numeric|min:0',
            'stock'                 => 'required|integer|min:0',
            'prescription_required' => 'required|in:YES,NO',
            'suppliers'             => 'nullable|array',
            'suppliers.*.id'        => 'exists:suppliers,supplier_id',
            'suppliers.*.unit_cost' => 'nullable|numeric|min:0',
            'suppliers.*.quantity'  => 'nullable|integer|min:0',
            'suppliers.*.last_supplied_at' => 'nullable|date',
        ]);

        $medicine->update([
            'medicine_name'         => $validated['medicine_name'],
            'category_id'           => $validated['category_id'] ?? null,
            'price'                 => $validated['price'],
            'stock'                 => $validated['stock'],
            'prescription_required' => $validated['prescription_required'],
        ]);

        // sync() replaces all pivot rows – the correct way to update many-to-many
        $pivotData = [];
        foreach ($validated['suppliers'] ?? [] as $sup) {
            $pivotData[$sup['id']] = [
                'unit_cost'        => $sup['unit_cost']        ?? 0,
                'quantity'         => $sup['quantity']         ?? 0,
                'last_supplied_at' => $sup['last_supplied_at'] ?? null,
            ];
        }
        $medicine->suppliers()->sync($pivotData);

        return redirect()
            ->route('medicines.show', $medicine)
            ->with('success', 'Medicine updated successfully.');
    }

    // ── DELETE ────────────────────────────────────────────────
    public function destroy(Medicine $medicine)
    {
        // pivot rows are cascade-deleted by the DB constraint
        $medicine->delete();

        return redirect()
            ->route('medicines.index')
            ->with('success', 'Medicine deleted.');
    }
}
