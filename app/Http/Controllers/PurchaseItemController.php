<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseItem\ReceivePurchaseItemRequest;
use App\Http\Requests\PurchaseItem\StorePurchaseItemRequest;
use App\Http\Requests\PurchaseItem\UpdatePurchaseItemRequest;
use App\Http\Resources\PurchaseItemResource;
use App\Http\Resources\PurchaseReceiptResource;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Services\PurchaseItemService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Attributes\Controllers\Authorize;
use Illuminate\Support\Facades\Validator;

class PurchaseItemController extends Controller
{
    #[Authorize('receive', PurchaseItem::class)]
    public function pendingReceipts(Request $request): AnonymousResourceCollection
    {
        parse_str($request->getQueryString() ?? '', $query);
        $validated = Validator::make($query, [
            'barcode' => ['required', 'string', 'max:100'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ])->validate();

        return PurchaseReceiptResource::collection($this->purchaseItem->pendingReceipts(
            $validated['barcode'], (int) ($validated['per_page'] ?? 10),
        ));
    }

    #[Authorize('receive', PurchaseItem::class)]
    public function receive(ReceivePurchaseItemRequest $request, PurchaseItem $purchaseItem): PurchaseReceiptResource
    {
        return new PurchaseReceiptResource($this->purchaseItem->receive(
            $purchaseItem, $request->validated('barcode'), (int) $request->validated('quantity'),
        ));
    }

    public function __construct(public PurchaseItemService $purchaseItem)
    {
        $this->purchaseItem = $purchaseItem;
    }

    #[Authorize('viewAny', PurchaseItem::class)]
    public function index(Request $request)
    {
        $purchaseItems = $this->purchaseItem->listPaginated($request->all());

        return PurchaseItemResource::collection($purchaseItems);
    }

    #[Authorize('view', 'purchase')]
    public function indexByPurchase(Request $request, Purchase $purchase): AnonymousResourceCollection
    {
        $purchaseItems = $this->purchaseItem->listByPurchase($purchase, $request->all());

        return PurchaseItemResource::collection($purchaseItems);
    }

    #[Authorize('create', PurchaseItem::class)]
    public function store(StorePurchaseItemRequest $request): JsonResponse
    {
        $purchaseItem = $this->purchaseItem->crear($request->validated());

        return (new PurchaseItemResource($purchaseItem))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('purchase-items.show', $purchaseItem));
    }

    #[Authorize('view', 'purchase_item')]
    public function show(PurchaseItem $purchaseItem)
    {
        return new PurchaseItemResource($purchaseItem);
    }

    #[Authorize('update', 'purchase_item')]
    public function update(UpdatePurchaseItemRequest $request, PurchaseItem $purchaseItem)
    {
        $purchaseItems = $this->purchaseItem->actualizar($purchaseItem, $request->validated());

        return new PurchaseItemResource($purchaseItems);
    }

    #[Authorize('delete', 'purchase_item')]
    public function destroy(PurchaseItem $purchaseItem)
    {
        $this->purchaseItem->eliminar($purchaseItem);

        return response()->json(null, 204);
    }
}
