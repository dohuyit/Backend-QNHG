<?php

namespace App\Listeners\Tables;

use App\Events\Tables\TableStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTableStatusUpdatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(TableStatusUpdated $event)
    {
        // Có thể lưu notification vào DB hoặc log lại
        // Log::info('Table status updated', $event->table);
    }
}
