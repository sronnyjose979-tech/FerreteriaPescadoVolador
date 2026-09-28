<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Services\CustomerServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;

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
    #[Authorize('create', Customer::class)]
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customer->crear($request->validated());

        return (new CustomerResource($customer))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('customers.show', $customer));
    }

    /**
     * Display the specified resource.
     */
    #[Authorize('view', 'customer')]
    public function show(Customer $customer)
    {
        return new CustomerResource($customer);
    }

    /**
     * Update the specified resource in storage.
     */
    #[Authorize('update', 'customer')]
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer = $this->customer->actualizar($customer, $request->validated());

        return new CustomerResource($customer);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[Authorize('delete', 'customer')]
    public function destroy(Customer $customer)
    {
        $this->customer->eliminar($customer);

        return response()->json(null, 204);
    }
}
