<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FaceRecognition\FaceRecognitionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
            'user_info.role' => 'sometimes|in:Admin,Quản lý bếp,Nhân viên'
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
        if ($result['success'] && $result['accuracy'] >= 10) {
            $userInfo = $result['user_info'] ?? [];
            $recognizedUserId = $result['user_id'] ?? null; // có thể là mã ngoài (user_id)

            $user = null;
            // Ưu tiên KHỚP THEO user_info.id nếu dự án quy ước id này là users.id
            if (isset($userInfo['id'])) {
                $user = User::find($userInfo['id']);
            }
            // Nếu chưa khớp được, thử theo email
            if (!$user && isset($userInfo['email'])) {
                $user = User::where('email', $userInfo['email'])->first();
            }
            // Nếu chưa có, thử theo username nếu có
            if (!$user && isset($userInfo['username'])) {
                $user = User::where('username', $userInfo['username'])->first();
            }
            // Nếu vẫn chưa có và có recognizedUserId, thử map theo cột users.user_id nếu tồn tại
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
                    // Cấp đầy đủ abilities cho token để tương thích middleware kiểm tra abilities
                    $token = $user->createToken('admin-token', ['*'])->plainTextToken;
                    $result['token'] = $token;
                    $result['login_success'] = true;
                    // Chuẩn hóa user_info trả về: LUÔN dùng users.id cho key 'id'
                    // Đảm bảo đã load quan hệ roles/permissions (nếu có)
                    try { $user->loadMissing(['roles.permissions']); } catch (\Throwable $e) {}
                    // Lấy roles/permissions an toàn, hỗ trợ cả Spatie methods và Eloquent relations
                    $roleNames = [];
                    $permissionNames = [];
                    try {
                        if (method_exists($user, 'getRoleNames')) {
                            $roleNames = $user->getRoleNames()->toArray();
                        } elseif (isset($user->roles)) {
                            $roleNames = $user->roles->pluck('name')->toArray();
                        }
                    } catch (\Throwable $e) {}
                    try {
                        if (method_exists($user, 'getAllPermissions')) {
                            $permissionNames = $user->getAllPermissions()->pluck('name')->toArray();
                        } elseif (isset($user->permissions)) {
                            $permissionNames = $user->permissions->pluck('name')->toArray();
                        }
                    } catch (\Throwable $e) {}
                    // Loại null/ trùng lặp
                    $roleNames = array_values(array_filter(array_unique($roleNames), function($v){ return !is_null($v) && $v !== ''; }));
                    $permissionNames = array_values(array_filter(array_unique($permissionNames), function($v){ return !is_null($v) && $v !== ''; }));

                    // Dùng role_name cũ từ payload nếu không lấy được từ hệ thống role
                    $existingInfo = [];
                    if (!empty($result['user_info']) && is_array($result['user_info'])) {
                        $existingInfo = $result['user_info'];
                    }
                    // Nếu roleNames đang rỗng nhưng payload có role_name -> suy ra roles và tìm permissions theo Role
                    $roleNameFromPayload = $existingInfo['role_name'] ?? null;
                    if ((empty($roleNames) || count($roleNames) === 0) && !empty($roleNameFromPayload)) {
                        try {
                            $roleModel = Role::where('name', $roleNameFromPayload)->first();
                            if ($roleModel) {
                                $roleNames = [$roleModel->name];
                                if (empty($permissionNames)) {
                                    $permissionNames = $roleModel->permissions()->pluck('name')->toArray();
                                }
                            } else {
                                // Không tìm thấy Role trong DB, vẫn set roles theo payload để frontend điều hướng cơ bản
                                $roleNames = [$roleNameFromPayload];
                            }
                        } catch (\Throwable $e) {
                            $roleNames = [$roleNameFromPayload];
                        }
                        // Làm sạch một lần nữa
                        $roleNames = array_values(array_filter(array_unique($roleNames), function($v){ return !is_null($v) && $v !== ''; }));
                        $permissionNames = array_values(array_filter(array_unique($permissionNames), function($v){ return !is_null($v) && $v !== ''; }));
                    }

                    // Nếu vẫn chưa có permissions và đã biết các role của user -> gom permissions từ tất cả role đó
                    if (empty($permissionNames) && !empty($roleNames)) {
                        try {
                            $all = [];
                            foreach ($roleNames as $rName) {
                                $r = Role::where('name', $rName)->first();
                                if ($r) {
                                    $names = $r->permissions()->pluck('name')->toArray();
                                    if ($names) { $all = array_merge($all, $names); }
                                }
                            }
                            if (!empty($all)) {
                                $permissionNames = $all;
                            }
                        } catch (\Throwable $e) {}
                        $permissionNames = array_values(array_filter(array_unique($permissionNames), function($v){ return !is_null($v) && $v !== ''; }));
                    }

                    // Nếu permissions vẫn rỗng -> gán mặc định theo role yêu cầu của hệ thống
                    if (empty($permissionNames)) {
                        $roleResolved = $roleNames[0] ?? ($existingInfo['role_name'] ?? ($existingInfo['role'] ?? ''));
                        $roleResolvedLower = mb_strtolower($roleResolved);
                        try {
                            if ($roleResolvedLower === 'admin' || $roleResolvedLower === 'Admin' ||$roleResolvedLower === 'administrator' || $roleResolvedLower === 'quản trị') {
                                // Admin có tất cả quyền
                                $permissionNames = [
                                    'dashboard.view',
                                    'user.view', 'user.create', 'user.update', 'user.delete',
                                    'role.view', 'role.create', 'role.update', 'role.delete',
                                    'permission.view', 'permission.create', 'permission.update', 'permission.delete',
                                    'category.view', 'category.create', 'category.update', 'category.delete',
                                    'dish.view', 'dish.create', 'dish.update', 'dish.delete',
                                    'combo.view', 'combo.create', 'combo.update', 'combo.delete',
                                    'order.view', 'order.create', 'order.update', 'order.delete',
                                    'order.item-status.update',
                                    'reservation.view', 'reservation.create', 'reservation.update', 'reservation.delete',
                                    'reservation.confirm',
                                    'table.view', 'table.create', 'table.update', 'table.delete',
                                    'table-area.view', 'table-area.create', 'table-area.update', 'table-area.delete',
                                    'customer.view', 'customer.create', 'customer.update', 'customer.delete',
                                    'kitchen.order.view', 'kitchen.order.update',
                                    'report.view', 'setting.view', 'setting.update'
                                ];
                            } elseif ($roleResolvedLower === 'nhân viên' || $roleResolvedLower === 'nhan vien' || $roleResolvedLower === 'staff'|| $roleResolvedLower === 'Nhân viên' ) {
                                $permissionNames = [
                                    'dashboard.view',
                                    'category.view',
                                    'dish.view',
                                    'combo.view',
                                    'order.view',
                                    'order.create',
                                    'order.update',
                                    'order.item-status.update',
                                    'reservation.view',
                                    'reservation.create',
                                    'reservation.update',
                                    'reservation.confirm',
                                    'table.view',
                                    'table-area.view',
                                    'dashboard.view',
                                    'customer.view',
                                ];
                            } elseif ($roleResolvedLower === 'bếp' || $roleResolvedLower === 'Quản lý bếp' || $roleResolvedLower === 'bep' || $roleResolvedLower === 'kitchen') {
                                // Quyền cho nhân viên bếp
                                $permissionNames = [
                                    'dashboard.view',
                                    'kitchen.order.view',
                                    'kitchen.order.update',
                                    'order.view',
                                    'order.item-status.update'
                                ];
                            }
                        } catch (\Throwable $e) {}
                        $permissionNames = array_values(array_filter(array_unique($permissionNames), function($v){ return !is_null($v) && $v !== ''; }));
                        // Persist vào DB để middleware permission không 403
                        try {
                            // Buộc dùng guard 'web' cho Spatie (chuẩn phổ biến)
                            $guard = config('permission.defaults.guard', 'web');
                            // Đảm bảo permission tồn tại
                            foreach ($permissionNames as $pName) {
                                if (!Permission::where('name', $pName)->where('guard_name', $guard)->exists()) {
                                    Permission::create(['name' => $pName, 'guard_name' => $guard]);
                                }
                            }
                            // Nếu có role thì gán vào role, nếu không thì gán trực tiếp cho user
                            if (!empty($roleNames)) {
                                foreach ($roleNames as $rName) {
                                    $role = Role::where('name', $rName)->where('guard_name', $guard)->first();
                                    if ($role) {
                                        $role->syncPermissions($permissionNames);
                                    }
                                }
                            } else {
                                $user->syncPermissions($permissionNames);
                            }
                            // Reload permission từ DB để đảm bảo nhất quán
                            $permissionNames = [];
                            try {
                                if (method_exists($user, 'getAllPermissions')) {
                                    $permissionNames = $user->getAllPermissions()->pluck('name')->toArray();
                                }
                            } catch (\Throwable $e2) {}
                        } catch (\Throwable $e) {}
                    }
                    $normalized = [
                        'id' => $user->id,
                        'user_id' => $user->user_id ?? $user->id,
                        'email' => $user->email,
                        'full_name' => $user->full_name ?? ($user->name ?? ''),
                        'username' => $user->username ?? null,
                        // Trả về role dạng chuỗi, không dùng mảng
                        'role' => $roleNames[0] ?? ($existingInfo['role_name'] ?? ''),
                        'role_name' => $roleNames[0] ?? ($existingInfo['role_name'] ?? ''),
                        'permissions' => $permissionNames,
                        'is_trained' => true,
                    ];
                    if (!empty($result['user_info']) && is_array($result['user_info'])) {
                        // Gộp thông tin nhưng ép id, email... theo $normalized
                        $result['user_info'] = array_merge($result['user_info'], $normalized);
                    } else {
                        $result['user_info'] = $normalized;
                    }

                    // Đảm bảo user có Role trong DB nếu nhận được role_name/role từ nhận diện
                    try {
                        $guard = method_exists($user, 'getDefaultGuardName') ? $user->getDefaultGuardName() : config('auth.defaults.guard', 'web');
                        $existingRoleNames = [];
                        try { if (method_exists($user, 'getRoleNames')) { $existingRoleNames = $user->getRoleNames()->toArray(); } } catch (\Throwable $e) {}
                        $fallbackRoleName = $result['user_info']['role_name'] ?? ($result['user_info']['role'] ?? null);
                        if ((empty($existingRoleNames) || !in_array($fallbackRoleName, $existingRoleNames, true)) && !empty($fallbackRoleName)) {
                            $lower = mb_strtolower($fallbackRoleName);
                            // Chuẩn hóa tên role theo hệ thống
                            if (in_array($lower, ['admin','administrator','quản trị','quan tri', 'Admin'])) { $fallbackRoleName = 'admin'; }
                            elseif (in_array($lower, ['nhân viên','nhan vien','staff', "Nhân viên"])) { $fallbackRoleName = 'nhân viên'; }
                            elseif (in_array($lower, ['bếp','bep','kitchen', 'Quản lý bếp'])) { $fallbackRoleName = 'bếp'; }

                            $roleModel = Role::where('name', $fallbackRoleName)->where('guard_name', $guard)->first();
                            if (!$roleModel) {
                                $roleModel = Role::create(['name' => $fallbackRoleName, 'guard_name' => $guard]);
                            }
                            if ($roleModel && (method_exists($user, 'assignRole'))) {
                                try { $user->assignRole($roleModel); } catch (\Throwable $e) {}
                                try { $user->load('roles'); } catch (\Throwable $e) {}
                            }
                        }
                    } catch (\Throwable $e) {}

                    // Chuẩn hóa cấu trúc response giống đăng nhập mật khẩu
                    // Tạo danh sách role object [{id, name}]
                    $roleObjects = [];
                    try {
                        if (isset($user->roles)) {
                            $roleObjects = $user->roles->map(function($r){
                                return ['id' => $r->id ?? null, 'name' => $r->name ?? null];
                            })->filter(function($r){ return !empty($r['name']); })->values()->toArray();
                        }
                    } catch (\Throwable $e) {}
                    if (empty($roleObjects)) {
                        $fallbackRoleName = $result['user_info']['role_name'] ?? ($result['user_info']['role'] ?? null);
                        if (!empty($fallbackRoleName)) {
                            // Sau khi đã cố gắng gán role ở trên, thử lấy lại từ DB lần nữa
                            try {
                                if (isset($user->roles) && $user->roles->count() > 0) {
                                    $roleObjects = $user->roles->map(function($r){
                                        return ['id' => $r->id ?? null, 'name' => $r->name ?? null];
                                    })->filter(function($r){ return !empty($r['name']); })->values()->toArray();
                                }
                            } catch (\Throwable $e) {}
                            if (empty($roleObjects)) {
                                $roleObjects = [['id' => null, 'name' => $fallbackRoleName]];
                            }
                        }
                    }

                    $userPayload = [
                        'id' => $user->id,
                        'username' => $user->username ?? null,
                        'email' => $user->email,
                        'full_name' => $user->full_name ?? ($user->name ?? null),
                        'roles' => $roleObjects,
                        'permissions' => $permissionNames,
                    ];

                    // Gán các field theo format chung
                    $result['code'] = 'SUCCESS';
                    $result['success'] = true;
                    $result['login_success'] = true;
                    $result['message'] = 'Đăng nhập thành công!';
                    $result['data'] = [
                        'user' => $userPayload,
                        // giữ accuracy nếu có để FE hiển thị
                        'accuracy' => $result['accuracy'] ?? null,
                        'token' => $token,
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
