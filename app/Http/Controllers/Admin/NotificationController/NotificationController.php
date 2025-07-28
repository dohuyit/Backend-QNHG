<?php

namespace App\Http\Controllers\Admin\NotificationController;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // Lấy danh sách thông báo cho user hiện tại
    public function getList(Request $request)
    {
        $user = Auth::user();
        $limit = (int)($request->input('limit', 10));
        $notifications = Notification::where('receiver_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
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