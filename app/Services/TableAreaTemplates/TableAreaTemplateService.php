<?php

namespace App\Services\TableAreaTemplates;

use Exception;
use App\Common\ListAggregate;
use App\Common\DataAggregate;
use Illuminate\Support\Facades\Log;
use App\Repositories\TableAreaTemplates\TableAreaTemplateRepositoryInterface;
use App\Models\AreaTemplate;
use Illuminate\Support\Str;

class TableAreaTemplateService
{
    protected TableAreaTemplateRepositoryInterface $tableAreaTemplateRepository;

    public function __construct(TableAreaTemplateRepositoryInterface $tableAreaTemplateRepository)
    {
        $this->tableAreaTemplateRepository = $tableAreaTemplateRepository;
    }

    public function getListTableAreas(array $params): ListAggregate
    {
        try {
            $filter = $params;
            $limit = ! empty($params['limit']) && $params['limit'] > 0 ? (int) $params['limit'] : 10;
            $pagination = $this->tableAreaTemplateRepository->getListTableAreas(filter: $filter, limit: $limit);
            $data = [];
            foreach ($pagination->items() as $item) {
                $data[] = [
                    'id' => (string) $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'description' => $item->description,
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
            return new ListAggregate([]);
        }
    }

    public function createTableArea(array $data): DataAggregate
    {
        $result = new DataAggregate();
        $slug = Str::slug($data['name'] ?? '');
        $listDataCreate = [
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'],
        ];
        try {
            $areaTemplate = $this->tableAreaTemplateRepository->createTableArea($listDataCreate);
            if (!$areaTemplate) {
                $result->setResultError(message: 'Thêm mẫu khu vực thất bại, vui lòng thử lại!');
                return $result;
            }
            $result->setResultSuccess(message: 'Tạo mẫu khu vực thành công!');
            return $result;
        } catch (\Exception $e) {
            Log::error('Error in createTableArea: ' . $e->getMessage());
            $result->setResultError(message: 'Đã xảy ra lỗi khi tạo mẫu khu vực.');
            return $result;
        }
    }

    public function getTableAreaDetail(string $slug): DataAggregate
    {
        $result = new DataAggregate();
        try {
            $areaTemplate = $this->tableAreaTemplateRepository->getTableAreaDetail($slug);
            if (!$areaTemplate) {
                $result->setResultError(message: 'Không tìm thấy mẫu khu vực.', code: 404);
                return $result;
            }
            $result->setResultSuccess(data: ['area_template' => $areaTemplate]);
            return $result;
        } catch (Exception $e) {
            Log::error('Error in getTableAreaDetail: ' . $e->getMessage());
            $result->setResultError(message: 'Không thể lấy thông tin mẫu khu vực.');
            return $result;
        }
    }

    public function updateTableArea(array $data, AreaTemplate $areaTemplate): DataAggregate
    {
        $result = new DataAggregate();
        try {
            if (isset($data['name'])) {
                $data['slug'] = Str::slug($data['name']);
            }
            $updated = $this->tableAreaTemplateRepository->updateTableArea($data, $areaTemplate);
            if (!$updated) {
                $result->setResultError(message: 'Cập nhật mẫu khu vực thất bại.');
                return $result;
            }
            $result->setResultSuccess(message: 'Cập nhật mẫu khu vực thành công.');
            return $result;
        } catch (Exception $e) {
            Log::error('Error in updateTableArea: ' . $e->getMessage());
            $result->setResultError(message: 'Đã xảy ra lỗi khi cập nhật mẫu khu vực.');
            return $result;
        }
    }

    public function deleteTableArea(string $slug): DataAggregate
    {
        $result = new DataAggregate();
        try {
            $deleted = $this->tableAreaTemplateRepository->deleteTableArea($slug);
            if (!$deleted) {
                $result->setResultError(message: 'Không tìm thấy hoặc xóa thất bại.');
                return $result;
            }
            $result->setResultSuccess(message: 'Xóa mẫu khu vực thành công!');
            return $result;
        } catch (\Exception $e) {
            $result->setResultError(message: 'Đã xảy ra lỗi khi xóa mẫu khu vực.');
            return $result;
        }
    }
}
