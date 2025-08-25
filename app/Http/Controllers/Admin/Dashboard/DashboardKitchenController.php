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
        // Lấy danh sách ready KHÔNG áp filter ngày ở repository (vì completed_at có thể null),
        // ta sẽ tự lọc theo khoảng ngày ở dưới dựa trên completed_at hoặc updated_at
        $readyFilter = [
            'status' => 'ready',
            'limit' => 1000,
        ];
        $readyList = $this->kitchenOrderService->getListKitchenOrder($readyFilter)->getResult();

        // Tính on-time vs late ngay tại BE để FE luôn có số liệu
        $readyItems = $readyList['data'] ?? $readyList ?? [];
        $onTime = 0; $late = 0; $considered = 0;
        $defaultCooking = 15; // phút
        foreach ($readyItems as $it) {
            $cooking = $it['cooking_time'] ?? null;
            if (!is_numeric($cooking) || (int)$cooking <= 0) { $cooking = $defaultCooking; }
            $startStr = $it['received_at'] ?? $it['created_at'] ?? null;
            $doneStr  = $it['completed_at'] ?? $it['updated_at'] ?? null;
            if (!$startStr || !$doneStr) { continue; }
            try {
                $start = \Carbon\Carbon::parse($startStr);
                $done  = \Carbon\Carbon::parse($doneStr);
            } catch (\Exception $e) { continue; }
            // Áp dụng lọc khoảng ngày nếu có: dùng mốc 'done'
            if ($dateFrom && $done->toDateString() < $dateFrom) { continue; }
            if ($dateTo && $done->toDateString() > $dateTo) { continue; }
            if ($done->lessThan($start)) { continue; }
            $diffMin = (int) ceil($done->diffInSeconds($start) / 60);
            if ($diffMin <= (int)$cooking) { $onTime++; } else { $late++; }
            $considered++;
        }

        // Fallback: nếu khoảng ngày không có dữ liệu, lấy tổng thể để hiển thị
        if ($considered === 0) {
            $fallbackFilter = [ 'status' => 'ready', 'limit' => 1000 ];
            $fallbackList = $this->kitchenOrderService->getListKitchenOrder($fallbackFilter)->getResult();
            $fallbackItems = $fallbackList['data'] ?? $fallbackList ?? [];
            foreach ($fallbackItems as $it) {
                $cooking = $it['cooking_time'] ?? null;
                if (!is_numeric($cooking) || (int)$cooking <= 0) { $cooking = $defaultCooking; }
                $startStr = $it['received_at'] ?? $it['created_at'] ?? null;
                $doneStr  = $it['completed_at'] ?? $it['updated_at'] ?? null;
                if (!$startStr || !$doneStr) { continue; }
                try {
                    $start = \Carbon\Carbon::parse($startStr);
                    $done  = \Carbon\Carbon::parse($doneStr);
                } catch (\Exception $e) { continue; }
                if ($dateFrom && $done->toDateString() < $dateFrom) { continue; }
                if ($dateTo && $done->toDateString() > $dateTo) { continue; }
                if ($done->lessThan($start)) { continue; }
                $diffMin = (int) ceil($done->diffInSeconds($start) / 60);
                if ($diffMin <= (int)$cooking) { $onTime++; } else { $late++; }
                $considered++;
            }
        }

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
            'on_time_count' => $onTime,
            'late_count' => $late,
        ]);
    }
}