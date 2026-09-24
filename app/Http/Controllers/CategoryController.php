<?php

namespace App\Http\Controllers;

use App\Http\Requests\Brand\StoreBrandRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(protected CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        $category = $this->categoryService->listPaginated($request->all());
        return CategoryResource::collection($category);
    }

    public function store(StoreBrandRequest $request)
    {
        $validated = $request->validated();
        $category = $this->categoryService->createCategory($validated);

        return response()->json(new CategoryResource($category), 201);
    }

    public function show(string $id)
    {
        $category = Category::findOrFail($id);

        return new CategoryResource($category);
    }

    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'category_name' => 'string|max:200',
            'description' => 'string|max:200',
        ]);

        $category->update($validated);

        return new CategoryResource($category);
    }

    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json(null, 204);
    }
}