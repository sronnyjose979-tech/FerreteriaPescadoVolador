<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Services\CustomerServices;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Support\Facades\Gate;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(public CustomerServices $customer)
    {
        $this->customer = $customer;
    }

    #[Authorize('viewAny', Customer::class)]
    public function index(Request $request)
    {
        $customer = $this->customer->listPaginated($request->all());
        return CustomerResource::collection($customer);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[Authorize('store', Customer::class)]
    public function store(StoreCustomerRequest $request)
    {
        $customer = $this->customer->crear($request->validated());
        return response()->json($customer, 201);
    }

    /**
     * Display the specified resource.
     */
    #[Authorize('show', Customer::class)]
    public function show(Customer $customer)
    {
        return new CustomerResource($customer);
    }

    /**
     * Update the specified resource in storage.
     */
    #[Authorize('viewAny', Customer::class)]
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer = $this->customer->actualizar($customer, $request->validated());
        return new CustomerResource($customer);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[Authorize('viewAny', Customer::class)]
    public function destroy(Customer $customer)
    {
        $this->customer->eliminar($customer);
        return response()->json(null, 204);
    }
}
