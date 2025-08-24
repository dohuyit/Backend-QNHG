<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\KitchenOrders\KitchenOrderService;
use Illuminate\Http\Request;

class DashboardKitchenController extends Controller
{
    protected KitchenOrderService $kitchenOrderService;

    public function __construct(KitchenOrderService $kitchenOrderService)
    {
        $this->kitchenOrderService = $kitchenOrderService;
    }

    public function index(Request $request)
    {
        // 1) Đếm trạng thái đơn bếp
        $orderStatusCounts = $this->kitchenOrderService->countByStatus();

        // 2) Hàng đợi ưu tiên (ưu tiên hiển thị)
        $priorityList = $this->kitchenOrderService->getListKitchenOrder([
            'is_priority' => 1,
            'limit' => 10,
        ])->getResult();

        // 3) Món đã sẵn sàng (ready) theo khoảng ngày (mặc định: hôm nay)
        $dateFrom = $request->query('date_from'); // format: Y-m-d
        $dateTo = $request->query('date_to');     // format: Y-m-d
        if (empty($dateFrom) && empty($dateTo)) {
            $today = now()->toDateString();
            $dateFrom = $today;
            $dateTo = $today;
        }
        $readyFilter = [
            'status' => 'ready',
            'limit' => 10,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];
        $readyList = $this->kitchenOrderService->getListKitchenOrder($readyFilter)->getResult();

        // FE sẽ tự tính tỉ lệ đúng/trễ giờ dựa trên các trường hiện có của ready_items

        // 4) Top món gọi nhiều (tổng quantity theo dish_id, bỏ cancelled)
        // order_items có dish_id/combo_id, không có menu_item_id/menu_item_name
        $topDishes = \App\Models\OrderItem::query()
            ->join('dishes', 'order_items.dish_id', '=', 'dishes.id')
            ->whereNotNull('order_items.dish_id')
            ->where('order_items.kitchen_status', '!=', 'cancelled')
            ->groupBy('order_items.dish_id', 'dishes.name')
            ->selectRaw('order_items.dish_id as dish_id, dishes.name as name, SUM(order_items.quantity) as total_quantity')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                return [
                    'dish_id' => $row->dish_id,
                    'name' => $row->name,
                    'total_quantity' => (int) $row->total_quantity,
                ];
            });

        return $this->responseSuccess(data: [
            'order_status_counts' => $orderStatusCounts,
            'priority_items' => $priorityList['data'] ?? $priorityList,
            'ready_items' => $readyList['data'] ?? $readyList,
            'top_dishes' => $topDishes,
        ]);
    }
}