<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * CategoryController
 * 
 * Handles API requests for category management.
 */
class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     * 
     * Returns active categories with optional product counts.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::where('is_active', true);

        // Include product count if requested
        if ($request->has('with_products')) {
            $query->withCount('products');
        }

        // Include products if requested
        if ($request->has('with_products_list')) {
            $query->with('products');
        }

        $categories = $query->get();

        return response()->json($categories);
    }

    /**
     * Store a newly created category.
     * 
     * Note: Should be protected by admin middleware in production.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
        ]);

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

    /**
     * Display the specified category.
     * 
     * Optionally includes products.
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $query = Category::where('is_active', true);

        if ($request->has('with_products')) {
            $query->with('products');
        }

        $category = $query->findOrFail($id);

        return response()->json($category);
    }

    /**
     * Update the specified category.
     * 
     * Note: Should be protected by admin middleware in production.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:categories,slug,' . $id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return response()->json($category);
    }

    /**
     * Remove the specified category.
     * 
     * Note: Should be protected by admin middleware in production.
     */
    public function destroy(string $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Category deleted successfully'], 200);
    }
}
