<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Product;
use App\Models\PurchaseItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ProductService
{
    public function crear(array $validated): Product
    {
        // Verifica que el usuario tenga permiso para crear productos
        Gate::authorize('create', Product::class);

        $this->validateStockCoherence($validated);

        return Product::create($validated);
    }

    public function actualizar(Product $product, array $validated): Product
    {
        // Verifica que el usuario tenga permiso para actualizar este producto
        Gate::authorize('update', $product);

        $merged = array_merge($product->toArray(), $validated);

        $this->validateStockCoherence($merged);

        $product->update($validated);

        return $product;
    }

    public function eliminar(Product $product): void
    {
        // Verifica que el usuario tenga permiso para eliminar este producto
        Gate::authorize('delete', $product);

        if (PurchaseItem::where('id_product', $product->id)->exists()) {
            throw new BusinessException(
                'No se puede eliminar el producto porque tiene dependencias activas.',
                409
            );
        }

        DB::transaction(function () use ($product) {
            $product->delete();
        });
    }

    public function getById(int $id): Product
    {
        // Busca el producto por su ID
        $product = Product::findOrFail($id);

        // Verifica que el usuario tenga permiso para ver este producto
        Gate::authorize('view', $product);

        return $product;
    }

    public function listPaginated(array $filters): LengthAwarePaginator
    {
        // Verifica que el usuario tenga permiso para ver la lista de productos
        Gate::authorize('viewAny', Product::class);

        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        $sort = $filters['sort'] ?? 'id';
        $direction = $filters['direction'] ?? 'asc';

        $allowedSorts = [
            'name',
            'price',
            'stock_quantity',
            'id',
            'created_at'
        ];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
        }

        if (!in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = Product::query()
            ->with(['category', 'brand', 'unit']);

        if (!empty($filters['q'])) {
            $q = $filters['q'];

            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%")
                    ->orWhere('barcode', 'like', "%{$q}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where(
                'is_active',
                filter_var(
                    $filters['is_active'],
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                ) ?? $filters['is_active']
            );
        }

        if (!empty($filters['low_stock'])) {
            $query->lowStock();
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        return $query
            ->orderBy($sort, $direction)
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    private function validateStockCoherence(array $data): void
    {
        $min = $data['minimum_stock'] ?? null;
        $max = $data['maximum_stock'] ?? null;
        $stock = $data['stock_quantity'] ?? null;

        if ($min !== null && $max !== null && $min > $max) {
            throw new BusinessException(
                'El stock mínimo no puede ser mayor que el stock máximo.',
                422
            );
        }

        if ($stock !== null && $min !== null && $stock < $min) {
            throw new BusinessException(
                'La cantidad en stock no puede ser menor que el stock mínimo.',
                422
            );
        }

        if ($stock !== null && $max !== null && $stock > $max) {
            throw new BusinessException(
                'La cantidad en stock no puede superar el stock máximo.',
                422
            );
        }
    }
}
