<?php

namespace App\Services\Order;

use App\DTOs\Order\OrderCheckoutData;
use App\Models\Order\Order;
use App\Pipelines\Order\CalculateAndApplyPromotions;
use App\Pipelines\Order\CreateOrderRecords;
use App\Pipelines\Order\ResolveClient;
use App\Pipelines\Order\ValidateStockAndSteps;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;

/**
 * Application service for website checkout creation.
 *
 * The service owns transaction safety and pipeline orchestration. Every stage
 * runs inside one database transaction, so guest client creation, order rows,
 * order lines, and promotion fields either commit together or roll back
 * together. This prevents partial guest accounts and orphaned cart records
 * when validation, stock checks, or promotion logic fails mid-checkout.
 */
class CheckoutService
{
    public function __construct(
        protected Pipeline $pipeline,
    ) {}

    public function execute(array $inputData, ?int $clientId = null): Order
    {
        return DB::transaction(function () use ($inputData, $clientId): Order {
            $checkoutData = new OrderCheckoutData($clientId, $inputData);

            /** @var OrderCheckoutData $result */
            $result = $this->pipeline
                ->send($checkoutData)
                ->through($this->stages())
                ->thenReturn();

            if (! $result->order) {
                throw new \LogicException('Checkout pipeline completed without creating an order.');
            }

            return $result->order;
        });
    }

    /**
     * @return array<int, class-string>
     */
    private function stages(): array
    {
        return [
            ResolveClient::class,
            ValidateStockAndSteps::class,
            CreateOrderRecords::class,
            CalculateAndApplyPromotions::class,
        ];
    }
}
