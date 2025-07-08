<?php

namespace App\Services\TableArea;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Helpers\ConvertHelper;
use App\Models\TableArea;
use App\Repositories\TableArea\TableAreaRepositoryInterface;
use Carbon\Carbon;

class TableAreaService
{
    protected TableAreaRepositoryInterface $tableAreaRepository;

    public function __construct(TableAreaRepositoryInterface $tableAreaRepository)
    {
        $this->tableAreaRepository = $tableAreaRepository;
    }

    public function getListTableAreas(array $params): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int) $params['limit'] : 10;

        $pagination = $this->tableAreaRepository->getTableAreaList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string) $item->id,
                'name' => $item->name ?? null,
                'description' => $item->description ?? null,
                'capacity' => $item->capacity ?? null,
                'status' => $item->status ?? null,
                'created_at' => $item->created_at->toDateTimeString(),
                'updated_at' => $item->updated_at->toDateTimeString(),
            ];
        }

        $result = new ListAggregate($data);
        $result->setMeta(
            page: $pagination->currentPage(),
            perPage: $pagination->perPage(),
            total: $pagination->total()
        );

        return $result;
    }

    public function getTableAreaDetail(string $id): DataAggregate
    {
        $result = new DataAggregate;
        $tableArea = $this->tableAreaRepository->findById($id);
        if (!$tableArea) {
            $result->setResultError(message: 'Khu vực bàn không tồn tại hoặc đã bị khóa');
            return $result;
        }

        $result->setResultSuccess(data: ['table_area' => $tableArea]);
        return $result;
    }

    public function createTableArea(array $data): DataAggregate
    {
        $result = new DataAggregate;
        try {
            $tableArea = $this->tableAreaRepository->create($data);
            $result->setResultSuccess(
                data: ['table_area' => $tableArea],
                message: 'Tạo khu vực bàn thành công'
            );
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                $result->setResultError(message: 'Tên khu vực đã tồn tại');
            } else {
                $result->setResultError(message: 'Tạo khu vực bàn thất bại: ' . $e->getMessage());
            }
        }
        return $result;
    }

    public function updateTableArea(array $data, TableArea $tableArea): DataAggregate
    {
        $result = new DataAggregate;
        $listDataUpdate = [
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'status' => $data['status'] ?? 'active',
        ];

        $ok = $this->tableAreaRepository->updateByConditions(
            conditions: ['id' => $tableArea->id],
            updateData: $listDataUpdate
        );

        if (!$ok) {
            $result->setResultError(message: 'Cập nhật thất bại, vui lòng thử lại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Cập nhật khu vực bàn thành công!');
        return $result;
    }

    public function deleteTableArea($id): DataAggregate
    {
        $result = new DataAggregate;
        $tableArea = $this->tableAreaRepository->findById($id);

        if (!$tableArea) {
            $result->setResultError(message: 'Khu vực bàn không tồn tại');
            return $result;
        }

        $ok = $tableArea->delete();
        if (!$ok) {
            $result->setResultError(message: 'Xóa thất bại, vui lòng thử lại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Xóa khu vực bàn thành công!');
        return $result;
    }

    public function countByStatus(): array
    {
        $listStatus = ['active', 'inactive'];
        $counts = [];

        foreach ($listStatus as $status) {
            $counts[$status] = $this->tableAreaRepository->countByConditions(['status' => $status]);
        }

        return $counts;
    }
}
