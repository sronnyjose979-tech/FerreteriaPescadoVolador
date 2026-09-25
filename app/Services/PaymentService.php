<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function crear(array $validated): Payment
    {
        return Payment::create($validated);
    }

    public function actualizar(Payment $payment, array $validated): Payment
    {
        $payment->update($validated);

        return $payment;
    }

    public function eliminar(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $payment->delete();
        });
    }

    public function listPaginated(array $filters): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        $sort = $filters['sort'] ?? 'id';
        $direction = $filters['direction'] ?? 'asc';

        $allowedSorts = [
            'id',
            'sale_id',
            'payment_method',
            'status',
            'created_at'
        ];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $query = Payment::query();

        if (! empty($filters['q'])) {
            $q = $filters['q'];

            $query->where(function ($w) use ($q) {
                $w->where('payment_method', 'like', "%{$q}%")
                    ->orWhere('transaction_reference', 'like', "%{$q}%")
                    ->orWhere('status', 'like', "%{$q}%");
            });
        }

        if (! empty($filters['sale_id'])) {
            $query->where('sale_id', $filters['sale_id']);
        }

        if (! empty($filters['payment_method'])) {
            $query->where(
                'payment_method',
                $filters['payment_method']
            );
        }

        if (! empty($filters['status'])) {
            $query->where(
                'status',
                $filters['status']
            );
        }

        return $query
            ->orderBy($sort, $direction)
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }
}
