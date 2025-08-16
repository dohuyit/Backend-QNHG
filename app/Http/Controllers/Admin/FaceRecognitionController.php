<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FaceRecognition\FaceRecognitionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class FaceRecognitionController extends Controller
{
    private $faceService;

    public function __construct(FaceRecognitionService $faceService)
    {
        $this->faceService = $faceService;
    }

    /**
     * Chụp và lưu ảnh khuôn mặt
     */
    public function captureface(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'image' => 'required|string',
            'image_count' => 'required|integer|min:1',
            'user_info' => 'sometimes|array',
            'user_info.email' => 'sometimes|email',
            'user_info.full_name' => 'sometimes|string|max:255',
            'user_info.role' => 'sometimes|in:admin,bếp,nhân viên'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->faceService->captureface(
            $request->user_id,
            $request->image,
            $request->image_count,
            $request->user_info ?? []
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Training model khuôn mặt
     */
    public function trainFaces(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'sometimes|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->faceService->trainFaces($request->user_id);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Nhận diện khuôn mặt để đăng nhập
     */
    public function recognizeFace(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->faceService->recognizeFace($request->image);

        // Nếu nhận diện thành công và đạt ngưỡng, tạo Sanctum token chuẩn
        if ($result['success'] && $result['accuracy'] >= 20) {
            $userInfo = $result['user_info'] ?? [];
            $recognizedUserId = $result['user_id'] ?? null; // có thể là mã ngoài (user_id)

            $user = null;
            // Ưu tiên: nếu có id nội bộ trong user_info thì tìm theo id
            if (isset($userInfo['id'])) {
                $user = User::find($userInfo['id']);
            }
            // Nếu chưa có và có recognizedUserId, chỉ thử theo user_id khi bảng có cột này
            if (!$user && $recognizedUserId && Schema::hasColumn((new User)->getTable(), 'user_id')) {
                $user = User::where('user_id', $recognizedUserId)->first();
            }

            if ($user) {
                // Nếu model có hằng số STATUS_ACTIVE, kiểm tra trạng thái
                if (defined(User::class . '::STATUS_ACTIVE') && $user->status !== User::STATUS_ACTIVE) {
                    $result['login_success'] = false;
                    $result['message'] = 'Tài khoản đã bị khóa hoặc không hoạt động.';
                } else {
                    // Tạo Sanctum token
                    $token = $user->createToken('admin-token')->plainTextToken;
                    $result['token'] = $token;
                    $result['login_success'] = true;
                    // Chuẩn hóa user_info trả về (nếu thiếu)
                    $result['user_info'] = $result['user_info'] ?? [
                        'id' => $user->id,
                        'user_id' => $user->user_id ?? $user->id,
                        'email' => $user->email,
                        'full_name' => $user->full_name ?? ($user->name ?? ''),
                        'role_name' => method_exists($user, 'getRoleNames') ? ($user->getRoleNames()[0] ?? '') : '',
                        'is_trained' => true,
                    ];
                }
            } else {
                $result['login_success'] = false;
                $result['message'] = 'Không tìm thấy người dùng tương ứng.';
            }
        } else {
            $result['login_success'] = false;
        }

        return response()->json($result, 200);
    }

    /**
     * Lấy danh sách users đã đăng ký
     */
    public function getRegisteredUsers(): JsonResponse
    {
        $result = $this->faceService->getRegisteredUsers();
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Xóa dữ liệu khuôn mặt user
     */
    public function deleteUserFace($userId): JsonResponse
    {
        if (!is_numeric($userId)) {
            return response()->json([
                'success' => false,
                'message' => 'User ID không hợp lệ'
            ], 422);
        }

        $result = $this->faceService->deleteUserFace($userId);
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Lấy thống kê hệ thống
     */
    public function getStatistics(): JsonResponse
    {
        $result = $this->faceService->getStatistics();
        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Kiểm tra kết nối Python API
     */
    public function checkApiConnection(): JsonResponse
    {
        $connected = $this->faceService->checkApiConnection();
        
        return response()->json([
            'success' => true,
            'connected' => $connected,
            'message' => $connected ? 'Kết nối API thành công' : 'Không thể kết nối với Python API'
        ]);
    }

    /**
     * Tạo auth token cho user (cần tùy chỉnh theo hệ thống auth hiện tại)
     */
    private function generateAuthToken($userInfo)
    {
        // Không còn sử dụng – giữ lại để tương thích cũ nếu nơi khác gọi
        return base64_encode(json_encode([
            'user_id' => $userInfo['user_id'] ?? null,
            'email' => $userInfo['email'] ?? null,
            'role' => $userInfo['role_name'] ?? null,
            'login_type' => 'face_recognition',
            'timestamp' => time()
        ]));
    }
}
