<?php

namespace App\Observers;

use App\Models\Order\Order;
use App\Enums\Order\OrderStatus;
use App\Services\Points\PointsService;

class OrderObserver
{
    protected $pointsService;

    public function __construct(PointsService $pointsService)
    {
        $this->pointsService = $pointsService;
    }

    /**
     * يتم تنفيذه عند تحديث الطلب
     */
// OrderObserver.php
public function updated(Order $order)
{
    // إضافة نقاط عند التأكيد
    if ($order->status == OrderStatus::CONFIRM->value &&
        $order->points_earned == 0) {
        $this->pointsService->addPointsForOrder($order);
    }

    // إلغاء النقاط عند الإلغاء
    if ($order->status == OrderStatus::CANCELED->value &&
        $order->points_redeemed > 0) {
        $this->pointsService->cancelPointsRedemption($order);
    }
}

    /**
     * (اختياري) يتم تنفيذه عند إنشاء الطلب
     */
    public function created(Order $order)
    {
        //
    }

    /**
     * (اختياري) يتم تنفيذه عند حذف الطلب
     */
    public function deleted(Order $order)
    {
        //
    }
}
