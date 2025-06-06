<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TableAreaTemplateRequest\StoreTableAreaTemplateRequest;
use App\Http\Requests\TableAreaTemplateRequest\UpdateTableAreaTemplateRequest;
use App\Repositories\TableAreaTemplates\TableAreaTemplateRepositoryInterface;
use App\Services\TableAreaTemplates\TableAreaTemplateService;
use Illuminate\Http\Request;

class TableAreaTemplateController extends Controller
{
    protected TableAreaTemplateService $tableAreaTemplateService;

    protected TableAreaTemplateRepositoryInterface $tableAreaTemplateRepository;

    public function __construct(TableAreaTemplateService $tableAreaTemplateService, TableAreaTemplateRepositoryInterface $tableAreaTemplateRepository)
    {
        $this->tableAreaTemplateService = $tableAreaTemplateService;
        $this->tableAreaTemplateRepository = $tableAreaTemplateRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function getListTableAreasTemplate(Request $request)
    {
        $filter = $request->only(['page', 'limit', 'query', 'sort_by', 'sort_order', 'name', 'description', 'slug']);
        $result = $this->tableAreaTemplateService->getListTableAreas($filter);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    // Trong TableAreaController.php
    public function createTableAreaTemplate(StoreTableAreaTemplateRequest $request)
    {
        try {
            $data = $request->only(['name', 'description', 'slug']); // Thêm slug
            $result = $this->tableAreaTemplateService->createTableArea($data);

            if (!$result->isSuccessCode()) {
                return $this->responseFail(message: $result->getMessage());
            }
            return $this->responseSuccess(message: $result->getMessage());
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function getTableAreaTemplateDetail(string $slug)
    {
        $result = $this->tableAreaTemplateService->getTableAreaDetail($slug);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }
        return $this->responseSuccess($result->getData());
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateTableAreaTemplate(UpdateTableAreaTemplateRequest $request, string $slug)
    {
        $data = $request->only(['name', 'description']);
        $areaTemplateResult = $this->tableAreaTemplateService->getTableAreaDetail($slug);
        if (!$areaTemplateResult->isSuccessCode()) {
            return $this->responseFail(message: $areaTemplateResult->getMessage(), statusCode: 404);
        }
        $areaTemplate = $areaTemplateResult->getData()['area_template'];
        $result = $this->tableAreaTemplateService->updateTableArea($data, $areaTemplate);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }

    public function deleteTableAreaTemplate(string $slug)
    {
        $result = $this->tableAreaTemplateService->deleteTableArea($slug);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
}
