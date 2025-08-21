<?php

namespace App\Http\Controllers\Admin\Category;

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
            'image_url',
            'description',
            'is_active',
            'parent_id',
            'cooking_time',
        ]);

        $result = $this->categoryService->createCategory($data);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function getCategoryDetail(int $id)
    {
        $result = $this->categoryService->getCategoryDetail($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }
        $data = $result->getData();
        return $this->responseSuccess($data);
    }
    public function updateCategory(UpdateCategoryRequest $request, int $id)
    {
        $data = $request->only([
            'name',
            'image_url',
            'description',
            'is_active',
            'parent_id',
            'cooking_time',
        ]);

        $category = $this->categoryRepository->getByConditions(['id' => $id]);
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
            'image_url',
            'description',
            'is_active',
            'parent_id'
        );
        $result = $this->categoryService->listTrashedCategory($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }
    public function softDeleteCategory(int $id)
    {
        $category = $this->categoryRepository->getByConditions(['id' => $id]);
        if (!$category) {
            return $this->responseFail(message: 'Danh mục không tồn tại', statusCode: 404);
        }
        $result = $this->categoryService->softDeleteCategory($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function forceDeleteCategory($id)
    {
        $result = $this->categoryService->forceDeleteCategory($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function restoreCategory($id)
    {
        $result = $this->categoryService->restoreCategory($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }

    public function getParentCategories()
    {
        $categories = $this->categoryService->getParentCategories();
        if (!$categories) {
            return $this->responseFail(message: 'Không có danh mục cha', statusCode: 404);
        }

        return $this->responseSuccess($categories);
    }
    public function countByStatus()
    {
        $result = $this->categoryService->countByStatus();

        return $this->responseSuccess($result);
    }
}