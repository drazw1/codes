<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CategoryApiController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::withCount('medicines')
                               ->orderBy('category_name')
                               ->paginate(15);

        return response()->json([
            'data'       => CategoryResource::collection($categories->items()),
            'pagination' => [
                'total'        => $categories->total(),
                'per_page'     => $categories->perPage(),
                'current_page' => $categories->currentPage(),
                'last_page'    => $categories->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:100|unique:categories,category_name',
            'description'   => 'nullable|string|max:255',
        ]);

        $category = Category::create($validated);

        return response()->json([
            'message' => 'Category created.',
            'data'    => new CategoryResource($category),
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        $category->load('medicines');
        return response()->json(['data' => new CategoryResource($category)]);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'category_name' => "sometimes|required|string|max:100|unique:categories,category_name,{$category->category_id},category_id",
            'description'   => 'nullable|string|max:255',
        ]);

        $category->update($validated);

        return response()->json([
            'message' => 'Category updated.',
            'data'    => new CategoryResource($category),
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();
        return response()->json(['message' => 'Category deleted.']);
    }
}
