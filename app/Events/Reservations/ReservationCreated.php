<?php

namespace App\Events\Reservations;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reservation;

    public function __construct($reservation)
    {
        $this->reservation = $reservation;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('reservations'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'reservation.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->reservation['id'] ?? null,
            'customer_name' => $this->reservation['customer_name'],
            'customer_phone' => $this->reservation['customer_phone'],
            'customer_email' => $this->reservation['customer_email'],
            'reservation_date' => $this->reservation['reservation_date'],
            'reservation_time' => $this->reservation['reservation_time'],
            'number_of_guests' => $this->reservation['number_of_guests'],
            'status' => $this->reservation['status'],
            'created_at' => now()->toISOString(),
            'message' => 'Có đơn đặt bàn mới từ ' . $this->reservation['customer_name'],
        ];
    }
}
