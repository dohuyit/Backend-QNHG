<?php

namespace App\Http\Controllers\Client\Category;

use App\Http\Controllers\Controller;
use App\Repositories\Categories\CategoryRepositoryInterface;
use App\Services\Categories\CategoryService;

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

    public function getParentCategories()
    {
        $result = $this->categoryService->getParentCategories();
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }
        $data = $result->getData();
        return $this->responseSuccess($data);
    }

    public function getChildCategoriesByDish()
    {
        $result = $this->categoryService->getChildCategoriesByDish();
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }
        $data = $result->getData();
        return $this->responseSuccess($data);
    }
}
