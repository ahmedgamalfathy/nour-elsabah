<?php

namespace App\DTOs\Order;

use App\Models\Order\Order;

/**
 * Mutable checkout command passed through the order pipeline.
 *
 * The DTO keeps transport data away from domain models while allowing each
 * pipeline stage to add exactly the state it owns. Controllers submit raw
 * request data once; stages resolve the client, create records, and attach the
 * final Order without re-parsing request payloads in multiple places.
 */
class OrderCheckoutData
{
    public ?Order $order = null;

    public ?int $clientPhoneId = null;

    public ?int $clientEmailId = null;

    public ?int $clientAddressId = null;

    public function __construct(
        public ?int $clientId,
        public array $inputData,
    ) {}

    public function isGuestCheckout(): bool
    {
        return $this->clientId === null;
    }

    public function items(): array
    {
        return $this->inputData['orderItems'] ?? [];
    }
}
