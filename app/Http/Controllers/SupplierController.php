<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        // withCount uses a subquery – no N+1 issue
        $suppliers = Supplier::withCount('medicines')
                             ->orderBy('supplier_name')
                             ->paginate(10);

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_name'  => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:100',
            'email'          => 'nullable|email|max:150|unique:suppliers,email',
            'phone'          => 'nullable|string|max:30',
            'address'        => 'nullable|string|max:255',
        ]);

        Supplier::create($validated);

        return redirect()->route('suppliers.index')
                         ->with('success', 'Supplier added successfully.');
    }

    public function show(Supplier $supplier)
    {
        // Eager-load medicines through the pivot (many-to-many show)
        $medicines = $supplier->medicines()
                              ->with('category')
                              ->paginate(10);

        return view('suppliers.show', compact('supplier', 'medicines'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'supplier_name'  => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:100',
            'email'          => "nullable|email|max:150|unique:suppliers,email,{$supplier->supplier_id},supplier_id",
            'phone'          => 'nullable|string|max:30',
            'address'        => 'nullable|string|max:255',
        ]);

        $supplier->update($validated);

        return redirect()->route('suppliers.index')
                         ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')
                         ->with('success', 'Supplier deleted.');
    }
}
