<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SupplierServices
{
    public function crear(array $supplier): Supplier
    {
        // Verifica que el usuario tenga permiso para crear proveedores
        Gate::authorize('create', Supplier::class);

        return Supplier::create($supplier);
    }

    public function actualizar(Supplier $supplier, array $validated): Supplier
    {
        // Verifica que el usuario tenga permiso para actualizar este proveedor
        Gate::authorize('update', $supplier);

        $supplier->update($validated);

        return $supplier;
    }

    public function eliminar(Supplier $supplier): void
    {
        // Verifica que el usuario tenga permiso para eliminar este proveedor
        Gate::authorize('delete', $supplier);

        // Verifica que el proveedor no tenga compras asociadas
        if ($supplier->purchases()->exists()) {
            throw new BusinessException(
                'No se puede eliminar el proveedor porque tiene compras asociadas.',
                409
            );
        }

        DB::transaction(function () use ($supplier) {
            $supplier->delete();
        });
    }

    public function getById(string $id): Supplier
    {
        // Busca el proveedor por su ID
        $supplier = Supplier::findOrFail($id);

        // Verifica que el usuario tenga permiso para ver este proveedor
        Gate::authorize('view', $supplier);

        return $supplier;
    }

    public function listPaginated(array $filters): LengthAwarePaginator
    {
        // Verifica que el usuario tenga permiso para ver la lista de proveedores
        Gate::authorize('viewAny', Supplier::class);

        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        $sort = $filters['sort'] ?? 'id_supplier';
        $direction = $filters['direction'] ?? 'asc';

        $allowedSorts = [
            'supplier_first_name',
            'supplier_last_name',
            'supplier_email',
            'id_supplier',
            'created_at',
        ];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id_supplier';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = Supplier::query();

        if (! empty($filters['q'])) {
            $q = $filters['q'];

            $query->where(function ($w) use ($q) {
                $w->where('supplier_first_name', 'like', "%{$q}%")
                    ->orWhere('supplier_last_name', 'like', "%{$q}%")
                    ->orWhere('supplier_email', 'like', "%{$q}%");
            });
        }

        if (! empty($filters['supplier_type'])) {
            $query->where('supplier_type', $filters['supplier_type']);
        }

        return $query
            ->orderBy($sort, $direction)
            ->orderBy('id_supplier', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }
}
