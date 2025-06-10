<?php

namespace App\Services\TableArea;

use App\Repositories\TableArea\TableAreaRepositoryInterface;
use App\Common\ListAggregate;
use App\Helpers\ResponseHelper;
use App\Helpers\ErrorHelper;

class TableAreaService
{
    protected $tableAreaRepository;

    public function __construct(TableAreaRepositoryInterface $tableAreaRepository)
    {
        $this->tableAreaRepository = $tableAreaRepository;
    }

    public function getListTableAreas($params)
    {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 10;

        $query = $this->tableAreaRepository->getList($params);
        $total = $query->total();
        $items = $query->items();

        $listAggregate = new ListAggregate($items);
        $listAggregate->setMeta($page, $limit, $total);

        return ResponseHelper::responseSuccess(
            $listAggregate->getResult(),
            'Lấy danh sách khu vực bàn thành công'
        );
    }

    public function getTableAreaDetail($id)
    {
        try {
            $tableArea = $this->tableAreaRepository->findById($id);
            if (!$tableArea) {
                return ResponseHelper::responseFail(
                    ErrorHelper::FAILED,
                    [],
                    'Khu vực bàn không tồn tại',
                    404
                );
            }

            return ResponseHelper::responseSuccess(
                $tableArea->toArray(),
                'Lấy thông tin khu vực bàn thành công'
            );
        } catch (\Exception $e) {
            return ResponseHelper::responseFail(
                ErrorHelper::FAILED,
                [],
                'Lấy thông tin khu vực bàn thất bại: ' . $e->getMessage(),
                500
            );
        }
    }

    public function createTableArea(array $data)
    {
        try {
            $tableArea = $this->tableAreaRepository->create($data);
            return ResponseHelper::responseSuccess(
                $tableArea->toArray(),
                'Tạo khu vực thành công',
                201
            );
        } catch (\Illuminate\Database\QueryException $e) {
            // Kiểm tra mã lỗi Duplicate entry
            if ($e->getCode() == 23000) {
                return ResponseHelper::responseFail(
                    ErrorHelper::FAILED,
                    [],
                    'Tên khu vực đã tồn tại',
                    409
                );
            }

            return ResponseHelper::responseFail(
                ErrorHelper::FAILED,
                [],
                'Tạo khu vực thất bại: ' . $e->getMessage(),
                500
            );
        }
    }

    public function updateTableArea($id, array $data)
    {
        try {
            $tableArea = $this->tableAreaRepository->findById($id);
            if (!$tableArea) {
                return ResponseHelper::responseFail(
                    ErrorHelper::FAILED,
                    [],
                    'Khu vực bàn không tồn tại',
                    404
                );
            }

            $this->tableAreaRepository->update($id, $data);
            return ResponseHelper::responseSuccess(
                null,
                'Cập nhật khu vực bàn thành công'
            );
        } catch (\Exception $e) {
            return ResponseHelper::responseFail(
                ErrorHelper::FAILED,
                [],
                'Cập nhật khu vực bàn thất bại: ' . $e->getMessage()
            );
        }
    }

    public function deleteTableArea($id)
    {
        try {
            $tableArea = $this->tableAreaRepository->findById($id);
            if (!$tableArea) {
                return ResponseHelper::responseFail(
                    ErrorHelper::FAILED,
                    [],
                    'Khu vực bàn không tồn tại',
                    404
                );
            }

            $this->tableAreaRepository->delete($id);
            return ResponseHelper::responseSuccess(
                null,
                'Xóa khu vực bàn thành công'
            );
        } catch (\Exception $e) {
            return ResponseHelper::responseFail(
                ErrorHelper::FAILED,
                [],
                'Xóa khu vực bàn thất bại: ' . $e->getMessage()
            );
        }
    }


}
