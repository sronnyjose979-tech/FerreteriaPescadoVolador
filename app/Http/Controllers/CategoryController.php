<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
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

    #[Authorize('create', Category::class)]
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->category->crear($request->validated());

        return (new CategoryResource($category))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('categories.show', $category));
    }

    #[Authorize('view', 'category')]
    public function show(Category $category)
    {
        return new CategoryResource($category);
    }

    #[Authorize('update', 'category')]
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category = $this->category->actualizar($category, $request->validated());

        return new CategoryResource($category);
    }

    #[Authorize('delete', 'category')]
    public function destroy(Category $category)
    {
        $this->category->eliminar($category);

        return response()->json(null, 204);
    }
}
