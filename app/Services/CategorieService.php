<?php

namespace App\Services;

use App\Models\Categorie;

class CategorieService
{

    public function createCategory(array $validated)
    {
        $category = Categorie::create($validated);
        return $category;
    }
}
