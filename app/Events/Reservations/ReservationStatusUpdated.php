<?php

namespace App\Events\Reservations;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reservation;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new event instance.
     */
    public function __construct($reservation, $oldStatus, $newStatus)
    {
        $this->reservation = $reservation;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('reservations'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'reservation.status.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        $statusLabels = [
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'no_show' => 'Không đến',
            'seated' => 'Đã ngồi',
        ];

        return [
            'id' => $this->reservation['id'],
            'customer_name' => $this->reservation['customer_name'],
            'customer_phone' => $this->reservation['customer_phone'],
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'old_status_label' => $statusLabels[$this->oldStatus] ?? $this->oldStatus,
            'new_status_label' => $statusLabels[$this->newStatus] ?? $this->newStatus,
            'updated_at' => now()->toISOString(),
            'message' => 'Đơn đặt bàn của ' . $this->reservation['customer_name'] . ' đã được cập nhật từ ' . ($statusLabels[$this->oldStatus] ?? $this->oldStatus) . ' sang ' . ($statusLabels[$this->newStatus] ?? $this->newStatus),
        ];
    }
}