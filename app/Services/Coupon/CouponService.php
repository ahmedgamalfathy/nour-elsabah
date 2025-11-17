<?php

namespace App\Services\Coupon;

use App\Models\Coupon\Coupon;
use App\Models\Coupon\CouponUsage;
use Illuminate\Support\Facades\DB;

class CouponService
{
    public function validateCoupon(string $code, $clientId, float $orderAmount): array
    {
        $coupon = Coupon::where('code', $code)->firstOrFail();

        if (!$coupon->isValid()) {
            return [
                'valid' => false,
                'message' => 'الكوبون غير صالح أو منتهي الصلاحية'
            ];
        }

        if (!$coupon->canBeUsedByClient($clientId)) {
            return [
                'valid' => false,
                'message' => 'لقد استخدمت هذا الكوبون من قبل'
            ];
        }

        if ($orderAmount < $coupon->min_order_amount) {
            return [
                'valid' => false,
                'message' => "الحد الأدنى للطلب {$coupon->min_order_amount} جنيه"
            ];
        }

        $discount = $coupon->calculateDiscount($orderAmount);

        return [
            'valid' => true,
            'coupon' => $coupon,
            'discount' => $discount,
            'message' => 'تم تطبيق الكوبون بنجاح'
        ];
    }

    /**
     * Apply coupon to order
     */
    public function applyCoupon($coupon, $order, $clientId, $discountAmount)
    {
        DB::transaction(function () use ($coupon, $order, $clientId, $discountAmount) {
            // Update coupon used count
            $coupon->increment('used_count');

            // Record usage
            CouponUsage::create([
                'coupon_id' => $coupon->id,
                'client_id' => $clientId,
                'order_id' => $order->id,
                'discount_amount' => $discountAmount,
                'created_at' => now(),
            ]);

            // Update order
            $order->update([
                'coupon_id' => $coupon->id,
                'coupon_discount' => $discountAmount,
            ]);
        });
    }
}
