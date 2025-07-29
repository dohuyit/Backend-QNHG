<?php

namespace App\Events\KitchenOrders;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KitchenOrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $kitchenOrder;

    public function __construct(array $kitchenOrder)
    {
        $this->kitchenOrder = $kitchenOrder;
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('kitchen-orders');
    }

    public function broadcastAs(): string
    {
        return 'kitchenorder.created';
    }
}
