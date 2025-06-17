<?php

use App\Http\Controllers\Admin\Role\RoleController;
use App\Http\Controllers\Admin\TableAreaController;
use App\Http\Controllers\Admin\TableAreaTemplateController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\Customer\CustomerController;
use App\Http\Controllers\Admin\User\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\PermissionGroup\PermissionGroupController;
use App\Http\Controllers\Admin\Permission\PermissionController;
use App\Http\Controllers\Admin\UserRole\UserRoleController;
use App\Http\Controllers\Admin\RolePermission\RolePermissionController;

Route::prefix('admin')->group(function () {
    // # branchs
    Route::get('customers/list', [CustomerController::class, 'getListCustomers']);
    Route::get('customers/{id}/detail', [CustomerController::class, 'getCustomerDetail']);
    Route::post('customers/create', [CustomerController::class, 'createCustomer']);
    Route::post('customers/{id}/update', [CustomerController::class, 'updateCustomer']);
    Route::get('customers/trash', [CustomerController::class, 'listTrashedCustomer']);
    Route::delete('customers/{id}/soft/delete', [CustomerController::class, 'softDeleteCustomer']);
    Route::delete('customers/{id}/force/delete', [CustomerController::class, 'forceDeleteCustomer']);
    Route::post('customers/{id}/restore', [CustomerController::class, 'restoreCustomer']);

    // # table areas templates
    Route::get('table-areas-templates/list', [TableAreaTemplateController::class, 'getListTableAreasTemplate']);
    Route::get('table-areas-templates/{slug}/detail', [TableAreaTemplateController::class, 'getTableAreaTemplateDetail']);
    Route::post('table-areas-templates/create', [TableAreaTemplateController::class, 'createTableAreaTemplate']);
    Route::post('table-areas-templates/{slug}/update', [TableAreaTemplateController::class, 'updateTableAreaTemplate']);
    Route::delete('table-areas-templates/{slug}/delete', [TableAreaTemplateController::class, 'deleteTableAreaTemplate']);

    // # table areas
    Route::get('table-areas/list/{branchId}', [TableAreaController::class, 'getTableAreasByBranch']);
    Route::post('table-areas/create/{branchId}', [TableAreaController::class, 'createTableAreaForBranch']);
    Route::post('table-areas/{slug}/update/{branchId}', [TableAreaController::class, 'updateTableAreaForBranch']);
    Route::post('table-areas/create-for-all-branches', [TableAreaController::class, 'createTableAreaForAllBranches']);
    Route::get('table-areas/{slug}/detail', [TableAreaController::class, 'getTableAreaDetail']);
    Route::delete('table-areas/{slug}/delete/{branchId}', [TableAreaController::class, 'deleteTableAreaForBranch']);
    ## categories
    Route::get('categories/list', [CategoryController::class, 'getListCategories']);
    Route::get('categories/{slug}/detail', [CategoryController::class, 'getCategoryDetail']);
    Route::post('categories/create', [CategoryController::class, 'createCategory']);
    Route::post('categories/{slug}/update', [CategoryController::class, 'updateCategory']);
    Route::get('categories/trash', [CategoryController::class, 'listTrashedCategory']);
    Route::delete('categories/{slug}/soft/delete', [CategoryController::class, 'softDeleteCategory']);
    Route::delete('categories/{slug}/force/delete', [CategoryController::class, 'forceDeleteCategory']);
    Route::post('categories/{slug}/restore', [CategoryController::class, 'restoreCategory']);

    ##users
    Route::post('users/create', [UserController::class, 'createUser']);
    Route::post('users/{id}/update', [UserController::class, 'updateUser']);
    Route::get('users/list', [UserController::class, 'getListUser']);
    Route::post('users/{id}/delete', [UserController::class, 'deleteUser']);
    Route::post('users/{id}/block', [UserController::class, 'blockUser']);
    Route::post('users/{id}/unblock', [UserController::class, 'unblockUser']);

    ##resetpass
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password/{id}', [AuthController::class, 'resetPassword']);

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
    Route::post('permission/groups/{id}/restore', [PermissionGroupController::class, 'restorePermissionGroup']);

    ##Permission
    Route::post('permissions/create', [PermissionController::class, 'createPermission']);
    Route::post('permissions/{id}/update', [PermissionController::class, 'updatePermission']);
    Route::get('permissions/list', [PermissionController::class, 'getPermissionLists']);
    Route::post('permissions/{id}/delete', [PermissionController::class, 'deletePermission']);
    Route::post('permissions/{id}/restore', [PermissionController::class, 'restorePermission']);

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

});
