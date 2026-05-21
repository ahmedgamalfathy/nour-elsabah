<?php

namespace App\Services\Order;

use App\Enums\Order\DiscountType;
use App\Enums\Order\OrderStatus;
use App\Models\Order\Order;
use App\Services\Inventory\InventoryService;
use App\Traits\ValidatesOrderQuantity;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OrderService
{
    use ValidatesOrderQuantity;

    public function __construct(
        protected OrderItemService $orderItemService,
        protected InventoryService $inventoryService,
    ) {}

    public function allOrders()
    {
        return QueryBuilder::for(Order::class)
            ->allowedFilters([
                'number',
                AllowedFilter::exact('clientId', 'client_id'),
                'status',
            ])
            ->orderByDesc('created_at')
            ->get();
    }

    public function editOrder(int $id): Order
    {
        $order = Order::with(['items', 'client', 'clientAddress', 'clientPhone', 'clientEmail'])->find($id);

        if (! $order) {
            throw new ModelNotFoundException();
        }

        return $order;
    }

    /**
     * Create a dashboard order without mutating inventory directly.
     *
     * The requested status is applied after line creation and total
     * recalculation so the OrderObserver can safely run inventory/points
     * side-effects against a complete aggregate.
     */
    public function createOrder(array $data): Order
    {
        $qtyErrors = $this->validateItemsQuantities($data['orderItems'] ?? []);
        if (! empty($qtyErrors)) {
            throw new \InvalidArgumentException(json_encode(['quantityErrors' => $qtyErrors]));
        }

        $requestedStatus = OrderStatus::from($data['status']);

        $order = Order::create([
            'discount'          => $data['discount'] ?? 0,
            'discount_type'     => DiscountType::from($data['discountType'])->value,
            'client_phone_id'   => $data['clientPhoneId'],
            'client_email_id'   => $data['clientEmailId'],
            'client_address_id' => $data['clientAddressId'],
            'client_id'         => $data['clientId'],
            'status'            => OrderStatus::DRAFT->value,
        ]);

        foreach ($data['orderItems'] as $itemData) {
            $this->orderItemService->createOrderItem([
                'orderId' => $order->id,
                ...$itemData,
            ]);
        }

        $order->recalculateTotals();
        $this->inventoryService->assertStockAvailable($order);

        if ($requestedStatus !== OrderStatus::DRAFT) {
            $order->update(['status' => $requestedStatus->value]);
        }

        return $order->fresh(['items']);
    }

    /**
     * Update a dashboard order while keeping status transitions last.
     *
     * This prevents observers from decrementing inventory against stale line
     * items, and makes the status transition the single side-effect boundary.
     */
    public function updateOrder(int $id, array $data): Order
    {
        $itemsToValidate = array_filter(
            $data['orderItems'] ?? [],
            fn ($i) => in_array($i['actionStatus'] ?? '', ['create', 'update'])
        );

        $qtyErrors = $this->validateItemsQuantities(array_values($itemsToValidate));
        if (! empty($qtyErrors)) {
            throw new \InvalidArgumentException(json_encode(['quantityErrors' => $qtyErrors]));
        }

        $requestedStatus = OrderStatus::from($data['status']);

        $order = Order::where('id', $id)->lockForUpdate()->firstOrFail();
        $wasInventoryDeducted = $order->inventory_deducted;

        if ($wasInventoryDeducted) {
            $this->inventoryService->restoreStock($order);
            $order->refresh();
        }

        $order->update([
            'discount'          => $data['discount'] ?? 0,
            'discount_type'     => DiscountType::from($data['discountType'])->value,
            'client_phone_id'   => $data['clientPhoneId'] ?? null,
            'client_email_id'   => $data['clientEmailId'] ?? null,
            'client_address_id' => $data['clientAddressId'] ?? null,
            'client_id'         => $data['clientId'],
        ]);

        foreach ($data['orderItems'] as $itemData) {
            $action = $itemData['actionStatus'] ?? '';

            if ($action === 'update') {
                $this->orderItemService->updateOrderItem($itemData['orderItemId'], $itemData);
            }

            if ($action === 'delete') {
                $this->orderItemService->deleteOrderItem($itemData['orderItemId']);
            }

            if ($action === 'create') {
                $this->orderItemService->createOrderItem([
                    'orderId' => $order->id,
                    ...$itemData,
                ]);
            }
        }

        $order->recalculateTotals();
        $this->inventoryService->assertStockAvailable($order);

        if ($order->status !== $requestedStatus) {
            $order->update(['status' => $requestedStatus->value]);
        } elseif ($wasInventoryDeducted && $requestedStatus === OrderStatus::CONFIRM) {
            $this->inventoryService->decrementStockIfNeeded($order);
        }

        return $order->fresh(['items']);
    }

    public function deleteOrder(int $id): void
    {
        $order = Order::find($id);

        if (! $order) {
            throw new ModelNotFoundException();
        }

        $order->delete();
    }
}
