<?php

use App\Http\Controllers\Admin\OrderPayment\OrderPaymentController;
use App\Http\Controllers\Admin\Role\RoleController;
use App\Http\Controllers\Admin\TableArea\TableAreaController;
use App\Http\Controllers\Admin\Table\TableController;
use App\Http\Controllers\Admin\Category\CategoryController;
use App\Http\Controllers\Admin\User\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\PermissionGroup\PermissionGroupController;
use App\Http\Controllers\Admin\Permission\PermissionController;
use App\Http\Controllers\Admin\UserRole\UserRoleController;
use App\Http\Controllers\Admin\RolePermission\RolePermissionController;
use App\Http\Controllers\Admin\Combo\ComboController;
use App\Http\Controllers\Admin\Customer\CustomerController;
use App\Http\Controllers\Admin\DiscountCode\DiscountCodeController;
use App\Http\Controllers\Admin\Dish\DishController;
use App\Http\Controllers\Admin\KitchenOrder\KitchenOrderController;
use App\Http\Controllers\Admin\Order\OrderController;
use App\Http\Controllers\Admin\Payment\PaymentController;
use App\Http\Controllers\Admin\Reservation\ReservationController;
use App\Http\Controllers\Admin\Auth\FaceAuthController;
use App\Http\Controllers\Admin\NotificationController\NotificationController;
use App\Http\Controllers\Admin\Statistics\StatisticsController;;

Route::prefix('admin')->group(function () {

    ##login admin
    Route::post('login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password/{id}', [AuthController::class, 'resetPassword']);

 
    

    // Face Authentication Routes
    Route::prefix('face-auth')->group(function () {
        Route::post('/login', [FaceAuthController::class, 'loginWithFaceNet']);
        Route::post('/register', [FaceAuthController::class, 'registerFace']);
        Route::delete('/delete/{user_id}', [FaceAuthController::class, 'deleteFace']);
        Route::get('/users', [FaceAuthController::class, 'listRegisteredFaces']);
    });

    Route::middleware(['auth:sanctum', 'check.status'])->group(function () {
        // Logout
        Route::post('logout', [AuthController::class, 'logout']);

        // # branchs
        Route::get('customers/list', [CustomerController::class, 'getListCustomers']);
        Route::get('customers/{id}/detail', [CustomerController::class, 'getCustomerDetail']);

        Route::post('customers/{id}/update', [CustomerController::class, 'updateCustomer']);
        Route::get('customers/trash', [CustomerController::class, 'listTrashedCustomer']);
        Route::delete('customers/{id}/soft/delete', [CustomerController::class, 'softDeleteCustomer']);
        Route::delete('customers/{id}/force/delete', [CustomerController::class, 'forceDeleteCustomer']);
        Route::post('customers/{id}/restore', [CustomerController::class, 'restoreCustomer']);
        Route::get('customers/count-by-status', [CustomerController::class, 'countByStatus']);

        ## categories
        Route::get('categories/list', [CategoryController::class, 'getListCategories']);
        Route::get('categories/{id}/detail', [CategoryController::class, 'getCategoryDetail']);
        Route::post('categories/create', [CategoryController::class, 'createCategory']);
        Route::post('categories/{id}/update', [CategoryController::class, 'updateCategory']);
        Route::get('categories/trash', [CategoryController::class, 'listTrashedCategory']);
        Route::delete('categories/{id}/soft/delete', [CategoryController::class, 'softDeleteCategory']);
        Route::delete('categories/{id}/force/delete', [CategoryController::class, 'forceDeleteCategory']);
        Route::post('categories/{id}/restore', [CategoryController::class, 'restoreCategory']);
        Route::get('categories/count-by-status', [CategoryController::class, 'countByStatus']);

        ##users
        Route::post('users/create', [UserController::class, 'createUser']);
        Route::post('users/{id}/update', [UserController::class, 'updateUser']);
        Route::get('users/list', [UserController::class, 'getListUser']);
        Route::post('users/{id}/delete', [UserController::class, 'deleteUser']);
        Route::post('users/{id}/block', [UserController::class, 'blockUser']);
        Route::post('users/{id}/unblock', [UserController::class, 'unblockUser']);
        Route::get('users/count-by-status', [UserController::class, 'countByStatus']);

        Route::post('users/change-password', [UserController::class, 'changePassword']);
        Route::get('users/{id}/detail', [UserController::class, 'getUserDetail']);



        ## table areas
        Route::get('table-areas/list', [TableAreaController::class, 'getListTableArea']);
        Route::get('table-areas/{id}/detail', [TableAreaController::class, 'getTableAreaDetail']);
        Route::post('table-areas/create', [TableAreaController::class, 'createTableArea']);
        Route::post('table-areas/{id}/update', [TableAreaController::class, 'updateTableArea']);
        Route::delete('table-areas/{id}/delete', [TableAreaController::class, 'destroy']);
        Route::get('table-areas/count-by-status', [TableAreaController::class, 'countByStatus']);

        ## tables
        Route::get('tables/list', [TableController::class, 'getListTables']);
        Route::get('tables/{id}/detail', [TableController::class, 'getTableDetail']);
        Route::post('tables/create', [TableController::class, 'createTable']);
        Route::post('tables/{id}/update', [TableController::class, 'updateTable']);
        Route::delete('tables/{id}/delete', [TableController::class, 'destroyTable']);
        Route::get('tables/count-by-status', [TableController::class, 'countByStatus']);
        Route::get('/tables/get-by-status', [TableController::class, 'getTablesByStatus']);

        ##resetpass
        // moved above; do not protect reset-password with auth

        ##Roles
        Route::post('roles/create', [RoleController::class, 'createRole']);
        Route::post('roles/{id}/update', [RoleController::class, 'updateRole']);
        Route::get('roles/list', [RoleController::class, 'getListRoles']);
        Route::post('roles/{id}/delete', [RoleController::class, 'deleteRole']);

        ##PermissionGroup
        Route::post('permission/groups/create', [PermissionGroupController::class, 'createPermissionGroup']);
        Route::post('permission/groups/{id}/update', [PermissionGroupController::class, 'updatePermissionGroup']);
        Route::get('permission/groups/list', [PermissionGroupController::class, 'getPermissionGroupLists']);
        Route::post('permission/groups/{id}/delete', [PermissionGroupController::class, 'deletePermissionGroup']);

        ##Permission
        Route::post('permissions/create', [PermissionController::class, 'createPermission']);
        Route::post('permissions/{id}/update', [PermissionController::class, 'updatePermission']);
        Route::get('permissions/list', [PermissionController::class, 'getPermissionLists']);
        Route::post('permissions/{id}/delete', [PermissionController::class, 'deletePermission']);

        ##UserRole
        Route::post('user/roles/create', [UserRoleController::class, 'createUserRole']);
        Route::post('user/roles/{id}/update', [UserRoleController::class, 'updateUserRole']);
        Route::get('user/roles/list', [UserRoleController::class, 'getUserRoleLists']);
        Route::post('user/roles/{id}/delete', [UserRoleController::class, 'deleteUserRole']);

        ##Role_permission
        Route::post('role/permissions/create', [RolePermissionController::class, 'createRolePermission']);
        Route::post('role/permissions/{id}/update', [RolePermissionController::class, 'updateRolePermission']);
        Route::get('role/permissions/list', [RolePermissionController::class, 'getRolePermissionList']);
        Route::post('role/permissions/{id}/delete', [RolePermissionController::class, 'deleteRolePermission']);


        ##order
        Route::get('orders/list', [OrderController::class, 'getListOrders']);
        Route::get('orders/change-logs', [OrderController::class, 'getAllOrderChangeLogs']);
        Route::get('orders/{id}/detail', [OrderController::class, 'getOrderDetail']);
        Route::post('orders/create', [OrderController::class, 'createOrder']);
        Route::post('orders/{id}/update', [OrderController::class, 'updateOrder']);
        Route::post('orders/items/{orderItemId}/status', [OrderController::class, 'updateItemStatus']);
        Route::get('orders/trash', [OrderController::class, 'listTrashedOrders']);
        Route::delete('orders/{id}/soft/delete', [OrderController::class, 'softDeleteOrder']);
        Route::delete('orders/{id}/force/delete', [OrderController::class, 'forceDeleteOrder']);
        Route::post('orders/{id}/restore', [OrderController::class, 'restoreOrder']);
        Route::get('orders/count-by-status', [OrderController::class, 'countByStatus']);
        Route::get('orders/table/{tableId}', [OrderController::class, 'getOrderByTableId']);


        // dishes
        Route::get('dishes/list', [DishController::class, 'getListDishes']);
        Route::get('dishes/{id}/detail', [DishController::class, 'getDishDetail']);
        Route::get('dishes/category/{id}', [DishController::class, 'getDishesByCategory']);
        Route::post('dishes/create', [DishController::class, 'createDish']);
        Route::post('dishes/{id}/update', [DishController::class, 'updateDish']);
        Route::get('dishes/trash', [DishController::class, 'listTrashedDish']);
        Route::delete('dishes/{id}/soft/delete', [DishController::class, 'softDeleteDish']);
        Route::delete('dishes/{id}/force/delete', [DishController::class, 'forceDeleteDish']);
        Route::post('dishes/{id}/restore', [DishController::class, 'restoreDish']);
        Route::get('dishes/count-by-status', [DishController::class, 'countByStatus']);
        Route::post('dishes/{id}/update-featured', [DishController::class, 'updateFeaturedDish']);

        // combos
        Route::get('combos/list', [ComboController::class, 'getListCombos']);
        Route::get('combos/{id}/detail', [ComboController::class, 'getComboDetail']);
        Route::post('combos/create', [ComboController::class, 'createCombo']);
        Route::post('combos/{id}/update', [ComboController::class, 'updateCombo']);
        Route::get('combos/trash', [ComboController::class, 'listTrashedCombo']);
        Route::delete('combos/{id}/soft/delete', [ComboController::class, 'softDeleteCombo']);
        Route::delete('combos/{id}/force/delete', [ComboController::class, 'forceDeleteCombo']);
        Route::post('combos/{id}/restore', [ComboController::class, 'restoreCombo']);
        Route::get('combos/count-by-status', [ComboController::class, 'countByStatus']);
        Route::post('combos/{id}/update-status', [ComboController::class, 'updateStatusCombo']);

        Route::post('combos/{id}/add-items', [ComboController::class, 'addItemToCombo']);
        Route::post('combos/{comboId}/{dishId}/update-quantity', [ComboController::class, 'updateItemQuantity']);

        // Reservation
        Route::get('reservations/list', [ReservationController::class, 'getListReservations']);
        Route::get('reservations/{id}/detail', [ReservationController::class, 'getReservationDetail']);
        Route::post('reservations/create', [ReservationController::class, 'createReservation']);
        Route::post('reservations/{id}/update', [ReservationController::class, 'updateReservation']);
        Route::get('reservations/trash', [ReservationController::class, 'listTrashedReservation']);
        Route::delete('reservations/{id}/soft/delete', [ReservationController::class, 'softDeleteReservation']);
        Route::delete('reservations/{id}/force/delete', [ReservationController::class, 'forceDeleteReservation']);
        Route::post('reservations/{id}/restore', [ReservationController::class, 'restoreReservation']);
        Route::post('reservations/{id}/confirm', [ReservationController::class, 'confirmReservation']);
        Route::get('reservations/count-by-status', [ReservationController::class, 'countByStatus']);
        Route::get('reservations/{id}/change-logs', [ReservationController::class, 'getChangeLogs']);

        // Kitchen Order
        Route::get('kitchen-orders/list', [KitchenOrderController::class, 'getListKitchenOrders']);
        Route::post('kitchen-orders/{id}/update-status', [KitchenOrderController::class, 'updateKitchenOrderStatus']);

       
        Route::get('kitchen-orders/count-by-status', [KitchenOrderController::class, 'countByStatus']);

        // Lịch sử thay đổi đơn hàng
        Route::get('orders/{id}/change-logs', [OrderController::class, 'getOrderChangeLogs']);

        // Order Payment
        Route::post('orders/{id}/pay', [PaymentController::class, 'payment']);
        Route::get('bills/{id}/detail', [PaymentController::class, 'getBillDetailForOrder']);

        // Notification
        Route::get('notifications/list', [NotificationController::class, 'getList']);
        Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead']);

        // Discount Codes
        Route::get('discount-codes/list', [DiscountCodeController::class, 'getListDiscountCodes']);
        Route::post('discount-codes/create', [DiscountCodeController::class, 'createDiscountCode']);
        Route::post('discount-codes/{id}/update', [DiscountCodeController::class, 'updateDiscountCode']);
        Route::delete('discount-codes/{id}/delete', [DiscountCodeController::class, 'deleteDiscountCode']);
        Route::get('discount-codes/count-by-status', [DiscountCodeController::class, 'countByStatus']);

        ## thống kê
        Route::get('statistics/reservations/status-count', [StatisticsController::class, 'getReservationStatusStats']);
        Route::get('statistics/reservations/time-count', [StatisticsController::class, 'getReservationTimeStats']);
        Route::get('statistics/orders/revenue', [StatisticsController::class, 'getOrderRevenueStats']);

    });
    Route::middleware(['auth:sanctum', 'check.status'])->group(function () {
        // ... protected routes ...
    });

    // VnPay/Momo returns
    Route::get('/vnpay-return', [PaymentController::class, 'vnpayReturn']);
    Route::get('/momo-return', [PaymentController::class, 'momoReturn']);

    // Face Recognition - PUBLIC (cho login)
    Route::post('face/recognize', [App\Http\Controllers\Admin\FaceRecognitionController::class, 'recognizeFace'])
        ->withoutMiddleware(['auth:sanctum', 'check.status'])
        ->name('admin.face.recognize');

    Route::options('face/recognize', function () {
        return response()->noContent(204)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    })->withoutMiddleware(['auth:sanctum', 'check.status']);

    // Face Recognition - Protected endpoints
    Route::middleware(['auth:sanctum', 'check.status'])->prefix('face')->group(function () {
        Route::post('capture', [App\Http\Controllers\Admin\FaceRecognitionController::class, 'captureface']);
        Route::post('train', [App\Http\Controllers\Admin\FaceRecognitionController::class, 'trainFaces']);
        Route::get('users', [App\Http\Controllers\Admin\FaceRecognitionController::class, 'getRegisteredUsers']);
        Route::delete('users/{userId}', [App\Http\Controllers\Admin\FaceRecognitionController::class, 'deleteUserFace']);
        Route::get('statistics', [App\Http\Controllers\Admin\FaceRecognitionController::class, 'getStatistics']);
        Route::get('check-connection', [App\Http\Controllers\Admin\FaceRecognitionController::class, 'checkApiConnection']);
    });
});