<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SupplierServices
{
    public function crear($supplier): Supplier
    {
        return Supplier::create($supplier);
    }

    public function actualizar(Supplier $supplier, array $validated): Supplier
    {
        $supplier->update($validated);

        return $supplier;
    }

    public function eliminar(Supplier $supplier): void
    {
        if ($supplier->purchases()->exists()) {
            throw new BusinessException('No se puede eliminar el proveedor porque tiene compras asociadas.', 409);
        }

        DB::transaction(function () use ($supplier) {
            $supplier->delete();
        });
    }

    public function listPaginated(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));
        $sort = $filters['sort'] ?? 'id_Supplier';
        $direction = $filters['direction'] ?? 'asc';
        $allowedSorts = ['Supplier_First_name', 'Supplier_Last_name', 'Supplier_Email', 'id_Supplier', 'created_at'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id_Supplier';
        }
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = Supplier::query();

        if (! empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($w) use ($q) {
                $w->where('Supplier_First_name', 'like', "%{$q}%")
                    ->orWhere('Supplier_Last_name', 'like', "%{$q}%")
                    ->orWhere('Supplier_Email', 'like', "%{$q}%");
            });
        }

        if (! empty($filters['Supplier_Type'])) {
            $query->where('Supplier_Type', $filters['Supplier_Type']);
        }

        return $query->orderBy($sort, $direction)->orderBy('id_Supplier', 'asc')->paginate($perPage)->withQueryString();
    }
}
