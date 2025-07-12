<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderItemCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderItemCreatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderItemCreated $event)
    {
        // Có thể lưu notification vào DB hoặc log lại
        // Log::info('Order item created', $event->orderItem);
    }
}
