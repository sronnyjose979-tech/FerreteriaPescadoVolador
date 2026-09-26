<?php

namespace App\Services;

use App\Models\SaleItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

class SaleItemService
{
    public function crear(array $saleItem): SaleItem
    {
        // Verifica que el usuario tenga permiso para crear detalles de venta
        Gate::authorize('create', SaleItem::class);

        return SaleItem::create($saleItem);
    }

    public function actualizar(SaleItem $saleItem, array $validated): SaleItem
    {
        // Verifica que el usuario tenga permiso para actualizar este detalle de venta
        Gate::authorize('update', $saleItem);

        $saleItem->update($validated);

        return $saleItem;
    }

    public function eliminar(SaleItem $saleItem): void
    {
        // Verifica que el usuario tenga permiso para eliminar este detalle de venta
        Gate::authorize('delete', $saleItem);

        $saleItem->delete();
    }

    public function getById(int $id): SaleItem
    {
        // Busca el detalle de venta por su ID
        $saleItem = SaleItem::findOrFail($id);

        // Verifica que el usuario tenga permiso para ver este detalle de venta
        Gate::authorize('view', $saleItem);

        return $saleItem;
    }


    //Funcion de la lista paginada (recibe filtros), por ejemplo el parametro array de filtros
    public function listPaginated(array $filters): LengthAwarePaginator //el LengthAwarePaginator es el paginador
    {
        // Verifica que el usuario tenga permiso para ver la lista de detalles de venta
        Gate::authorize('viewAny', SaleItem::class);

        //El ?? means que si existe es valor usalo pero sino usar el 10
        $perPage = (int) ($filters['per_page'] ?? 10);
        //Cuantos registros mostrar por pagina, minimo 2, max 50    
        $perPage = max(1, min($perPage, 50));

        //En sort le decimos que columna usar para ordenar, en este caso id
        $sort = $filters['sort'] ?? 'id';
        //Pero por defecto esta en asc, que es ascendente
        $direction = $filters['direction'] ?? 'asc';

        //Estas son las columnas que permitimos ordenar, son las que utilizamos 
        $allowedSorts = [
            'id',
            'sale_id',
            'product_id',
            'quantity',
            'unit_price',
            'subtotal',
            'created_at'
        ];

        //aqui pregunta en in_array que si X valor esta en el arreglo, por ejemplo el id
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
        }
        //aqui solo validamos si se ponen en ascendente o descendente 
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        //Aqui se crea la consulta para la tabla SaleItem
        $query = SaleItem::query();

        //el q significa la busqueda que vamos a hacer, ejemplo (sale-items?q= 5 )
        if (! empty($filters['q'])) {
            //Aqui guardamos lo que buscamos, es el q=5
            $q = $filters['q'];

            //Estas son las condiciones de la consulta
            $query->where(function ($w) use ($q) {
                //Esta primera es busca el valor de $q dentro de id o en SQL= id LIKE '%5%'
                $w->where('id', 'like', "%{$q}%")
                    //O tambien busca en sale_id
                    ->orWhere('sale_id', 'like', "%{$q}%")
                    ->orWhere('product_id', 'like', "%{$q}%")
                    ->orWhere('quantity', 'like', "%{$q}%")
                    ->orWhere('unit_price', 'like', "%{$q}%")
                    ->orWhere('subtotal', 'like', "%{$q}%");
                /*El "%{$q}%" es para filtrar, por ejemplo si q=5, puede encontrar el 5 en donde sea
                    puede estar en 5,15,500, etc*/
            });
        }

        //aqui se devuelven los resultados de la busqueda 
        return $query
            //Esto es como en SQL ORDER BY quantity DESC
            ->orderBy($sort, $direction)
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }
}
