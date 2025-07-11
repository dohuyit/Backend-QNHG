<?php

namespace App\Listeners\Reservations;

use App\Events\Reservations\ReservationCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendReservationNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ReservationCreated $event): void
    {
        // Log thông báo đơn đặt bàn mới
        Log::info('Đơn đặt bàn mới được tạo', [
            'customer_name' => $event->reservation['customer_name'],
            'customer_phone' => $event->reservation['customer_phone'],
            'reservation_date' => $event->reservation['reservation_date'],
            'reservation_time' => $event->reservation['reservation_time'],
            'number_of_guests' => $event->reservation['number_of_guests'],
        ]);

        // Có thể thêm logic gửi email, SMS, push notification ở đây
        // Ví dụ: gửi email thông báo cho admin
        // Mail::to('admin@example.com')->send(new NewReservationNotification($event->reservation));
    }

    /**
     * Handle a job failure.
     */
    public function failed(ReservationCreated $event, \Throwable $exception): void
    {
        Log::error('Lỗi xử lý thông báo đơn đặt bàn mới', [
            'reservation' => $event->reservation,
            'error' => $exception->getMessage(),
        ]);
    }
}
