<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerServices
{
    public function crear(array $customer): Customer
    {
        return Customer::create($customer);
    }

    public function actualizar(Customer $customer, array $validated): Customer
    {
        $customer->update($validated);

        return $customer;
    }

    public function eliminar(Customer $customer): void
    {
        $customer->delete();
    }

    public function getById(string $id): Customer
    {
        return Customer::findOrFail($id);
    }

    public function listPaginated(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);

        $perPage = max(1, min($perPage, 50));

        $sort = $filters['sort'] ?? 'id_Customer';

        $direction = $filters['direction'] ?? 'asc';

        $allowedSorts = [
            'id_Customer',
            'first_name',
            'second_name',
            'last_name1',
            'last_name2',
            'email',
            'telephone_number',
            'created_at'
        ];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id_Customer';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = Customer::query();

        if (! empty($filters['q'])) {
            $q = $filters['q'];

            $query->where(function ($w) use ($q) {
                $w->where('id_Customer', 'like', "%{$q}%")
                    ->orWhere('first_name', 'like', "%{$q}%")
                    ->orWhere('second_name', 'like', "%{$q}%")
                    ->orWhere('last_name1', 'like', "%{$q}%")
                    ->orWhere('last_name2', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                    ->orWhere('telephone_number', 'like', "%{$q}%");
            });
        }

        return $query
            ->orderBy($sort, $direction)
            ->orderBy('id_Customer', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }
}