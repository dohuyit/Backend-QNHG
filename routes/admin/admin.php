<?php

use App\Http\Controllers\Admin\BranchController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    ## branchs
    Route::get('branches/list', [BranchController::class, 'getListBranchs']);
    Route::get('branches/{slug}/detail', [BranchController::class, 'getBranchDetail']);
    Route::post('branches/create', [BranchController::class, 'createBranch']);
    Route::post('branches/{slug}/update', [BranchController::class, 'updateBranch']);
    Route::get('branches/trash', [BranchController::class, 'listTrashedBranch']);
    Route::delete('branches/{slug}/soft/delete', [BranchController::class, 'softDeleteBranch']);
    Route::delete('branches/{slug}/force/delete', [BranchController::class, 'forceDeleteBranch']);
    Route::post('branches/{slug}/restore', [BranchController::class, 'restoreBranch']);
});
