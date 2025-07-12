<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderItemDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderItemDeletedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderItemDeleted $event)
    {
        // Có thể lưu notification vào DB hoặc log lại
        // Log::info('Order item deleted', $event->orderItem);
    }
}
