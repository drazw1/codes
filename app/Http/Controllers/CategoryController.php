<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

/**
 * CategoryController
 * -------------------
 * Standard Laravel Resource Controller for categories.
 * Registered in web.php as: Route::resource('categories', CategoryController::class)
 * which auto-maps:
 *   GET    /categories           → index()
 *   GET    /categories/create    → create()
 *   POST   /categories           → store()
 *   GET    /categories/{id}      → show()
 *   GET    /categories/{id}/edit → edit()
 *   PUT    /categories/{id}      → update()
 *   DELETE /categories/{id}      → destroy()
 */
class CategoryController extends Controller
{
    // ── READ ALL (with pagination) ────────────────────────────
    public function index()
    {
        // paginate(8) returns a LengthAwarePaginator
        // used in Blade as: $categories->links()
        $categories = Category::withCount('medicines')
                               ->orderBy('category_name')
                               ->paginate(8);

        return view('categories.index', compact('categories'));
    }

    // ── CREATE FORM ───────────────────────────────────────────
    public function create()
    {
        return view('categories.create');
    }

    // ── STORE (INSERT) ────────────────────────────────────────
    public function store(Request $request)
    {
        // Validation rules
        $validated = $request->validate([
            'category_name' => 'required|string|max:100|unique:categories,category_name',
            'description'   => 'nullable|string|max:255',
        ]);

        Category::create($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    // ── SHOW SINGLE (with its medicines – one-to-many demo) ───
    public function show(Category $category)
    {
        // Eager-load the relationship:  $category->medicines
        // paginate the relationship result set
        $medicines = $category->medicines()
                               ->orderBy('medicine_name')
                               ->paginate(10);

        return view('categories.show', compact('category', 'medicines'));
    }

    // ── EDIT FORM ─────────────────────────────────────────────
    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    // ── UPDATE ────────────────────────────────────────────────
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'category_name' => "required|string|max:100|unique:categories,category_name,{$category->category_id},category_id",
            'description'   => 'nullable|string|max:255',
        ]);

        $category->update($validated);

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    // ── DELETE ────────────────────────────────────────────────
    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category deleted. Associated medicines now uncategorised.');
    }
}
