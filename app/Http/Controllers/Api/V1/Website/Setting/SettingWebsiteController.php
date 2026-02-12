<?php

namespace App\Http\Controllers\Api\V1\Website\Setting;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Setting\Setting;

class SettingWebsiteController extends Controller
{
    /**
     * Get available payment gateways for website
     */
    public function getAvailablePaymentGateways()
    {
        $gateways = [
            'paypal' => [
                'enabled' => (bool) Setting::get('payment.paypal.enabled', true),
                'name' => 'PayPal',
                'icon' => 'paypal-icon.png',
            ],
            'stripe' => [
                'enabled' => (bool) Setting::get('payment.stripe.enabled', true),
                'name' => 'Stripe',
                'icon' => 'stripe-icon.png',
            ],
            'cash_on_delivery' => [
                'enabled' => (bool) Setting::get('payment.cash_on_delivery.enabled', true),
                'name' => 'الدفع عند الاستلام',
                'icon' => 'cash-icon.png',
            ],
        ];

        // Filter only enabled gateways
        $enabledGateways = array_filter($gateways, fn($gateway) => $gateway['enabled']);

        return ApiResponse::success([
            'gateways' => $enabledGateways,
            'count' => count($enabledGateways),
        ]);
    }

    /**
     * Get public settings
     */
    public function getPublicSettings()
    {
        $settings = [
            'siteName' => Setting::get('site.name', 'ecommerce'),
            'maintenanceMode' => (bool) Setting::get('site.maintenance_mode', false),
            'minOrderAmount' => (int) Setting::get('order.min_amount', 0),
            'maxOrderAmount' => (int) Setting::get('order.max_amount', 100000),
        ];

        return ApiResponse::success($settings);
    }
}
