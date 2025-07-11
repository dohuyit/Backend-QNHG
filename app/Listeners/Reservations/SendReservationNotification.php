<?php

namespace App\Listeners\Reservations;

use App\Events\Reservations\ReservationCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use App\Models\Notification;
use App\Models\User;

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
        // Lấy thông tin đơn đặt bàn
        $reservation = $event->reservation;

        // Lấy danh sách admin (hoặc user nhận thông báo)
        $admins = User::whereHas('roles', function ($q) {
            $q->where('role_name', 'Admin'); // Phân biệt hoa thường!
        })->get();

        foreach ($admins as $admin) {
            Notification::create([
                'title' => 'Đơn đặt bàn mới',
                'message' => "Khách hàng {$reservation['customer_name']} vừa đặt bàn lúc {$reservation['reservation_time']} ngày {$reservation['reservation_date']}.",
                'type' => 'reservation',
                'reservation_id' => $reservation['id'] ?? null,
                'receiver_id' => $admin->id,
            ]);
        }
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
