<?php

namespace Database\Seeders;

use App\Enums\Order\DiscountType;
use App\Enums\Order\OrderStatus;
use App\Models\Client\Client;
use App\Models\Product\Product;
use App\Services\Order\OrderService;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function run(): void
    {
        // Fetch clients with required relationships
        $clients = Client::has('phones')->has('emails')->has('addresses')->get();
        // Fetch active products
        $products = Product::where(function ($query) {
            $query->where('is_limited_quantity', false)
                  ->orWhere('quantity', '>', 0);
        })->get();

        if ($clients->isEmpty() || $products->isEmpty()) {
            echo "No valid clients or products found in the database.\n";
            return;
        }

        foreach ($clients as $client) {
            $clientPhone = $client->phones()->inRandomOrder()->first();
            $clientEmail = $client->emails()->inRandomOrder()->first();
            $clientAddress = $client->addresses()->inRandomOrder()->first();

            // Skip if any client detail is missing
            if (!$clientPhone || !$clientEmail || !$clientAddress) {
                continue;
            }

            $orderItems = $this->generateOrderItems($products);

            // Skip if no order items were selected
            if (empty($orderItems)) {
                continue;
            }

            $orderData = $this->prepareOrderData($client, $clientPhone, $clientEmail, $clientAddress, $orderItems);

            // Create the order using the OrderService
            $order = $this->orderService->createOrder($orderData);

            // Output the created order details
            if (isset($order->id)) {
                echo "✅ Created order #{$order->id} | Total: {$order->price}, After Discount: {$order->price_after_discount}\n";
            } else {
                echo "⚠️ Order creation failed for client #{$client->id}.\n";
            }
        }
    }

    private function generateOrderItems($products)
    {
        $orderItems = [];

        // Select random products and generate order items
        $selectedProducts = $products->shuffle()->take(3);
        foreach ($selectedProducts as $product) {
            $qty = rand(1, 5);

            // Check the product quantity
            if ($product->is_limited_quantity && $product->quantity < $qty) {
                continue; // Skip products with insufficient quantity
            }

            $orderItems[] = [
                'productId' => $product->id,
                'qty' => $qty
            ];
        }

        return $orderItems;
    }

    private function prepareOrderData($client, $clientPhone, $clientEmail, $clientAddress, $orderItems)
    {
        $discountType = fake()->randomElement([
            DiscountType::FIXCED->value,
            DiscountType::PERCENTAGE->value,
            DiscountType::NO_DISCOUNT->value,
        ]);

        $discount = $discountType !== DiscountType::NO_DISCOUNT->value ? rand(5, 25) : 0;

        return [
            'clientId' => $client->id,
            'clientPhoneId' => $clientPhone->id,
            'clientEmailId' => $clientEmail->id,
            'clientAddressId' => $clientAddress->id,
            'discountType' => $discountType,
            'discount' => $discount,
            'status' => OrderStatus::DRAFT->value,
            'orderItems' => $orderItems,
        ];
    }
}
