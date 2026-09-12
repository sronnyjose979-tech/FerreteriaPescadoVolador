<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Services\SupplierServices;
use App\Http\Requests\StoreSupplierRequest;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{

    public function __construct(public SupplierServices $orderSupplier)
    {
        $this->orderSupplier = $orderSupplier;
    }

    public function index(Request $request)
    {

        $name = $request->input('name');

        return Supplier::when($name, function ($query, $name) {
            return $query->where('Supplier_First_name', 'like', "$name%");
        })
            ->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierRequest  $request)
    {
        $validatedData = $request->validated();
        $supplier = $this->orderSupplier->crear($validatedData);

        return $supplier;
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        return $supplier->load('products'); //Retorno el producto del supplier
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $ValidatedData = $request->validate([
            'id_Supplier' => ['required', Rule::unique('Supplier', 'id_Supplier')->ignore($supplier->id)],
            'Supplier_fist_name' => 'required|string|max:255',
            'Supplier_last_name' => 'required|string|max:255',
            'Phone' => 'nullable|string|max:20',
            'Address' => 'nullable|string|max:255',
            'Email' => 'nullable|email|max:255',
        ]);
        $supplier->update($ValidatedData);
        return $supplier;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return response()->json(null, 204);
    }
}
