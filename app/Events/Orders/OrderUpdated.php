<?php

namespace App\Events\Orders;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class OrderUpdated implements ShouldBroadcast
{
    use SerializesModels;

    public $order;

    public function __construct(array $order)
    {
        $this->order = $order;
    }

    public function broadcastOn()
    {
        return new Channel('orders');
    }

    public function broadcastAs()
    {
        return 'order.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->order['id'] ?? null,
            'order_code' => $this->order['order_code'] ?? null,
            'status' => $this->order['status'] ?? null,
            'updated_at' => $this->order['updated_at'] ?? now()->toISOString(),
            // ... các trường khác nếu cần
        ];
    }
}
