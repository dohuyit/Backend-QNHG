<?php

namespace App\Services\Table;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Models\Table;
use App\Repositories\Table\TableRepositoryInterface;
use Carbon\Carbon;

class TableService
{
    protected TableRepositoryInterface $tableRepository;

    public function __construct(TableRepositoryInterface $tableRepository)
    {
        $this->tableRepository = $tableRepository;
    }

    public function getListTables(array $params): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int) $params['limit'] : 100000;

        $pagination = $this->tableRepository->getTableList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string) $item->id,
                'name' => $item->name ?? null,
                'description' => $item->description ?? null,
                'capacity' => $item->capacity ?? null,
                'status' => $item->status ?? null,
                'table_area' => $item->tableArea ? [
                    'id' => (string) $item->tableArea->id,
                    'name' => $item->tableArea->name,
                ] : null,
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

    public function getTableDetail(string $id): DataAggregate
    {
        $result = new DataAggregate;
        $table = $this->tableRepository->findById($id);
        if (!$table) {
            $result->setResultError(message: 'Bàn không tồn tại hoặc đã bị khóa');
            return $result;
        }

        $result->setResultSuccess(data: ['table' => $table]);
        return $result;
    }

    public function createTable(array $data): DataAggregate
    {
        $result = new DataAggregate;
        try {
            $table = $this->tableRepository->createTable($data);
            $result->setResultSuccess(
                data: ['table' => $table],
                message: 'Tạo bàn thành công'
            );
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                $result->setResultError(message: 'Tên bàn đã tồn tại');
            } else {
                $result->setResultError(message: 'Tạo bàn thất bại: ' . $e->getMessage());
            }
        }
        return $result;
    }

    public function updateTable(array $data, Table $table): DataAggregate
    {
        $result = new DataAggregate;
        $listDataUpdate = [
            'table_number' => $data['table_number'] ?? null,
            'description' => $data['description'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'status' => $data['status'] ?? 'active',
            'table_area_id' => $data['table_area_id'] ?? null
        ];

        $ok = $this->tableRepository->updateByConditions(
            conditions: ['id' => $table->id],
            updateData: $listDataUpdate
        );

        if (!$ok) {
            $result->setResultError(message: 'Cập nhật thất bại, vui lòng thử lại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Cập nhật bàn thành công!');
        return $result;
    }

    public function deleteTable($id): DataAggregate
    {
        $result = new DataAggregate;
        $table = $this->tableRepository->findById($id);
        if (!$table) {
            $result->setResultError(message: 'Bàn không tồn tại');
            return $result;
        }

        $ok = $this->tableRepository->deleteTable($id);
        if (!$ok) {
            $result->setResultError(message: 'Xóa bàn thất bại, vui lòng thử lại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Xóa bàn thành công!');
        return $result;
    }
        public function countByStatus(): array
    {
        $listStatus = ['available', 'occupied', 'reserved', 'cleaning', 'out_of_service'];
        $counts = [];

        foreach($listStatus as $status) {
            $counts[$status] = $this->tableRepository->countByConditions(['status' => $status]);
        }
        return $counts;
    }
}
