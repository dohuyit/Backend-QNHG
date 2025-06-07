<?php

namespace App\Services\TableAreas;

use Exception;
use App\Common\ListAggregate;
use App\Common\DataAggregate;
use Illuminate\Support\Facades\Log;
use App\Repositories\TableAreas\TableAreaRepositoryInterface;
use App\Models\TableArea;
use App\Models\Branch;

class BranchTableAreaService
{
    protected TableAreaRepositoryInterface $tableAreaRepository;

    public function __construct(TableAreaRepositoryInterface $tableAreaRepository)
    {
        $this->tableAreaRepository = $tableAreaRepository;
    }

    public function getTableAreasByBranch(int $branchId, array $params): ListAggregate
    {
        try {
            $filter = $params;
            $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int) $params['limit'] : 10;

            $pagination = $this->tableAreaRepository->getTableAreasByBranch(
                branchId: $branchId,
                filter: $filter,
                limit: $limit
            );

            $data = [];
            foreach ($pagination->items() as $item) {
                $data[] = [
                    'id' => (string) $item->id,
                    'branch_id' => $item->branch_id,
                    'area_template_id' => $item->area_template_id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'description' => $item->description,
                    'status' => $item->status,
                    'capacity' => $item->capacity,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ];
            }

            $result = new ListAggregate($data);
            $result->setMeta(
                page: $pagination->currentPage(),
                perPage: $pagination->perPage(),
                total: $pagination->total()
            );
            return $result;
        } catch (Exception $e) {
            Log::error('Error in getTableAreasByBranch: ' . $e->getMessage());
            return new ListAggregate([]);
        }
    }

    public function createTableAreaForBranch(array $data): DataAggregate
    {
        $result = new DataAggregate();
        try {
            $tableArea = $this->tableAreaRepository->createTableAreaForBranch($data);
            if (!$tableArea) {
                $result->setResultError(message: 'Thêm khu vực bàn cho chi nhánh thất bại.');
                return $result;
            }
            $result->setResultSuccess(message: 'Thêm khu vực bàn cho chi nhánh thành công!');
            return $result;
        } catch (Exception $e) {
            Log::error('Error in createTableAreaForBranch: ' . $e->getMessage());
            $result->setResultError(message: 'Đã xảy ra lỗi khi thêm khu vực bàn cho chi nhánh.');
            return $result;
        }
    }

    public function createTableAreaForAllBranches(array $data): DataAggregate
    {
        $result = new DataAggregate();
        try {
            $createdAreas = $this->tableAreaRepository->createTableAreaForAllBranches($data);
            if (empty($createdAreas)) {
                $result->setResultError(message: 'Thêm khu vực bàn cho tất cả chi nhánh thất bại.');
                return $result;
            }
            $result->setResultSuccess(message: 'Thêm khu vực bàn cho tất cả chi nhánh thành công!');
            return $result;
        } catch (Exception $e) {
            Log::error('Error in createTableAreaForAllBranches: ' . $e->getMessage());
            $result->setResultError(message: 'Đã xảy ra lỗi khi thêm khu vực bàn cho tất cả chi nhánh.');
            return $result;
        }
    }

    public function updateTableAreaForBranch(array $data, TableArea $tableArea): DataAggregate
    {
        $result = new DataAggregate();
        try {
            $updated = $this->tableAreaRepository->updateTableAreaForBranch($data, $tableArea);
            if (!$updated) {
                $result->setResultError(message: 'Cập nhật khu vực bàn thất bại.');
                return $result;
            }
            $result->setResultSuccess(message: 'Cập nhật khu vực bàn thành công.');
            return $result;
        } catch (Exception $e) {
            Log::error('Error in updateTableAreaForBranch: ' . $e->getMessage());
            $result->setResultError(message: 'Đã xảy ra lỗi khi cập nhật khu vực bàn.');
            return $result;
        }
    }

    public function deleteTableAreaForBranch(TableArea $tableArea): DataAggregate
    {
        $result = new DataAggregate();
        try {
            $deleted = $this->tableAreaRepository->deleteTableAreaForBranch($tableArea);
            if (!$deleted) {
                $result->setResultError(message: 'Xóa khu vực bàn thất bại.');
                return $result;
            }
            $result->setResultSuccess(message: 'Xóa khu vực bàn thành công.');
            return $result;
        } catch (Exception $e) {
            Log::error('Error in deleteTableAreaForBranch: ' . $e->getMessage());
            $result->setResultError(message: 'Đã xảy ra lỗi khi xóa khu vực bàn.');
            return $result;
        }
    }

    public function getTableAreaDetail(TableArea $tableArea): DataAggregate
    {
        $result = new DataAggregate();
        try {
            $tableArea = $this->tableAreaRepository->getTableAreaDetail($tableArea);
            if (!$tableArea) {
                $result->setResultError(message: 'Không tìm thấy khu vực bàn.');
                return $result;
            }

            $result->setResultSuccess(
                data: $tableArea->toArray(),
                message: 'Lấy chi tiết khu vực bàn thành công.'
            );

            return $result;
        } catch (Exception $e) {
            Log::error('Error in getTableAreaDetail: ' . $e->getMessage());
            $result->setResultError(message: 'Đã xảy ra lỗi khi lấy chi tiết khu vực bàn.');
            return $result;
        }
    }
}
