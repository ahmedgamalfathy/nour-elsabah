<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        
        $settings = [
            // Payment Gateway Settings
            [
                'key' => 'payment.paypal.enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'payment',
                'description' => 'تفعيل/إيقاف بوابة الدفع PayPal',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'payment.stripe.enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'payment',
                'description' => 'تفعيل/إيقاف بوابة الدفع Stripe',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'payment.cash_on_delivery.enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'payment',
                'description' => 'تفعيل/إيقاف الدفع عند الاستلام',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            
            // General Settings
            [
                'key' => 'site.name',
                'value' => 'ecommerce',
                'type' => 'string',
                'group' => 'general',
                'description' => 'اسم الموقع',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'site.maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'general',
                'description' => 'وضع الصيانة',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'order.min_amount',
                'value' => '0',
                'type' => 'integer',
                'group' => 'order',
                'description' => 'الحد الأدنى لقيمة الطلب',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'order.max_amount',
                'value' => '100000',
                'type' => 'integer',
                'group' => 'order',
                'description' => 'الحد الأقصى لقيمة الطلب',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('settings')->insert($settings);
    }
}
