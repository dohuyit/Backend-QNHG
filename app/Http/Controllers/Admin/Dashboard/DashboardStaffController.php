<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\Reservations\ReservationService;
use App\Services\KitchenOrders\KitchenOrderService;
use Illuminate\Http\Request;

class DashboardStaffController extends Controller
{
    protected ReservationService $reservationService;
    protected KitchenOrderService $kitchenOrderService;

    public function __construct(ReservationService $reservationService, KitchenOrderService $kitchenOrderService)
    {
        $this->reservationService = $reservationService;
        $this->kitchenOrderService = $kitchenOrderService;
    }

    public function index(Request $request)
    {
        // 1) Đếm trạng thái đặt bàn
        $reservationStatusCounts = $this->reservationService->countByStatus();

        // 2) Bàn đang phục vụ (ước tính): đếm đơn hàng đang mở nếu có, fallback 0
        // Nếu hệ thống có OrderRepository và status cho đơn đang phục vụ, có thể thay thế logic dưới đây.
        $activeTablesCount = 0;
        if (class_exists('App\\Models\\Order')) {
            $activeTablesCount = \App\Models\Order::query()
                ->whereIn('status', ['pending_confirmation', 'confirmed', 'preparing', 'serving'])
                ->distinct('id')
                ->count('id');
        }

        // 3) Hàng đợi món theo trạng thái (mặc định: ready)
        $status = $request->query('status', 'ready'); // pending|preparing|ready|cancelled
        $limit = (int) $request->query('limit', 10);
        $serveQueue = $this->kitchenOrderService->getListKitchenOrder([
            'status' => $status,
            'limit' => $limit,
        ])->getResult();

        // 4) Cảnh báo (placeholder): đơn đặt bàn sắp đến giờ hoặc quá hạn đã có autoCancel
        // Tạm thời đưa ra danh sách pending gần thời điểm hiện tại (tùy chỉnh sau)
        $alerts = [];

        // 5) Top món gọi nhiều (giống kitchen, hữu ích cho staff) — aggregate theo dish_id
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
            'reservation_status_counts' => $reservationStatusCounts,
            'active_tables_count' => $activeTablesCount,
            'serve_queue' => $serveQueue['items'] ?? $serveQueue,
            'alerts' => $alerts,
            'top_dishes' => $topDishes,
        ]);
    }
}

