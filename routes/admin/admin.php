<?php

use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ComboController;
use App\Http\Controllers\Admin\DishController;
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

    ## categories
    Route::get('categories/list', [CategoryController::class, 'getListCategories']);
    Route::get('categories/{slug}/detail', [CategoryController::class, 'getCategoryDetail']);
    Route::post('categories/create', [CategoryController::class, 'createCategory']);
    Route::post('categories/{slug}/update', [CategoryController::class, 'updateCategory']);
    Route::get('categories/trash', [CategoryController::class, 'listTrashedCategory']);
    Route::delete('categories/{slug}/soft/delete', [CategoryController::class, 'softDeleteCategory']);
    Route::delete('categories/{slug}/force/delete', [CategoryController::class, 'forceDeleteCategory']);
    Route::post('categories/{slug}/restore', [CategoryController::class, 'restoreCategory']);

    ## dishes
    Route::get('dishes/list', [DishController::class, 'getListDishes']);
    Route::get('dishes/{slug}/detail', [DishController::class, 'getDishDetail']); 
    Route::get('dishes/category/{slug}', [DishController::class, 'getDishesByCategory']);
    Route::post('dishes/create', [DishController::class, 'createDish']); 
    Route::post('dishes/{slug}/update', [DishController::class, 'updateDish']);
    Route::get('dishes/trash', [DishController::class, 'listTrashedDish']);
    Route::delete('dishes/{slug}/soft/delete', [DishController::class, 'softDeleteDish']);
    Route::delete('dishes/{slug}/force/delete', [DishController::class, 'forceDeleteDish']);
    Route::post('dishes/{slug}/restore', [DishController::class, 'restoreDish']);

    // Combos
    Route::get('combos/list', [ComboController::class, 'getListCombos']);
    Route::get('combos/{slug}/detail', [ComboController::class, 'getComboDetail']);
    Route::post('combos/create', [ComboController::class, 'createCombo']);
    Route::post('combos/{slug}/update', [ComboController::class, 'updateCombo']);
    Route::get('combos/trash', [ComboController::class, 'listTrashedCombo']);
    Route::delete('combos/{slug}/soft/delete', [ComboController::class, 'softDeleteCombo']);
    Route::delete('combos/{slug}/force/delete', [ComboController::class, 'forceDeleteCombo']);
    Route::post('combos/{slug}/restore', [ComboController::class, 'restoreCombo']);
    Route::post('combos/{slug}/add-item', [ComboController::class, 'addItemToCombo']);
    Route::post('combos/{slug}/remove-item', [ComboController::class, 'removeItemFromCombo']);
});
