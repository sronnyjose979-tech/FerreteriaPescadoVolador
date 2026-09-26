<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Requests\Payment\UpdatePaymenteRequest;
use App\Http\Requests\PurchaseItem\StorePurchaseItemRequest;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\SaleResource;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Authorize;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct(public PaymentService $payment)
    {
        $this->payment = $payment;
    }

    #[Authorize('viewAny', Payment::class)]
    public function index(Request $request)
    {
        $payment = $this->payment->listPaginated($request->all());
        return PaymentResource::collection($payment);
    }

    /**
     * Store a newly created resource in storage.
     */
    #[Authorize('create', Payment::class)]
    public function store(StorePaymentRequest $request)
    {
        $payment = $this->payment->crear($request->validated());
        return response()->json($payment, 201);
    }

    /**
     * Display the specified resource.
     */
    #[Authorize('view', 'payment')]
    public function show(Payment $payment)
    {
        return new PaymentResource($payment);
    }

    /**
     * Update the specified resource in storage.
     */
    #[Authorize('update', Payment::class)]
    public function update(UpdatePaymenteRequest $request, Payment $payment)
    {
        $payment = $this->payment->actualizar($payment, $request->validated());
        return new PaymentResource($payment);
    }

    /**
     * Remove the specified resource from storage.
     */
    #[Authorize('delete', Payment::class)]
    public function destroy(Payment $payment)
    {
        $this->payment->eliminar($payment);
        return response()->json(null, 204);
    }
}
