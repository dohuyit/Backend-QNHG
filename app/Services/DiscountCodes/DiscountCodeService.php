<?php

namespace App\Services\DiscountCodes;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Models\DiscountCode;
use App\Repositories\DiscountCodes\DiscountCodeRepositoryInterface;

class DiscountCodeService
{
    protected  $discountCodeRepository;
    public function __construct(DiscountCodeRepositoryInterface $discountCodeRepository)
    {
        $this->discountCodeRepository = $discountCodeRepository;
    }
    public function getListDiscountCodes(array $params): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int)$params['limit'] : 10;
        $pagination =  $this->discountCodeRepository->getDiscountCodeList($filter, $limit);

        $data  = [];
        foreach ($pagination as $item) {
            $data[] = [
                'id' => (string)$item->id,
                'code' => $item->code,
                'type' => $item->type,
                'is_active' => (bool)$item->is_active,
                'value' => $item->value,
                'usage_limit' => $item->usage_limit,
                'used' => $item->used,
                'start_date' => $item->start_date,
                'end_date' => $item->end_date,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        }
        $result = new ListAggregate($data);
        $result->setMeta(
            page: $pagination->currentPage(),
            total: $pagination->total(),
            perPage: $pagination->perPage(),
        );
        return $result;
    }
    public function createDiscountCode(array $data)
    {
        $result = new DataAggregate();
        $listDataCreate = [
            'code' => $data['code'],
            'type' => $data['type'],
            'value' => $data['value'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'is_active' => $data['is_active'],
            'usage_limit' => $data['usage_limit'],
            'used' => 0
        ];
        $ok = $this->discountCodeRepository->createData($listDataCreate);
        if (!$ok) {
            $result->setMessage(message: 'Thêm mới thất bại, vui lòng thử lại');
            return $result;
        }
        $result->setResultSuccess(message: 'Thêm mới thành công');
        return $result;
    }
    public function getDiscountCodeDetail(int $id): DataAggregate
    {
        $result = new DataAggregate();

        $discountCode = $this->discountCodeRepository->getByConditions(['id' => $id]);

        if (!$discountCode) {
            $result->setMessage(message: 'Mã giảm giá không tồn tại');
            return $result;
        }

        $result->setResultSuccess(data: ['discountCode' => $discountCode]);
        return $result;
    }
    public function updateDiscountCode(array $data, $discountCode): DataAggregate
    {
        $result = new DataAggregate();

        $listDataUpdate = [
            'code' => $data['code'],
            'type' => $data['type'],
            'value' => $data['value'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'is_active' => $data['is_active'],
            'usage_limit'  => $data['usage_limit'],
            'used' => $data['used'] ?? 0
        ];

        $ok = $this->discountCodeRepository->updateByConditions(['id' => $discountCode->id], $listDataUpdate);
        if (!$ok) {
            $result->setMessage(message: 'Cập nhật thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Cập nhật thành công!');
        return $result;
    }
    public function deleteDiscountCode(DiscountCode $discountCode): DataAggregate
    {
        $result = new DataAggregate;

        $ok = $this->discountCodeRepository->forceDelete($discountCode);
        if (!$ok) {
            $result->setMessage('Xóa mã giảm giá thất bại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Xóa mã giảm giá thành công!');
        return $result;
    }
    public function countByStatus(array $filter = []): array
    {
        $listStatus = [true, false];
        $counts = [];

        foreach ($listStatus as $status) {
            $key = $status ? 'active' : 'inactive';

            // merge filter với điều kiện status
            $conditions = array_merge($filter, ['is_active' => $status]);

            $counts[$key] = $this->discountCodeRepository->countByConditions($conditions);
        }

        return $counts;
    }
}
