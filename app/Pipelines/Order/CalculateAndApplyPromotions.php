<?php

namespace App\Pipelines\Order;

use App\DTOs\Order\OrderCheckoutData;
use App\Models\Coupon\Coupon;
use App\Models\Points\PointsSetting;
use Closure;
use Illuminate\Validation\ValidationException;

/**
 * Finalizes monetary state for the checkout aggregate.
 *
 * The Order model owns total calculation. This stage only maps optional
 * checkout-level promotions from the payload into order fields, then delegates
 * all arithmetic to `Order::recalculateTotals()` to keep pricing consistent
 * across guest, auth, dashboard, and later cart-edit flows.
 */
class CalculateAndApplyPromotions
{
    public function handle(OrderCheckoutData $data, Closure $next): mixed
    {
        $order = $data->order;

        if (! $order) {
            throw new \LogicException('Order must be created before promotions are calculated.');
        }

        $order->recalculateTotals();

        if (! empty($data->inputData['couponCode'])) {
            $this->applyCoupon($data);
        }

        if (! empty($data->inputData['points'])) {
            $this->applyPoints($data);
        }

        $data->order = $order->fresh(['items', 'items.product']);

        return $next($data);
    }

    private function applyCoupon(OrderCheckoutData $data): void
    {
        $order = $data->order;
        $coupon = Coupon::where('code', $data->inputData['couponCode'])->first();

        if (! $coupon || ! $coupon->isValid() || ! $coupon->canBeUsedByClient($data->clientId)) {
            throw ValidationException::withMessages([
                'couponCode' => __('crud.invalid_coupon'),
            ]);
        }

        if ((float) $order->price_after_discount < (float) $coupon->min_order_amount) {
            throw ValidationException::withMessages([
                'couponCode' => "Minimum order amount is {$coupon->min_order_amount}.",
            ]);
        }

        $order->update([
            'coupon_id' => $coupon->id,
            'coupon_discount' => $coupon->calculateDiscount((float) $order->price_after_discount),
        ]);

        $order->recalculateTotals();
    }

    private function applyPoints(OrderCheckoutData $data): void
    {
        $order = $data->order;
        $points = (int) $data->inputData['points'];
        $settings = PointsSetting::getSettings();

        if (! $settings->is_active || $points < $settings->min_points_to_redeem) {
            throw ValidationException::withMessages([
                'points' => 'Points redemption is not available for this checkout.',
            ]);
        }

        $discountAmount = min(
            (float) $order->price_after_discount,
            $points * (float) $settings->currency_per_point
        );

        $order->update([
            'points_redeemed' => $points,
            'points_discount_amount' => $discountAmount,
        ]);

        $order->recalculateTotals();
    }
}
