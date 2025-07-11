<?php

namespace App\Events\Orders;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class OrderItemCreated implements ShouldBroadcast
{
    use SerializesModels;

    public $orderItem;

    public function __construct(array $orderItem)
    {
        $this->orderItem = $orderItem;
    }

    public function broadcastOn()
    {
        return new Channel('kitchen-orders');
    }

    public function broadcastAs()
    {
        return 'orderitem.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->orderItem['id'] ?? null,
            'order_id' => $this->orderItem['order_id'] ?? null,
            'item_name' => $this->orderItem['item_name'] ?? null,
            'status' => $this->orderItem['status'] ?? null,
            'quantity' => $this->orderItem['quantity'] ?? null,
            'notes' => $this->orderItem['notes'] ?? null,
            'updated_at' => $this->orderItem['updated_at'] ?? now()->toISOString(),
            // ... các trường khác nếu cần
        ];
    }
}
