<?php

use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\TableAreaController;
use App\Http\Controllers\Admin\TableAreaTemplateController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    // # branchs
    Route::get('branches/list', [BranchController::class, 'getListBranchs']);
    Route::get('branches/{slug}/detail', [BranchController::class, 'getBranchDetail']);
    Route::post('branches/create', [BranchController::class, 'createBranch']);
    Route::post('branches/{slug}/update', [BranchController::class, 'updateBranch']);
    Route::get('branches/trash', [BranchController::class, 'listTrashedBranch']);
    Route::delete('branches/{slug}/soft/delete', [BranchController::class, 'softDeleteBranch']);
    Route::delete('branches/{slug}/force/delete', [BranchController::class, 'forceDeleteBranch']);
    Route::post('branches/{slug}/restore', [BranchController::class, 'restoreBranch']);

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
});
