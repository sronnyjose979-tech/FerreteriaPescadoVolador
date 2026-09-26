<?php

namespace App\Http\Controllers;

use App\Http\Requests\Brand\StoreBrandRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class CategoryController extends Controller
{
    public function __construct(protected CategoryService $category)
    {
        $this->category = $category;
    }

    #[Authorize('viewAny', Category::class)]
    public function index(Request $request)
    {
        $category = $this->category->listPaginated($request->all());
        return CategoryResource::collection($category);
    }

    #[Authorize('store', Category::class)]
    public function store(StoreBrandRequest $request)
    {
        $category = $this->category->crear($request->validated());
        return response()->json($category, 201);
    }

    #[Authorize('show', Category::class)]
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    #[Authorize('update', Category::class)]
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category = $this->category->actualizar($category, $request->validated());

        return new CategoryResource($category);
    }

    #[Authorize('delete', Category::class)]
    public function destroy(Category $category)
    {
        $this->category->eliminar($category);
        return response()->json(null, 204);
    }
}
