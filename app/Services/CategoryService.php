<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{

    public function createCategory(array $validated)
    {
        $category = Category::create($validated);
        return $category;
    }
}
