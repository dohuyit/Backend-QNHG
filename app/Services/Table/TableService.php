<?php

namespace App\Services\Table;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Models\Table;
use App\Repositories\Table\TableRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TableService
{
    protected TableRepositoryInterface $tableRepository;

    private static $statusLabels = [
        'available' => 'Trống',
        'occupied' => 'Đang sử dụng',
        'reserved' => 'Đã đặt trước',
        'cleaning' => 'Đang dọn dẹp',
        'out_of_service' => 'Bàn đang sửa chữa',
    ];

    private static $tableTypeLabels = [
        '2_seats' => '2 ghế',
        '4_seats' => '4 ghế',
        '6_seats' => '6 ghế',
        '8_seats' => '8 ghế',
    ];

    public function __construct(TableRepositoryInterface $tableRepository)
    {
        $this->tableRepository = $tableRepository;
    }

    public function getListTables(array $params): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int) $params['limit'] : 1000;

        $pagination = $this->tableRepository->getTableList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string) $item->id,
                'table_number' => $item->table_number ?? null,
                'description' => $item->description ?? null,
                'table_type' => $item->table_type ?? null,
                'table_type_label' => self::$tableTypeLabels[$item->table_type] ?? $item->table_type,
                'status' => $item->status ?? null,
                'label_status' => self::$statusLabels[$item->status] ?? $item->status,
                'tags' => $item->tags ?? null,
                'table_area' => $item->tableArea ? [
                    'id' => (string) $item->tableArea->id,
                    'name' => $item->tableArea->name,
                ] : null,
                'created_at' => $item->created_at->toDateTimeString(),
                'updated_at' => $item->updated_at->toDateTimeString(),
            ];
        }

        $result = new ListAggregate($data);
        $result->setMeta(
            page: $pagination->currentPage(),
            perPage: $pagination->perPage(),
            total: $pagination->total()
        );

        return $result;
    }

    public function getTableDetail(string $id): DataAggregate
    {
        $result = new DataAggregate;
        $table = $this->tableRepository->getByConditions(['id' => $id]);
        if (!$table) {
            $result->setResultError(message: 'Bàn không tồn tại hoặc đã bị khóa');
            return $result;
        }

        // Lấy thông tin khu vực bàn (table area)
        $tableArea = $table->tableArea ? [
            'id' => (string) $table->tableArea->id,
            'name' => $table->tableArea->name,
        ] : null;

        // Lấy thông tin đơn hàng nếu bàn đang sử dụng
        $orderData = null;
        if ($table->status === 'occupied') {
            $order = $table->orders()->where('status', '!=', 'completed')->first();
            if ($order) {
                $orderData = [
                    'id' => (string) $order->id,
                    'total_amount' => $order->total_amount,
                    'number_of_guests' => $order->number_of_guests,
                    'contact_name' => $order->contact_name,
                    'contact_phone' => $order->contact_phone,
                    'payment_method' => $order->payment_method,
                    'notes' => $order->notes,
                    'created_at' => $order->created_at->toDateTimeString(),
                    'customer' => $order->customer ? [
                        'id' => (string) $order->customer->id,
                        'full_name' => $order->customer->full_name ?? $order->customer->name,
                        'phone_number' => $order->customer->phone_number ?? $order->customer->phone,
                        'email' => $order->customer->email,
                    ] : null,
                    'order_items' => $order->items->map(function ($item) {
                        return [
                            'id' => (string) $item->id,
                            'dish_name' => $item->menuItem ? $item->menuItem->name : ($item->combo ? $item->combo->name : $item->name),
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'notes' => $item->notes,
                            'kitchen_status' => $item->kitchen_status,
                        ];
                    })->toArray(),
                ];
            }
        }


        $tableData = [
            'id' => (string) $table->id,
            'table_number' => $table->table_number ?? null,
            'description' => $table->description ?? null,
            'table_type' => $table->table_type ?? null,
            'table_type_label' => self::$tableTypeLabels[$table->table_type] ?? $table->table_type,
            'status' => $table->status ?? null,
            'label_status' => self::$statusLabels[$table->status] ?? $table->status,
            'tags' => $table->tags ?? null,
            'table_area' => $tableArea,
            'created_at' => $table->created_at ? $table->created_at->toDateTimeString() : null,
            'updated_at' => $table->updated_at ? $table->updated_at->toDateTimeString() : null,
            'order' => $orderData,
        ];

        $result->setResultSuccess(data: ['table' => $tableData]);
        return $result;
    }

    public function createTable(array $data): DataAggregate
    {
        $result = new DataAggregate;
        try {
            // Đặt trạng thái mặc định là available khi tạo bàn mới
            $data['status'] = 'available';

            $table = $this->tableRepository->createTable($data);
            $result->setResultSuccess(
                data: ['table' => $table],
                message: 'Tạo bàn thành công'
            );
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                $result->setResultError(message: 'Tên bàn đã tồn tại');
            } else {
                $result->setResultError(message: 'Tạo bàn thất bại: ' . $e->getMessage());
            }
        }
        return $result;
    }

    public function updateTable(array $data, $table): DataAggregate
    {
        $result = new DataAggregate;
        if (!$table) {
            $result->setResultError(message: 'Bàn không tồn tại');
            return $result;
        }

        // Kiểm tra logic nghiệp vụ khi cập nhật trạng thái
        if (isset($data['status'])) {
            $currentStatus = $table->status;
            $newStatus = $data['status'];

            // Kiểm tra xem có thể chuyển từ trạng thái hiện tại sang trạng thái mới không
            if (!$this->canChangeStatus($currentStatus, $newStatus)) {
                $result->setResultError(message: 'Không thể chuyển từ trạng thái "' . self::$statusLabels[$currentStatus] . '" sang "' . self::$statusLabels[$newStatus] . '"');
                return $result;
            }
        }

        $listDataUpdate = [];

        // Chỉ cập nhật các trường được gửi lên
        if (isset($data['table_number'])) {
            $listDataUpdate['table_number'] = $data['table_number'];
        }
        if (isset($data['description'])) {
            $listDataUpdate['description'] = $data['description'];
        }
        if (isset($data['table_type'])) {
            $listDataUpdate['table_type'] = $data['table_type'];
        }
        if (isset($data['tags'])) {
            $listDataUpdate['tags'] = $data['tags'];
        }
        if (isset($data['status'])) {
            $listDataUpdate['status'] = $data['status'];
        }
        if (isset($data['table_area_id'])) {
            $listDataUpdate['table_area_id'] = $data['table_area_id'];
        }

        $ok = $this->tableRepository->updateByConditions(
            conditions: ['id' => $table->id],
            updateData: $listDataUpdate
        );
        if (!$ok) {
            $result->setResultError(message: 'Cập nhật thất bại, vui lòng thử lại!');
            return $result;
        }
        // Nếu có cập nhật trạng thái thì broadcast event
        if (isset($listDataUpdate['status'])) {
            $tableFresh = $this->tableRepository->getByConditions(['id' => $table->id]);
            event(new \App\Events\Tables\TableStatusUpdated([
                'id' => $tableFresh->id,
                'table_number' => $tableFresh->table_number,
                'status' => $tableFresh->status,
                'updated_at' => $tableFresh->updated_at,
                // ... các trường khác nếu cần
            ]));
        }
        $result->setResultSuccess(message: 'Cập nhật bàn thành công!');
        return $result;
    }

    /**
     * Kiểm tra xem có thể chuyển từ trạng thái hiện tại sang trạng thái mới không
     */
    private function canChangeStatus(string $currentStatus, string $newStatus): bool
    {
        // Định nghĩa các trạng thái có thể chuyển đổi
        $allowedTransitions = [
            'available' => ['occupied', 'cleaning', 'out_of_service'],
            'occupied' => ['available', 'cleaning'],
            'cleaning' => ['available', 'out_of_service'],
            'out_of_service' => ['available', 'cleaning'],
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }

    public function deleteTable($id): DataAggregate
    {
        $result = new DataAggregate;
        $table = $this->tableRepository->getByConditions(['id' => $id]);
        if (!$table) {
            $result->setResultError(message: 'Bàn không tồn tại');
            return $result;
        }
        $ok = $this->tableRepository->deleteTable($id);
        if (!$ok) {
            $result->setResultError(message: 'Xóa bàn thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Xóa bàn thành công!');
        return $result;
    }

    public function countByStatus(): array
    {
        $listStatus = ['available', 'occupied', 'reserved', 'cleaning', 'out_of_service'];
        $counts = [];

        foreach ($listStatus as $status) {
            $counts[$status] = $this->tableRepository->countByConditions(['status' => $status]);
        }
        return $counts;
    }

    public function getTablesGroupedByStatusByTableNumber(string $tableNumber): DataAggregate
    {
        $result = new DataAggregate;

        $table = $this->tableRepository->getByConditions(['table_number' => $tableNumber]);

        if (!$table) {
            $result->setResultError(message: 'Bàn không tồn tại');
            return $result;
        }

        $tables = $this->getListTables([
            'table_area_id' => $table->table_area_id,
            'limit' => 1000, // lấy tất cả bàn trong khu vực
        ]);

        $item = [
            'table_area_id' => $table->table_area_id,
            'requested_table' => $table->only(['id', 'table_number', 'status']),
            'tables' => $tables->items // lấy mảng các bàn
        ];

        $result->setResultSuccess(data: ['table' => $item]);
        return $result;
    }
}
