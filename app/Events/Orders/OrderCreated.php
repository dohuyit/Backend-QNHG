<?php

namespace App\Events\Orders;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * Thông tin đơn hàng mới tạo
     * @var array
     */
    public $order;

    /**
     * Tạo event OrderCreated
     * @param array $order
     */
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
        return 'order.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->order['id'] ?? null,
            'order_code' => $this->order['order_code'] ?? null,
            'created_at' => $this->order['created_at'] ?? now()->toISOString(),
            'status' => $this->order['status'] ?? null,
            'customer_name' => $this->order['customer_name'] ?? null,
            // ... các trường khác nếu cần
        ];
    }
}
