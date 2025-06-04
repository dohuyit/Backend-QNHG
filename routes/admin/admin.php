<?php

use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\TableAreaController;
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

    // # table areas
    Route::get('table-areas/list', [TableAreaController::class, 'getListTableAreas']);
    Route::get('table-areas/{slug}/detail', [TableAreaController::class, 'getTableAreaDetail']);
    Route::post('table-areas/create', [TableAreaController::class, 'createTableArea']);
    Route::post('table-areas/{slug}/update', [TableAreaController::class, 'updateTableArea']);
});
