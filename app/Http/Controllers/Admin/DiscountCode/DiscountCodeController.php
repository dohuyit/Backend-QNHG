<?php

namespace App\Http\Controllers\Admin\DiscountCode;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiscountCodeRequest\StoreDiscountCodeRequest;
use App\Http\Requests\DiscountCodeRequest\UpdateDiscountCodeRequest;
use App\Repositories\DiscountCodes\DiscountCodeRepository;
use App\Services\DiscountCodes\DiscountCodeService;

class DiscountCodeController extends Controller
{
    protected DiscountCodeService $discountCodeService;
    protected DiscountCodeRepository $discountCodeRepository;

    public function __construct(DiscountCodeService $discountCodeService, DiscountCodeRepository $discountCodeRepository)
    {
        $this->discountCodeService = $discountCodeService;
        $this->discountCodeRepository = $discountCodeRepository;

    }
    public function getListDiscountCodes()
    {
        $params = request()->only(
            'page',
            'limit',
            'query',
            'code',
            'type',
            'is_active',
            'start_date',
            'end_date',
            'value',
            'usage_limit',
            'used'
        );
        $result = $this->discountCodeService->getListDiscountCodes($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }
    public function createDiscountCode(StoreDiscountCodeRequest $request)
    {
        $data = $request->only([
            'code',
            'type',
            'is_active',
            'start_date',
            'end_date',
            'value',
            'usage_limit',
            'used'
        ]);
        $result = $this->discountCodeService->createDiscountCode($data);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }
        $data = $result->getData();
        return $this->responseSuccess($data);
    }
    public function updateDiscountCode(UpdateDiscountCodeRequest  $request, int $id)
    {
        $data = $request->only([
            'code',
            'type',
            'is_active',
            'start_date',
            'end_date',
            'value',
            'usage_limit',
            'used'
        ]);

        $discountCode = $this->discountCodeRepository->getByConditions(['id' => $id]);
        if (!$discountCode) {
            return $this->responseFail(message: 'Mã giảm giá không tồn tại', statusCode: 404);
        }

        $result = $this->discountCodeService->updateDiscountCode($data, $discountCode);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
       public function deleteDiscountCode(int $id)
    {
        $discountCode = $this->discountCodeRepository->getByConditions(['id' => $id]);
        
        $result = $this->discountCodeService->deleteDiscountCode($discountCode);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function countByStatus()
    {
        $filter = request()->all();

        $result = $this->discountCodeService->countByStatus($filter);

        return $this->responseSuccess($result);
    }
}
