<?php
namespace App\Repositories\Reservations;

use App\Models\Reservation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReservationRepositoryInterface
{
    public function updateByConditions(array $conditions, array $updateData): bool;
    public function createData(array $data): bool;
    public function getByConditions(array $conditions): ?Reservation;
    public function getReservationList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function getTrashReservationList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function findOnlyTrashedById($id): ?Reservation;
}