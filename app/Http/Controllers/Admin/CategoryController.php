<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest\StoreCategoryRequest;
use App\Http\Requests\CategoryRequest\UpdateCategoryRequest;
use App\Repositories\Categories\CategoryRepositoryInterface;
use App\Services\Categories\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected CategoryService $categoryService;
    protected CategoryRepositoryInterface $categoryRepository;
    public function __construct(
        CategoryService $categoryService,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->categoryService = $categoryService;
        $this->categoryRepository = $categoryRepository;
    }
    public function getListCategories(Request $request)
    {
        $params = $request->only(
            'page',
            'limit',
            'query',
            'name',
            'slug',
            'image_url',
            'description',
            'is_active',
            'parent_id',
        );
        $result = $this->categoryService->getListCategories($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }
    public function createCategory(StoreCategoryRequest $request)
    {
        $data = $request->only([
            'name',
            'slug',
            'image_url',
            'description',
            'is_active',
            'parent_id',
        ]);

        $result = $this->categoryService->createCategory($data);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function getCategoryDetail(string $slug)
    {
        $result = $this->categoryService->getCategoryDetail($slug);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }
        $data = $result->getData();
        return $this->responseSuccess($data);
    }
    public function updateCategory(UpdateCategoryRequest $request, string $slug)
    {
        $data = $request->only([
            'name',
            'slug',
            'image_url',
            'description',    
            'is_active',        
            'parent_id',       
        ]);

        $category = $this->categoryRepository->getByConditions(['slug' => $slug]);
        if (!$category) {
            return $this->responseFail(message: 'Danh mục không tồn tại', statusCode: 404);
        }

        $result = $this->categoryService->updateCategory($data, $category);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function listTrashedCategory(Request $request)
    {
        $params = $request->only(
            'page',
            'limit',
            'query',
            'name',
            'slug',
            'image_url',
            'description',
            'is_active',
            'parent_id'
        );
        $result = $this->categoryService->listTrashedCategory($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }
    public function softDeleteCategory(string $slug)
    {
        $category = $this->categoryRepository->getByConditions(['slug' => $slug]);
        if (!$category) {
            return $this->responseFail(message: 'Danh mục không tồn tại', statusCode: 404);
        }
        $result = $this->categoryService->softDeleteCategory($slug);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function forceDeleteCategory($slug)
    {
        $result = $this->categoryService->forceDeleteCategory($slug);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function restoreCategory($slug)
    {
        $result = $this->categoryService->restoreCategory($slug);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
}
