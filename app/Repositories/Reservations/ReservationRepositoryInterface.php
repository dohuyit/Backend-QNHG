<?php
namespace App\Repositories\Reservations;

use App\Models\Reservation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Services\ServiceResult;
use Illuminate\Support\Collection;

interface ReservationRepositoryInterface
{
    public function updateByConditions(array $conditions, array $updateData): bool;
    public function createData(array $data): ?Reservation;
    public function getByConditions(array $conditions): ?Reservation;
    public function getReservationList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function getTrashReservationList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function findOnlyTrashedById($id): ?Reservation;
    public function confirmReservation(int $id, int $userId): bool;
    public function countByConditions(array $conditions): int;
    /**
     * Lấy lịch sử thay đổi của 1 đơn đặt bàn
     */
    public function getReservationChangeLogs(int $reservationId): Collection;

    /**
     * Tạo log thay đổi đơn đặt bàn
     */
    public function createReservationChangeLog(array $data);

    public function getReservationStatusStats(): array;
    public function getReservationTimeStats(?string $startDate, ?string $endDate, string $groupBy = 'day'): array;
}
