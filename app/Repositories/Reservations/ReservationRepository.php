<?php
namespace App\Repositories\Reservations;

use App\Models\Reservation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ReservationRepository implements ReservationRepositoryInterface
{
	 public function updateByConditions(array $conditions, array $updateData): bool
    {
        $result = Reservation::where($conditions)->update($updateData);
        return (bool)$result;
    }

    public function createData(array $data): ?Reservation
    {
        $result = Reservation::create($data);
        return $result;
    }
    public function getByConditions(array $conditions): ?Reservation
    {
        $result = Reservation::where($conditions)->first();
        return $result;
    }

    public function getReservationList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = Reservation::query();

        if (!empty($filter)) {
            $query = $this->filterReservationList($query, $filter);
        }

        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }

    private function filterReservationList(Builder $query, array $filter = []): Builder
    {
        if ($val = $filter['name'] ?? null) {
            $query->where('name', 'like', '%' . $val . '%');
        }

        if ($val = $filter['status'] ?? null) {
            $query->where('status', $val);
        }

        return $query;
    }

    public function findOnlyTrashedById($id): ?Reservation
    {
        $result = Reservation::onlyTrashed()->where('id', $id)->firstOrFail();
        return $result;
    }

    public function getTrashReservationList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = Reservation::onlyTrashed();

        if (!empty($filter)) {
            $query = $this->filterReservationList($query, $filter);
        }

        return $query->orderBy('deleted_at', 'desc')->paginate($limit);
    }

    public function confirmReservation(int $id, int $userId): bool
    {
        return $this->updateByConditions(
            ['id' => $id],
            [
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'user_id' => $userId,
            ]
        );
    }

}
