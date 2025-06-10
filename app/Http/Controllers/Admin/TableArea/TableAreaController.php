<?php

namespace App\Http\Controllers\Admin\TableArea;

use App\Http\Controllers\Controller;
use App\Http\Requests\TableAreaRequest\TableAreaRequest;
use App\Repositories\TableArea\TableAreaRepositoryInterface;
use App\Services\TableArea\TableAreaService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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

    public function index(Request $request): JsonResponse
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
        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }

    public function show($id): JsonResponse
    {
        $tableArea = $this->tableAreaService->getTableAreaDetail($id);
        if (!$tableArea) {
            return response()->json([
                'status' => 'error',
                'message' => 'Khu vực bàn không tồn tại'
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'data' => $tableArea
        ]);
    }

    public function store(TableAreaRequest $request): JsonResponse
    {
        $data = $request->only([
            'name',
            'description',
            'capacity',
            'status'
        ]);

        $tableArea = $this->tableAreaService->createTableArea($data);
        return response()->json([
            'status' => 'success',
            'message' => 'Tạo khu vực bàn thành công',
            'data' => $tableArea
        ], 201);
    }

    public function update(TableAreaRequest $request, $id): JsonResponse
    {
        $data = $request->only([
            'name',
            'description',
            'capacity',
            'status'
        ]);

        $tableArea = $this->tableAreaRepository->findById($id);
        if (!$tableArea) {
            return response()->json([
                'status' => 'error',
                'message' => 'Khu vực bàn không tồn tại'
            ], 404);
        }

        $this->tableAreaService->updateTableArea($id, $data);
        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật khu vực bàn thành công'
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $tableArea = $this->tableAreaRepository->findById($id);
        if (!$tableArea) {
            return response()->json([
                'status' => 'error',
                'message' => 'Khu vực bàn không tồn tại'
            ], 404);
        }

        $this->tableAreaService->deleteTableArea($id);
        return response()->json([
            'status' => 'success',
            'message' => 'Xóa khu vực bàn thành công'
        ]);
    }
}
