<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DishRequest\StoreDishRequest;
use App\Http\Requests\DishRequest\UpdateDishRequest;
use App\Models\Dish;
use App\Repositories\Dishes\DishRepositoryInterface;
use App\Services\Dishes\DishService;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Http\Request;

class DishController extends Controller
{
   protected DishService $dishService;
   protected DishRepositoryInterface $dishRepository;
    public function __construct(
         DishService $dishService,
         DishRepositoryInterface $dishRepository
    ) {
         $this->dishService = $dishService;
         $this->dishRepository = $dishRepository;
    }

    public function getListDishes()
    {
        $params = request()->only(
            'page',
            'limit',
            'query',
            'category_id',
            'name',
            'slug',
            'image_url',
            'description',
            'original_price',
            'selling_price',
            'unit',
            'tags',
            'is_featured',
            'is_active'
        );
        $result = $this->dishService->getListDishes($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }
     public function getDishesByCategory(Request $request, string $slug)
    {
        $params = $request->only(
            'page',
            'limit',
            'query',
            'category_id',
            'name',
            'slug',
            'image_url',
            'description',
            'original_price',
            'selling_price',
            'unit',
            'tags',
            'is_featured',
            'is_active'
        );
        $result = $this->dishService->getDishByCategory($params, $slug);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }

    public function createDish(StoreDishRequest $request)	
    {
        $data = $request->only([
            'category_id',
            'name',
            'slug',
            'description',
            'original_price',
            'selling_price',
            'image_url',
            'unit',
            'tags',
            'is_featured',
            'is_active'
        ]);

        $result = $this->dishService->createDish($data);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function getDishDetail(string $slug)
    {
        $result = $this->dishService->getDishDetail($slug);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }
        $data = $result->getData();
        return $this->responseSuccess($data);
    }
    public function updateDish(UpdateDishRequest $request, string $slug)
    {
        $data = $request->only([
            'category_id',
            'name',
            'slug',
            'description',
            'original_price',
            'selling_price',
            'image_url',
            'unit',
            'tags',
            'is_featured',
            'is_active'
        ]);

        $dish = $this->dishRepository->getByConditions(['slug' => $slug]);
        if (!$dish) {
            return $this->responseFail(message: 'Món ăn không tồn tại', statusCode: 404);
        }

        $result = $this->dishService->updateDish($data, $dish);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function listTrashedDish(Request $request)
    {
        $params = $request->only(
            'page',
            'limit',
            'query',
            'category_id',
            'name',
            'slug',
            'image_url',
            'description',
            'original_price',
            'selling_price',
            'unit',
            'tags',
            'is_featured',
            'is_active'
        );
        $result = $this->dishService->listTrashedDish($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }
    public function softDeleteDish(string $slug)
    {
        $dish = $this->dishRepository->getByConditions(['slug' => $slug]);
        if (!$dish) {
            return $this->responseFail(message: 'Món ăn không tồn tại', statusCode: 404);
        }
        $result = $this->dishService->softDeleteDish($slug);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function forceDeleteDish(string $slug)
    {
        $result = $this->dishService->forceDeleteDish($slug);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function restoreDish($slug)
    {
        $result = $this->dishService->restoreDish($slug);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
}