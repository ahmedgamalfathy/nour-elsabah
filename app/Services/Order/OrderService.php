<?php
namespace App\Services\Order;

use App\Enums\Order\DiscountType;
use App\Enums\Order\OrderStatus;
use App\Enums\Product\LimitedQuantity;
use App\Models\Order\Order;
use App\Traits\ValidatesOrderQuantity;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class OrderService
{
    use ValidatesOrderQuantity;

    protected $orderItemService;

    public function __construct(OrderItemService $orderItemService)
    {
        $this->orderItemService = $orderItemService;
    }

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

    public function createOrder(array $data): Order
    {
        // Validate all item quantities before touching the DB
        $qtyErrors = $this->validateItemsQuantities($data['orderItems'] ?? []);
        if (! empty($qtyErrors)) {
            throw new \InvalidArgumentException(
                json_encode(['quantityErrors' => $qtyErrors])
            );
        }

        $order = Order::create([
            'discount'          => $data['discount'] ?? null,
            'discount_type'     => DiscountType::from($data['discountType'])->value,
            'client_phone_id'   => $data['clientPhoneId'],
            'client_email_id'   => $data['clientEmailId'],
            'client_address_id' => $data['clientAddressId'],
            'client_id'         => $data['clientId'],
            'status'            => OrderStatus::from($data['status'])->value,
        ]);

        $totalPrice = 0.0;
        $totalCost  = 0.0;

        foreach ($data['orderItems'] as $itemData) {
            $item = $this->orderItemService->createOrderItem([
                'orderId' => $order->id,
                ...$itemData,
            ]);

            // Reload product to get fresh quantity after any previous decrements
            $product = $item->product()->lockForUpdate()->first();

            if ($product->is_limited_quantity == LimitedQuantity::LIMITED) {
                if ((float) $product->quantity < (float) $item->qty) {
                    // Throw so the controller's DB::transaction rolls back everything
                    throw new \App\Exceptions\InsufficientStockException(
                        $product->name,
                        (float) $product->quantity
                    );
                }
                // Decimal-safe decrement (supports 0.250, 0.500, etc.)
                $product->decrement('quantity', $item->qty);
            }

            // total = unit_price × decimal_qty
            $totalPrice += (float) $item->price * (float) $item->qty;
            $totalCost  += (float) $item->cost  * (float) $item->qty;
        }

        $order->update([
            'price'                => $totalPrice,
            'total_cost'           => $totalCost,
            'price_after_discount' => $this->applyDiscount(
                $totalPrice,
                $order->discount_type,
                (float) ($data['discount'] ?? 0)
            ),
        ]);

        return $order;
    }

    public function updateOrder(int $id, array $data): Order
    {
        // Validate quantities for create/update actions only
        $itemsToValidate = array_filter(
            $data['orderItems'] ?? [],
            fn($i) => in_array($i['actionStatus'] ?? '', ['create', 'update'])
        );

        $qtyErrors = $this->validateItemsQuantities(array_values($itemsToValidate));
        if (! empty($qtyErrors)) {
            throw new \InvalidArgumentException(
                json_encode(['quantityErrors' => $qtyErrors])
            );
        }

        $order = Order::where('id', $id)->lockForUpdate()->firstOrFail();
        $order->update([
            'discount'          => $data['discount'] ?? null,
            'discount_type'     => DiscountType::from($data['discountType'])->value,
            'client_phone_id'   => $data['clientPhoneId'] ?? null,
            'client_email_id'   => $data['clientEmailId'] ?? null,
            'client_address_id' => $data['clientAddressId'] ?? null,
            'client_id'         => $data['clientId'],
            'status'            => OrderStatus::from($data['status'])->value,
        ]);

        $totalPrice = 0.0;
        $totalCost  = 0.0;

        foreach ($data['orderItems'] as $itemData) {
            $action = $itemData['actionStatus'] ?? '';

            if ($action === 'update') {
                $oldQty = (float) $this->orderItemService->editOrderItem($itemData['orderItemId'])->qty;
                $item   = $this->orderItemService->updateOrderItem($itemData['orderItemId'], $itemData);

                if ($item->product->is_limited_quantity == LimitedQuantity::LIMITED) {
                    // Restore old qty first, then re-check with new qty
                    $product = $item->product()->lockForUpdate()->first();
                    $product->increment('quantity', $oldQty);
                    $product->refresh();

                    if ((float) $product->quantity < (float) $item->qty) {
                        throw new \App\Exceptions\InsufficientStockException(
                            $product->name,
                            (float) $product->quantity
                        );
                    }
                    $product->decrement('quantity', $item->qty);
                }

                $totalPrice += (float) $item->price * (float) $item->qty;
                $totalCost  += (float) $item->cost  * (float) $item->qty;
            }

            if ($action === 'delete') {
                $item = $this->orderItemService->editOrderItem($itemData['orderItemId']);
                if ($item->product->is_limited_quantity == LimitedQuantity::LIMITED) {
                    $item->product->increment('quantity', $item->qty);
                }
                $this->orderItemService->deleteOrderItem($itemData['orderItemId']);
            }

            if ($action === 'create') {
                $item = $this->orderItemService->createOrderItem([
                    'orderId' => $order->id,
                    ...$itemData,
                ]);

                $product = $item->product()->lockForUpdate()->first();

                if ($product->is_limited_quantity == LimitedQuantity::LIMITED) {
                    if ((float) $product->quantity < (float) $item->qty) {
                        throw new \App\Exceptions\InsufficientStockException(
                            $product->name,
                            (float) $product->quantity
                        );
                    }
                    $product->decrement('quantity', $item->qty);
                }

                $totalPrice += (float) $item->price * (float) $item->qty;
                $totalCost  += (float) $item->cost  * (float) $item->qty;
            }

            if ($action === '') {
                $item = $this->orderItemService->editOrderItem($itemData['orderItemId']);
                $totalPrice += (float) $item->price * (float) $item->qty;
                $totalCost  += (float) $item->cost  * (float) $item->qty;
            }
        }

        $order->update([
            'price'                => $totalPrice,
            'total_cost'           => $totalCost,
            'price_after_discount' => $this->applyDiscount(
                $totalPrice,
                $order->discount_type,
                (float) ($data['discount'] ?? 0)
            ),
        ]);

        return $order;
    }

    public function deleteOrder(int $id): void
    {
        $order = Order::find($id);
        if (! $order) {
            throw new ModelNotFoundException();
        }
        $order->delete();
    }

    // ─── Private ─────────────────────────────────────────────────────────────

    /**
     * Apply discount to total price. Returns price_after_discount.
     */
    private function applyDiscount(float $totalPrice, DiscountType $type, float $discount): float
    {
        return match ($type) {
            DiscountType::PERCENTAGE  => $totalPrice - ($totalPrice * ($discount / 100)),
            DiscountType::FIXCED      => $totalPrice - $discount,
            default                   => $totalPrice,
        };
    }
}

