<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\FaceLoginRequest;
use App\Services\Auth\AuthService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class FaceAuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Đăng nhập bằng khuôn mặt sử dụng FaceNet
     */
    public function loginWithFaceNet(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|integer',
                'confidence' => 'required|numeric|min:0|max:1'
            ]);

            $userId = $request->input('user_id');
            $confidence = $request->input('confidence');

            // Kiểm tra độ tin cậy tối thiểu
            if ($confidence < 0.2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Độ tin cậy khuôn mặt quá thấp. Vui lòng thử lại.',
                    'code' => 'FACE_CONFIDENCE_LOW'
                ], 400);
            }

            // Tìm user trong hệ thống
            $user = User::with('roles.permissions')->find($userId);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng với ID này.',
                    'code' => 'USER_NOT_FOUND'
                ], 404);
            }

            // Kiểm tra trạng thái user
            if ($user->status !== User::STATUS_ACTIVE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản đã bị khóa hoặc không hoạt động.',
                    'code' => 'USER_INACTIVE'
                ], 403);
            }

            // Tạo token đăng nhập
            $token = $user->createToken('admin-token')->plainTextToken;

            // Lấy thông tin user
            $permissions = $user->roles
                ->flatMap(fn($role) => $role->permissions)
                ->pluck('permission_name')
                ->unique()
                ->values();

            $roles = $user->roles->map(fn($role) => [
                'id' => $role->id,
                'name' => $role->role_name,
            ]);

            $userData = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'full_name' => $user->full_name,
                'roles' => $roles,
                'permissions' => $permissions,
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $userData,
                    'token' => $token,
                    'face_confidence' => $confidence
                ],
                'message' => 'Đăng nhập bằng khuôn mặt thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('FaceNet login error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra trong quá trình đăng nhập bằng khuôn mặt.',
                'code' => 'FACE_LOGIN_ERROR'
            ], 500);
        }
    }

    /**
     * Đăng ký khuôn mặt mới cho user
     */
    public function registerFace(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|integer',
                'face_image' => 'required|string' // base64 image
            ]);

            $userId = $request->input('user_id');
            $faceImage = $request->input('face_image');

            // Kiểm tra user có tồn tại không
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng với ID này.',
                    'code' => 'USER_NOT_FOUND'
                ], 404);
            }

            // Gọi API FaceNet để đăng ký khuôn mặt
            $faceNetResponse = $this->callFaceNetAPI('/facenet/capture-face', [
                'user_id' => $userId,
                'face_image' => $faceImage
            ]);

            if (!$faceNetResponse['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể đăng ký khuôn mặt. Vui lòng thử lại.',
                    'code' => 'FACE_REGISTRATION_FAILED'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $userId,
                    'message' => 'Đăng ký khuôn mặt thành công!'
                ],
                'message' => 'Đăng ký khuôn mặt thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Face registration error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra trong quá trình đăng ký khuôn mặt.',
                'code' => 'FACE_REGISTRATION_ERROR'
            ], 500);
        }
    }

    /**
     * Xóa khuôn mặt của user
     */
    public function deleteFace(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'user_id' => 'required|integer'
            ]);

            $userId = $request->input('user_id');

            // Gọi API FaceNet để xóa khuôn mặt
            $faceNetResponse = $this->callFaceNetAPI('/facenet/users/' . $userId, [], 'DELETE');

            if (!$faceNetResponse['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa khuôn mặt. Vui lòng thử lại.',
                    'code' => 'FACE_DELETION_FAILED'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $userId,
                    'message' => 'Xóa khuôn mặt thành công!'
                ],
                'message' => 'Xóa khuôn mặt thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Face deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra trong quá trình xóa khuôn mặt.',
                'code' => 'FACE_DELETION_ERROR'
            ], 500);
        }
    }

    /**
     * Lấy danh sách user đã đăng ký khuôn mặt
     */
    public function listRegisteredFaces(): JsonResponse
    {
        try {
            // Gọi API FaceNet để lấy danh sách
            $faceNetResponse = $this->callFaceNetAPI('/facenet/users', [], 'GET');

            if (!$faceNetResponse['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể lấy danh sách khuôn mặt.',
                    'code' => 'FACE_LIST_FAILED'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $faceNetResponse['users'],
                    'total' => $faceNetResponse['total']
                ],
                'message' => 'Lấy danh sách khuôn mặt thành công!'
            ]);

        } catch (\Exception $e) {
            Log::error('Face list error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách khuôn mặt.',
                'code' => 'FACE_LIST_ERROR'
            ], 500);
        }
    }

    /**
     * Gọi API FaceNet
     */
    private function callFaceNetAPI(string $endpoint, array $data = [], string $method = 'POST'): array
    {
        try {
            $url = config('services.face_auth.url', 'http://localhost:5000') . $endpoint;

            $ch = curl_init();

            if ($method === 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            } elseif ($method === 'DELETE') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            }

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);

            curl_close($ch);

            if ($error) {
                throw new \Exception('cURL Error: ' . $error);
            }

            if ($httpCode !== 200) {
                throw new \Exception('HTTP Error: ' . $httpCode);
            }

            $responseData = json_decode($response, true);
            return $responseData ?: ['success' => false, 'message' => 'Invalid response format'];

        } catch (\Exception $e) {
            Log::error('FaceNet API call error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
