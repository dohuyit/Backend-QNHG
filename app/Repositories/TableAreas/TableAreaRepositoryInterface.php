<?php

namespace App\Repositories\TableAreas;

use App\Models\TableArea;
use Illuminate\Pagination\LengthAwarePaginator;

interface TableAreaRepositoryInterface
{
    /**
     * Lấy danh sách khu vực bàn theo chi nhánh
     *
     * @param int $branchId
     * @param array $filter
     * @param int $limit
     * @return LengthAwarePaginator
     */
    public function getTableAreasByBranch(int $branchId, array $filter = [], int $limit = 10): LengthAwarePaginator;

    /**
     * Tạo khu vực bàn cho một chi nhánh
     *
     * @param array $data
     * @return TableArea|null
     */
    public function createTableAreaForBranch(array $data): ?TableArea;

    /**
     * Tạo khu vực bàn cho tất cả chi nhánh
     *
     * @param array $data
     * @return array
     */
    public function createTableAreaForAllBranches(array $data): array;

    /**
     * Cập nhật khu vực bàn cho chi nhánh
     *
     * @param array $data
     * @param TableArea $tableArea
     * @return bool
     */
    public function updateTableAreaForBranch(array $data, TableArea $tableArea): bool;

    /**
     * Xóa khu vực bàn cho chi nhánh
     *
     * @param TableArea $tableArea
     * @return bool
     */
    public function deleteTableAreaForBranch(TableArea $tableArea): bool;

    /**
     * Lấy chi tiết khu vực bàn
     *
     * @param TableArea $tableArea
     * @return TableArea|null
     */
    public function getTableAreaDetail(TableArea $tableArea): ?TableArea;
}
