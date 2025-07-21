<?php

namespace App\Http\Controllers\Admin\TableArea;

use App\Http\Controllers\Controller;
use App\Http\Requests\TableAreaRequest\TableAreaRequest;
use App\Models\TableArea;
use App\Repositories\TableArea\TableAreaRepositoryInterface;
use App\Services\TableArea\TableAreaService;
use Illuminate\Http\Request;

class TableAreaController extends Controller
{
    protected TableAreaService $tableAreaService;
    protected TableAreaRepositoryInterface $tableAreaRepository;

    public function __construct(
        TableAreaService $tableAreaService,
        TableAreaRepositoryInterface $tableAreaRepository
    ) {
        $this->tableAreaService = $tableAreaService;
        $this->tableAreaRepository = $tableAreaRepository;
    }

    public function getListTableArea(Request $request)
    {
        $params = $request->only(
            'page',
            'limit',
            'query',
            'name',
            'description',
            'capacity',
            'status'
        );
        $result = $this->tableAreaService->getListTableAreas($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }

    public function getTableAreaDetail($id)
    {
        $result = $this->tableAreaService->getTableAreaDetail($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: 'Khu vực bàn không tồn tại', statusCode: 404);
        }
        $data = $result->getData();
        return $this->responseSuccess($data);
    }

    public function createTableArea(TableAreaRequest $request)
    {
        $data = $request->only([
            'name',
            'description',
            'capacity',
            'status'
        ]);

        $result = $this->tableAreaService->createTableArea($data);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess($result->getData(), message: $result->getMessage());
    }

    public function updateTableArea(TableAreaRequest $request, $id)
    {
        $data = $request->only([
            'name',
            'description',
            'capacity',
            'status'
        ]);

        $tableArea = $this->tableAreaRepository->findById($id);
        if (!$tableArea) {
            return $this->responseFail(message: 'Khu vực bàn không tồn tại', statusCode: 404);
        }

        $result = $this->tableAreaService->updateTableArea($data, $tableArea);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }

    public function destroy($id)
    {
        $result = $this->tableAreaService->deleteTableArea($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: 'Khu vực bàn không tồn tại', statusCode: 404);
        }
        return $this->responseSuccess(message: 'Xóa khu vực bàn thành công');
    }

    public function countByStatus()
    {
        $filter = request()->all();
        $result = $this->tableAreaService->countByStatus($filter);

        return $this->responseSuccess($result);
    }

}
