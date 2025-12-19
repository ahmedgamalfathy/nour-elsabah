<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreatedOrderEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public $order)
    {
       $this->order = $order;
    }

    /**
     * Get the channels the event should broadcast on.
     *تحديد القناه اللي هبعت عليها
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            // new PrivateChannel('send-created-order-to-dashboard'),
            new Channel('send-created-order-to-dashboard'),
        ];
    }
    /**
     * اسم الـ Event
     */
    public function broadcastAs(): string
    {
        return 'create-order-event';
    }
    public function broadcastWith()
    {
        return [
            'orderId' => $this->order->id,
            'orderNumber' => $this->order->number,
            'clientName' => $this->order->client->name??"",
            'status' => $this->order->status,
            'price' => $this->order->price,
            'totalOrderCost' => $this->order->total_cost,
            'priceAfterDiscount' => $this->order->price_after_discount,
            'date' =>$this->order->created_at,
        ];
    }
}
