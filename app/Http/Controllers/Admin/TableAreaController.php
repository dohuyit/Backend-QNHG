<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TableAreaRequest\StoreTableAreaRequest;
use App\Http\Requests\TableAreaRequest\UpdateTableAreaRequest;
use App\Services\TableAreas\TableAreaService;
use Illuminate\Http\Request;

class TableAreaController extends Controller
{
    protected TableAreaService $tableAreaService;

    public function __construct(TableAreaService $tableAreaService)
    {
        $this->tableAreaService = $tableAreaService;
    }

    /**
     * Display a listing of the resource.
     */
    public function getListTableAreas(Request $request)
    {
        $params = $request->only(['page', 'limit', 'query', 'branch_id', 'status', 'sort_by', 'sort_order']);
        $result = $this->tableAreaService->getListTableAreas($params);
        return $this->responseSuccess($result->getResult());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function createTableArea(StoreTableAreaRequest $request)
    {
        $data = $request->only(['branch_id', 'name', 'slug', 'description', 'capacity', 'status']);
        $result = $this->tableAreaService->createTableArea($data);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }

    /**
     * Display the specified resource.
     */
    public function getTableAreaDetail(string $slug)
    {
        $result = $this->tableAreaService->getTableAreaDetail($slug);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }
        return $this->responseSuccess($result->getData());
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateTableArea(UpdateTableAreaRequest $request, string $slug)
    {
        $data = $request->only(['branch_id', 'name', 'slug', 'description', 'capacity', 'status']);

        $tableAreaResult = $this->tableAreaService->getTableAreaDetail($slug);
        if (!$tableAreaResult->isSuccessCode()) {
            return $this->responseFail(message: $tableAreaResult->getMessage(), statusCode: 404);
        }
        $tableArea = $tableAreaResult->getData();

        $result = $this->tableAreaService->updateTableArea($data, $tableArea);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
}
