<?php

namespace App\Services\FaceRecognition;

use App\Models\AdminFace;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FaceRecognitionService
{
    private $pythonApiUrl;

    public function __construct()
    {
        $this->pythonApiUrl = config('services.face_recognition.api_url', 'http://localhost:5000');
    }

    /**
     * Chụp và lưu ảnh khuôn mặt
     */
    public function captureface($userId, $imageData, $imageCount, $userInfo = [])
    {
        try {
            $response = Http::timeout(30)->post($this->pythonApiUrl . '/api/face/capture', [
                'user_id' => $userId,
                'image' => $imageData,
                'image_count' => $imageCount,
                'user_info' => $userInfo
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Cập nhật database nếu cần
                if ($imageCount == 1 && !empty($userInfo)) {
                    AdminFace::updateOrCreate(
                        ['user_id' => $userId],
                        [
                            'email' => $userInfo['email'] ?? '',
                            'full_name' => $userInfo['full_name'] ?? '',
                            'role_name' => $userInfo['role'] ?? 'nhân viên',
                            'is_trained' => false
                        ]
                    );
                }

                return [
                    'success' => true,
                    'data' => $data,
                    'message' => $data['message'] ?? 'Chụp ảnh thành công'
                ];
            }

            return [
                'success' => false,
                'message' => 'Lỗi kết nối với Python API'
            ];
        } catch (Exception $e) {
            Log::error('Face capture error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Training model khuôn mặt
     */
    public function trainFaces($userId = null)
    {
        try {
            $response = Http::timeout(60)->post($this->pythonApiUrl . '/api/face/train', [
                'user_id' => $userId
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Cập nhật trạng thái trained trong database
                if ($data['success'] && $userId) {
                    AdminFace::where('user_id', $userId)->update(['is_trained' => true]);
                }

                return [
                    'success' => $data['success'],
                    'message' => $data['message']
                ];
            }

            return [
                'success' => false,
                'message' => 'Lỗi kết nối với Python API'
            ];
        } catch (Exception $e) {
            Log::error('Face training error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi training: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Nhận diện khuôn mặt
     */
    public function recognizeFace($imageData)
    {
        try {
            $response = Http::timeout(30)->post($this->pythonApiUrl . '/api/face/recognize', [
                'image' => $imageData
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['success']) {
                    // Lấy thông tin chi tiết từ database
                    $adminFace = AdminFace::where('user_id', $data['user_id'])->first();
                    
                    return [
                        'success' => true,
                        'user_id' => $data['user_id'],
                        'accuracy' => $data['accuracy'],
                        'user_info' => $adminFace ? [
                            'id' => $adminFace->id,
                            'user_id' => $adminFace->user_id,
                            'email' => $adminFace->email,
                            'full_name' => $adminFace->full_name,
                            'role_name' => $adminFace->role_name,
                            'is_trained' => $adminFace->is_trained
                        ] : null,
                        'message' => $data['message']
                    ];
                }

                return [
                    'success' => false,
                    'message' => $data['message'],
                    'accuracy' => $data['accuracy'] ?? 0
                ];
            }

            return [
                'success' => false,
                'message' => 'Lỗi kết nối với Python API'
            ];
        } catch (Exception $e) {
            Log::error('Face recognition error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi nhận diện: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lấy danh sách users đã đăng ký
     */
    public function getRegisteredUsers()
    {
        try {
            $users = AdminFace::orderBy('created_at', 'desc')->get();
            
            return [
                'success' => true,
                'data' => $users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'user_id' => $user->user_id,
                        'email' => $user->email,
                        'full_name' => $user->full_name,
                        'role_name' => $user->role_name,
                        'is_trained' => $user->is_trained,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at
                    ];
                })
            ];
        } catch (Exception $e) {
            Log::error('Get registered users error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi lấy danh sách: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Xóa dữ liệu khuôn mặt user
     */
    public function deleteUserFace($userId)
    {
        try {
            // Gọi API Python để xóa dataset
            $response = Http::timeout(30)->delete($this->pythonApiUrl . "/api/face/delete/{$userId}");
            
            // Xóa từ database
            AdminFace::where('user_id', $userId)->delete();

            return [
                'success' => true,
                'message' => 'Đã xóa dữ liệu khuôn mặt'
            ];
        } catch (Exception $e) {
            Log::error('Delete user face error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi xóa dữ liệu: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Kiểm tra kết nối với Python API
     */
    public function checkApiConnection()
    {
        try {
            $response = Http::timeout(5)->get($this->pythonApiUrl . '/api/face/users');
            return $response->successful();
        } catch (Exception $e) {
            Log::error('API connection check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy thống kê
     */
    public function getStatistics()
    {
        try {
            $total = AdminFace::count();
            $trained = AdminFace::where('is_trained', true)->count();
            $byRole = AdminFace::selectRaw('role_name, COUNT(*) as count')
                ->groupBy('role_name')
                ->pluck('count', 'role_name')
                ->toArray();

            return [
                'success' => true,
                'data' => [
                    'total_users' => $total,
                    'trained_users' => $trained,
                    'untrained_users' => $total - $trained,
                    'by_role' => $byRole,
                    'api_connected' => $this->checkApiConnection()
                ]
            ];
        } catch (Exception $e) {
            Log::error('Get statistics error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Lỗi lấy thống kê: ' . $e->getMessage()
            ];
        }
    }
}
