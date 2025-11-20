<?php

namespace App\Services\Points;

use App\Models\Client\Client;
use App\Models\Order\Order;
use App\Models\Points\PointsSetting;
use App\Models\Points\PointsTransaction;
use App\Enums\Points\PointsTransactionType;
use Illuminate\Support\Facades\DB;

class PointsService
{
    /**
     * حساب النقاط المكتسبة من الطلب
     */
    public function calculateEarnedPoints(float $orderAmount): int
    {
        $settings = PointsSetting::getSettings();

        if (!$settings->is_active) {
            return 0;
        }
        return (int) floor($orderAmount * $settings->points_per_currency);
    }

    /**
     * إضافة نقاط للعميل عند إتمام الطلب
     */
    public function addPointsForOrder(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $points = $this->calculateEarnedPoints($order->price_after_discount);

            if ($points <= 0) {
                return;
            }

            // تحديث نقاط العميل
            $order->client->increment('points', $points);

            // حفظ سجل النقاط
            PointsTransaction::create([
                'client_id' => $order->client_id,
                'order_id' => $order->id,
                'points' => $points,
                'type' => PointsTransactionType::EARNED->value,
                'description' => "نقاط مكتسبة من الطلب رقم #{$order->id}",
                'expires_at' => now()->addYear(), // النقاط تنتهي بعد سنة
            ]);

            // تحديث الطلب
            $order->update(['points_earned' => $points]);
        });
    }

    /**
     * استخدام النقاط في الطلب
     */
    public function redeemPoints(Client $client, Order $order, int $pointsToRedeem): array
    {
        $settings = PointsSetting::getSettings();

        // التحقق من تفعيل النظام
        if (!$settings->is_active) {
            return [
                'success' => false,
                'message' => 'نظام النقاط غير مفعل حالياً'
            ];
        }

        // التحقق من الحد الأدنى للنقاط
        if ($pointsToRedeem < $settings->min_points_to_redeem) {
            return [
                'success' => false,
                'message' => "الحد الأدنى للنقاط المستخدمة هو {$settings->min_points_to_redeem} نقطة"
            ];
        }

        // التحقق من رصيد العميل
        if ($client->points < $pointsToRedeem) {
            return [
                'success' => false,
                'message' => "رصيدك من النقاط غير كافي. رصيدك الحالي: {$client->points} نقطة"
            ];
        }

        // حساب قيمة الخصم
        $discountAmount = $pointsToRedeem * $settings->currency_per_point;

        // التحقق من أن الخصم لا يتجاوز سعر الطلب
        if ($discountAmount > $order->price_after_discount) {
            $discountAmount = $order->price_after_discount;
            $pointsToRedeem = (int) ceil($discountAmount / $settings->currency_per_point);
        }

        DB::transaction(function () use ($client, $order, $pointsToRedeem, $discountAmount) {
            // خصم النقاط من العميل
            $client->decrement('points', $pointsToRedeem);

            // حفظ سجل النقاط
            PointsTransaction::create([
                'client_id' => $client->id,
                'order_id' => $order->id,
                'points' => -$pointsToRedeem,
                'type' => PointsTransactionType::REDEEMED->value,
                'description' => "استخدام نقاط في الطلب رقم #{$order->id}",
            ]);

            // تحديث الطلب
            $newPriceAfterDiscount = $order->price_after_discount - $discountAmount;

            $order->update([
                'points_redeemed' => $pointsToRedeem,
                'points_discount_amount' => $discountAmount,
                'price_after_discount' => $newPriceAfterDiscount,
            ]);
        });

        return [
            'success' => true,
            'message' => 'تم استخدام النقاط بنجاح',
            'points_redeemed' => $pointsToRedeem,
            'discount_amount' => $discountAmount,
            'new_total' => $order->fresh()->price_after_discount,
            'remaining_points' => $client->fresh()->points,
        ];
    }

    /**
     * إلغاء استخدام النقاط
     */
    public function cancelPointsRedemption(Order $order): void
    {
        if ($order->points_redeemed <= 0) {
            return;
        }

        DB::transaction(function () use ($order) {
            // إرجاع النقاط للعميل
            $order->client->increment('points', $order->points_redeemed);

            // حفظ سجل الإلغاء
            PointsTransaction::create([
                'client_id' => $order->client_id,
                'order_id' => $order->id,
                'points' => $order->points_redeemed,
                'type' => PointsTransactionType::REFUNDED->value,
                'description' => "إلغاء استخدام النقاط من الطلب رقم #{$order->id}",
            ]);

            // تحديث الطلب
            $order->update([
                'price_after_discount' => $order->price_after_discount + $order->points_discount_amount,
                'points_redeemed' => 0,
                'points_discount_amount' => 0,
            ]);
        });
    }

    /**
     * الحصول على رصيد النقاط المتاح
     */
    public function getAvailablePoints(Client $client): int
    {
        return $client->points;
    }

    /**
     * الحصول على سجل النقاط
     */
    public function getPointsHistory(Client $client, int $limit = 50)
    {
        return PointsTransaction::where('client_id', $client->id)
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * حساب قيمة النقاط بالعملة
     */
    public function calculatePointsValue(int $points): float
    {
        $settings = PointsSetting::getSettings();
        return $points * $settings->currency_per_point;
    }
}
