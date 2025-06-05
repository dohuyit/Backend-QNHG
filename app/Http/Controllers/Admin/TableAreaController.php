<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TableAreaRequest\CreateBranchTableAreaRequest;
use App\Services\TableAreas\BranchTableAreaService;
use App\Models\TableArea;
use Illuminate\Http\Request;

class TableAreaController extends Controller
{
    protected BranchTableAreaService $branchTableAreaService;

    public function __construct(BranchTableAreaService $branchTableAreaService)
    {
        $this->branchTableAreaService = $branchTableAreaService;
    }

    public function getTableAreasByBranch(Request $request, int $branchId)
    {
        $filter = $request->only(['page', 'limit', 'query', 'sort_by', 'sort_order', 'status']);
        $result = $this->branchTableAreaService->getTableAreasByBranch($branchId, $filter);
        return $this->responseSuccess($result->getResult());
    }

    public function createTableAreaForBranch(CreateBranchTableAreaRequest $request)
    {
        $data = $request->validated();
        $result = $this->branchTableAreaService->createTableAreaForBranch($data);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }

    public function createTableAreaForAllBranches(CreateBranchTableAreaRequest $request)
    {
        $data = $request->validated();
        $result = $this->branchTableAreaService->createTableAreaForAllBranches($data);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }

    public function updateTableAreaForBranch(CreateBranchTableAreaRequest $request, string $slug)
    {
        $tableArea = TableArea::where('slug', $slug)->first();
        if (!$tableArea) {
            return $this->responseFail(message: 'Không tìm thấy khu vực bàn.', statusCode: 404);
        }

        $data = $request->validated();
        $result = $this->branchTableAreaService->updateTableAreaForBranch($data, $tableArea);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }

    public function getTableAreaDetail(string $slug)
    {
        $tableArea = TableArea::where('slug', $slug)->first();
        if (!$tableArea) {
            return $this->responseFail(message: 'Không tìm thấy khu vực bàn.', statusCode: 404);
        }
        $result = $this->branchTableAreaService->getTableAreaDetail($tableArea);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }
        return $this->responseSuccess($result->getData());
    }

    public function deleteTableAreaForBranch(string $slug)
    {
        $tableArea = TableArea::where('slug', $slug)->first();
        if (!$tableArea) {
            return $this->responseFail(message: 'Không tìm thấy khu vực bàn.', statusCode: 404);
        }
        $result = $this->branchTableAreaService->deleteTableAreaForBranch($tableArea);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
}
