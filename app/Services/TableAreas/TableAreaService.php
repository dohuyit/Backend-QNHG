<?php

namespace App\Services\TableAreas;

use Exception;
use App\Common\ListAggregate;
use App\Common\DataAggregate;
use Illuminate\Support\Facades\Log;
use App\Repositories\TableAreas\TableAreaRepositoryInterface;
use Illuminate\Support\Str;
use App\Models\TableArea;

class TableAreaService
{
    protected TableAreaRepositoryInterface $tableAreaRepository;

    public function __construct(TableAreaRepositoryInterface $tableAreaRepository)
    {
        $this->tableAreaRepository = $tableAreaRepository;
    }

    public function getListTableAreas(array $params): ListAggregate
    {
        try {
            $limit = ! empty($params['limit']) && $params['limit'] > 0 ? (int) $params['limit'] : 10;
            $pagination = $this->tableAreaRepository->getListTableAreas($params);

            $data = [];
            foreach ($pagination->items() as $item) {
                $data[] = [
                    'id' => (string) $item->id,
                    'branch_id' => $item->branch_id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'description' => $item->description,
                    'capacity' => $item->capacity,
                    'status' => $item->status,
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
            Log::error('Error in getListTableAreas: ' . $e->getMessage());

            return new ListAggregate([]); // Return empty ListAggregate on error
        }
    }
    public function createTableArea(array $data): DataAggregate
    {
        $result = new DataAggregate();

        try {
            $slug = Str::slug($data['name'] ?? '');

            $listDataCreate = [
                'branch_id'         => $data['branch_id'],
                'area_template_id'  => $data['area_template_id'],
                'name'              => $data['name'],
                'slug'              => $slug,
                'description'       => $data['description'] ?? null,
                'status'            => $data['status'] ?? 'active',
                'capacity'          => $data['capacity'] ?? 0,
            ];

            $tableArea = $this->tableAreaRepository->createTableArea($listDataCreate);


            if (! $tableArea) {
                $result->setResultError(message: 'Thêm khu vực bàn thất bại, vui lòng thử lại!');
                return $result;
            }

            $result->setResultSuccess(message: 'Tạo khu vực bàn thành công!');
            return $result;
        } catch (\Exception $e) {
            Log::error('Error in createTableArea: ' . $e->getMessage());
            $result->setResultError(message: 'Đã xảy ra lỗi khi tạo khu vực bàn.');
            return $result;
        }
    }


    public function getTableAreaDetail(string $slug): DataAggregate
    {
        $result = new DataAggregate(); // Initialize DataAggregate
        try {
            $tableArea = $this->tableAreaRepository->getTableAreaDetail($slug);
            if (! $tableArea) {
                $result->setResultError(message: 'Table area not found.', code: 404); // Use setResultError with code
                return $result;
            }

            $result->setResultSuccess(data: ['table_area' => $tableArea]); // Set data on success
            return $result;
        } catch (Exception $e) {
            Log::error('Error in getTableAreaDetail: ' . $e->getMessage());

            $result->setResultError(message: 'Failed to get table area detail.');
            return $result; // Return result with error
        }
    }

    public function updateTableArea(array $data, $tableArea): DataAggregate
    {
        $result = new DataAggregate(); // Initialize DataAggregate
        try {
            $updated = $this->tableAreaRepository->updateTableArea($data, $tableArea);
            if (! $updated) {
                $result->setResultError(message: 'Failed to update table area.');
                return $result;
            }

            $result->setResultSuccess(message: 'Table area updated successfully.'); // Set success message
            return $result;
        } catch (Exception $e) {
            Log::error('Error in updateTableArea: ' . $e->getMessage());

            $result->setResultError(message: 'Failed to update table area.');
            return $result; // Return result with error
        }
    }

    // Removed soft delete, force delete, restore, and list trashed methods
}
