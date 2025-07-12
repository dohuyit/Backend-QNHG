<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderUpdatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderUpdated $event)
    {
        // Có thể lưu notification vào DB hoặc log lại
        // Log::info('Order updated', $event->order);
    }
}
