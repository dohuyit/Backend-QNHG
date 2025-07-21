<?php

namespace App\Http\Controllers\Admin\Table;

use App\Http\Controllers\Controller;
use App\Http\Requests\TableRequest\StoreTableRequest;
use App\Http\Requests\TableRequest\UpdateTableRequest;
use App\Models\Table;
use App\Repositories\Table\TableRepositoryInterface;
use App\Services\Table\TableService;
use Illuminate\Http\Request;

class TableController extends Controller
{
    protected TableService $tableService;
    protected TableRepositoryInterface $tableRepository;

    public function __construct(
        TableService $tableService,
        TableRepositoryInterface $tableRepository
    ) {
        $this->tableService = $tableService;
        $this->tableRepository = $tableRepository;
    }

    public function getListTables(Request $request)
    {
        $params = $request->only(
            'page',
            'limit',
            'query',
            'table_number',
            'description',
            'table_type',
            'status',
            'table_area_id'
        );
        $result = $this->tableService->getListTables($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }

    public function getTableDetail($id)
    {
        $result = $this->tableService->getTableDetail($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: 'Bàn không tồn tại', statusCode: 404);
        }
        $data = $result->getData();
        return $this->responseSuccess($data);
    }

    public function createTable(StoreTableRequest $request)
    {
        $data = $request->only([
            'table_number',
            'description',
            'table_type',
            'tags',
            'table_area_id'
        ]);

        $result = $this->tableService->createTable($data);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess($result->getData(), message: $result->getMessage());
    }

    public function updateTable(UpdateTableRequest $request, $id)
    {
        $data = $request->only([
            'table_number',
            'description',
            'table_type',
            'tags',
            'status',
            'table_area_id'
        ]);

        $table = $this->tableRepository->findById($id);
        if (!$table) {
            return $this->responseFail(message: 'Bàn không tồn tại', statusCode: 404);
        }

        $result = $this->tableService->updateTable($data, $table);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }

    public function destroyTable($id)
    {
        $result = $this->tableService->deleteTable($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: 'Bàn không tồn tại', statusCode: 404);
        }
        return $this->responseSuccess(message: 'Xóa bàn thành công');
    }

    public function countByStatus()
    {
        $filter = request()->all();
        $result = $this->tableService->countByStatus($filter);

        return $this->responseSuccess($result);
    }

    /**
     * Lấy danh sách các loại bàn
     */
    public function getTableTypes()
    {
        $tableTypes = Table::getTableTypes();
        return $this->responseSuccess($tableTypes);
    }

    /**
     * Lấy danh sách các trạng thái
     */
    public function getStatuses()
    {
        $statuses = Table::getStatuses();
        return $this->responseSuccess($statuses);
    }

    public function getTablesByStatus(Request $request)
    {
        $request->validate([
            'table_number' => 'required|string'
        ]);

        $result = $this->tableService->getTablesGroupedByStatusByTableNumber($request->table_number);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }

        return $this->responseSuccess(data: $result->getData(), message: $result->getMessage());
    }
}
