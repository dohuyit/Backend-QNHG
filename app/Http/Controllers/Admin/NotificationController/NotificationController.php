<?php

namespace App\Http\Controllers\Admin\NotificationController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use App\Services\Notifications\NotificationService;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    // Lấy danh sách thông báo cho user hiện tại hoặc tất cả
    public function getList(Request $request)
    {
        $limit = (int)($request->input('limit', 10));
        $receiverId = $request->input('receiver_id');
        $notifications = $this->notificationService->getListNotification($receiverId, $limit);
        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }
    // Đánh dấu đã đọc tất cả thông báo
    public function markAllRead(Request $request)
    {
        $user = Auth::user();
        Notification::where('receiver_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        return response()->json(['success' => true]);
    }
}
