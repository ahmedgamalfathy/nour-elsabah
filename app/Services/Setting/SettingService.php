<?php

namespace App\Services\Setting;

use App\Models\Setting\Setting;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SettingService
{
    /**
     * Get all settings
     */
    public function getAllSettings()
    {
        return Setting::orderBy('group')->orderBy('key')->get();
    }

    /**
     * Get settings by group
     */
    public function getSettingsByGroup(string $group)
    {
        return Setting::where('group', $group)->orderBy('key')->get();
    }

    /**
     * Get single setting
     */
    public function getSetting(int $id)
    {
        $setting = Setting::find($id);
        
        if (!$setting) {
            throw new ModelNotFoundException();
        }
        
        return $setting;
    }

    /**
     * Update setting
     */
    public function updateSetting(int $id, array $data)
    {
        $setting = $this->getSetting($id);
        
        $setting->update([
            'value' => $data['value'],
            'description' => $data['description'] ?? $setting->description,
        ]);

        Setting::clearCache();
        
        return $setting;
    }

    /**
     * Bulk update settings
     */
    public function bulkUpdateSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }
        
        Setting::clearCache();
    }

    /**
     * Check if payment gateway is enabled
     */
    public function isPaymentGatewayEnabled(string $gateway): bool
    {
        $key = "payment.{$gateway}.enabled";
        return (bool) Setting::get($key, false);
    }

    /**
     * Get available payment gateways
     */
    public function getAvailablePaymentGateways(): array
    {
        $gateways = [
            'paypal' => Setting::get('payment.paypal.enabled', true),
            'stripe' => Setting::get('payment.stripe.enabled', true),
            'cash_on_delivery' => Setting::get('payment.cash_on_delivery.enabled', true),
        ];

        return array_filter($gateways, fn($enabled) => $enabled);
    }
}
