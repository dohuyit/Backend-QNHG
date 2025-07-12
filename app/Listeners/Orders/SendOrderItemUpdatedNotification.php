<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderItemUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderItemUpdatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderItemUpdated $event)
    {
        // Có thể lưu notification vào DB hoặc log lại
        // Log::info('Order item updated', $event->orderItem);
    }
}
