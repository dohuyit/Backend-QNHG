<?php

namespace App\Listeners\Reservations;

use App\Events\Reservations\ReservationCreated;
use App\Events\Reservations\ReservationStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpdateReservationCounters implements ShouldQueue
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
    public function handle($event): void
    {
        if ($event instanceof ReservationCreated) {
            $this->handleNewReservation($event);
        } elseif ($event instanceof ReservationStatusUpdated) {
            $this->handleStatusUpdate($event);
        }
    }

    /**
     * Xử lý khi có đơn đặt bàn mới
     */
    private function handleNewReservation(ReservationCreated $event): void
    {
        // Cập nhật cache cho bộ đếm trạng thái
        $this->updateStatusCounters();

        // Cập nhật cache cho thống kê tổng quan
        $this->updateOverallStats();

        Log::info('Đã cập nhật bộ đếm sau khi tạo đơn đặt bàn mới');
    }

    /**
     * Xử lý khi cập nhật trạng thái đơn đặt bàn
     */
    private function handleStatusUpdate(ReservationStatusUpdated $event): void
    {
        // Cập nhật cache cho bộ đếm trạng thái
        $this->updateStatusCounters();

        // Cập nhật cache cho thống kê tổng quan
        $this->updateOverallStats();

        Log::info('Đã cập nhật bộ đếm sau khi thay đổi trạng thái đơn đặt bàn', [
            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus,
        ]);
    }

    /**
     * Cập nhật bộ đếm theo trạng thái
     */
    private function updateStatusCounters(): void
    {
        $statuses = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show', 'seated'];

        foreach ($statuses as $status) {
            // Xóa cache cũ để force refresh
            Cache::forget("reservation_count_{$status}");
        }

        // Cache sẽ được tạo lại khi có request mới
        Cache::forget('reservation_status_counters');
    }

    /**
     * Cập nhật thống kê tổng quan
     */
    private function updateOverallStats(): void
    {
        // Xóa cache thống kê tổng quan
        Cache::forget('reservation_overall_stats');
        Cache::forget('today_reservations_count');
        Cache::forget('pending_reservations_count');
    }

    /**
     * Handle a job failure.
     */
    public function failed($event, \Throwable $exception): void
    {
        Log::error('Lỗi cập nhật bộ đếm đơn đặt bàn', [
            'event' => get_class($event),
            'error' => $exception->getMessage(),
        ]);
    }
}