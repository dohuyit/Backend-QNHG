<?php

namespace App\Services\KitchenOrders;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Helpers\ConvertHelper;
use App\Repositories\KitchenOrders\KitchenOrderRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KitchenOrderService
{
    protected KitchenOrderRepositoryInterface $kitchenOrderRepository;
    public function __construct(KitchenOrderRepositoryInterface $kitchenOrderRepository)
    {
        $this->kitchenOrderRepository = $kitchenOrderRepository;
    }
    public function getListKitchenOrder(array $params): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int)$params['limit'] : 10;
        $pagination = $this->kitchenOrderRepository->getKitchenOrderList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string) $item->id,
                'order_item_id' => $item->order_item_id,
                'order_id' => $item->order_id,
                'table_number' => $item->table_number,
                'item_name' => $item->item_name,
                'quantity' => $item->quantity,
                'notes' => $item->notes,
                'status' => $item->status,
                'is_priority' => (bool) $item->is_priority,
                'received_at' => $item->received_at,
                'completed_at' => $item->completed_at,
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
    }

    public function updateStatus(int $id, string $newStatus): DataAggregate
    {
        $result = new DataAggregate();

        $kitchenOrder = $this->kitchenOrderRepository->getByConditions(['id' => $id]);
        if (!$kitchenOrder) {
            $result->setMessage('Đơn bếp không tồn tại');
            return $result;
        }

        $currentStatus = $kitchenOrder->status;

        // Validate trạng thái mới có hợp lệ không
        $validStatuses = ['pending', 'preparing', 'ready', 'cancelled'];
        if (!in_array($newStatus, $validStatuses)) {
            $result->setMessage('Trạng thái không hợp lệ');
            return $result;
        }

        // Kiểm tra logic chuyển trạng thái
        $allowedTransitions = [
            'pending' => ['preparing', 'cancelled'],
            'preparing' => ['ready', 'cancelled'],
            'ready' => [], // ready không chuyển được nữa
            'cancelled' => [], // cancelled không chuyển được nữa
        ];

        if (!in_array($newStatus, $allowedTransitions[$currentStatus])) {
            $result->setMessage("Không thể chuyển từ trạng thái '{$currentStatus}' sang '{$newStatus}'");
            return $result;
        }

        // Cập nhật trạng thái
        $kitchenOrder->status = $newStatus;

        // Cập nhật thời gian nếu cần
        if ($newStatus === 'preparing' && $currentStatus === 'pending') {
            $kitchenOrder->received_at = now();
        } elseif ($newStatus === 'ready' && $currentStatus === 'preparing') {
            $kitchenOrder->completed_at = now();
        }

        $kitchenOrder->save();

        $result->setResultSuccess(
            message: 'Chuyển trạng thái thành công',
            data: [
                'id' => $kitchenOrder->id,
                'status' => $newStatus,
                'previous_status' => $currentStatus
            ]
        );
        return $result;
    }
    public function cancelKitchenOrder(int $id): DataAggregate
    {
        $result = new DataAggregate();

        $kitchenOrder = $this->kitchenOrderRepository->getByConditions(['id' => $id]);
        if (!$kitchenOrder) {
            $result->setMessage('Đơn bếp không tồn tại');
            return $result;
        }

        if ($kitchenOrder->status === 'ready') {
            $result->setMessage('Đơn đã hoàn tất, không thể hủy');
            return $result;
        }

        if ($kitchenOrder->status === 'preparing') {
            $result->setMessage('Đơn đang chế biến, không thể hủy');
            return $result;
        }

        if ($kitchenOrder->status === 'cancelled') {
            $result->setMessage('Đơn đã bị hủy rồi');
            return $result;
        }

        $kitchenOrder->status = 'cancelled';
        $kitchenOrder->save();

        $result->setResultSuccess(
            message: 'Hủy đơn thành công',
            data: ['new_status' => 'cancelled']
        );
        return $result;
    }
    public function countByStatus(): array
    {
        $listStatus = ['pending', 'preparing', 'ready', 'cancelled'];
        $counts = [];

        foreach($listStatus as $status) {
            $counts[$status] = $this->kitchenOrderRepository->countByConditions(['status' => $status]);
        }
        return $counts;
    }

}
