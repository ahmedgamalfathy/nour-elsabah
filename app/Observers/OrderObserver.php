<?php

namespace App\Observers;

use App\Enums\Order\OrderStatus;
use App\Models\Order\Order;
use App\Services\Inventory\InventoryService;
use App\Services\Points\PointsService;

class OrderObserver
{
    public function __construct(
        protected PointsService $pointsService,
        protected InventoryService $inventoryService,
    ) {}

    /**
     * Central order side-effect guard.
     */
    public function updated(Order $order): void
    {
        // الصح: نتحقق من الـ Original value مقابل القيمة الحالية لضمان رصد التغيير بدقة مع الـ Enums
        if ($order->getOriginal('status') === $order->status->value) {
            return;
        }

        // نقارن دائماً باستخدام ->value لضمان تطابق أنواع البيانات (الأرقام)
        if ($order->status->value === OrderStatus::CONFIRM->value) {
            $this->inventoryService->decrementStockIfNeeded($order);

            if ((int) $order->points_earned === 0) {
                $this->pointsService->addPointsForOrder($order->fresh());
            }
        }

        if ($order->status->value === OrderStatus::CANCELED->value) {
            $this->inventoryService->restoreStock($order);

            if ((int) $order->points_redeemed > 0) {
                $this->pointsService->cancelPointsRedemption($order->fresh());
            }
        }
    }

    /**
     * Deleting an order is an inventory-affecting operation.
     */
    public function deleting(Order $order): void
    {
        // شحن العلاقات مسبقاً قبل الحذف لضمان وجود الـ items في الذاكرة
        $order->loadMissing('items.product');

        $this->inventoryService->restoreStock($order);

        if ((int) $order->points_redeemed > 0) {
            $this->pointsService->cancelPointsRedemption($order);
        }
    }
}