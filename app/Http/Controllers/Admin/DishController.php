<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\DishRequest\StoreDishRequest;
use App\Models\Dish;
use App\Repositories\Dishes\DishRepositoryInterface;
use App\Services\Dishes\DishesService;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Http\Request;

class DishController extends Controller
{
   protected DishesService $dishesService;
   protected DishRepositoryInterface $dishRepository;
    public function __construct(
         DishesService $dishesService,
         DishRepositoryInterface $dishRepository
    ) {
         $this->dishesService = $dishesService;
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
        $result = $this->dishesService->getListDishes($params);
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
        $result = $this->dishesService->getDishByCategory($params, $slug);
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

        $result = $this->dishesService->createDish($data);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function getDishDetail(string $slug)
    {
        $result = $this->dishesService->getDishDetail($slug);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }
        $data = $result->getData();
        return $this->responseSuccess($data);
    }
    public function updateDish(StoreDishRequest $request, string $slug)
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

        $result = $this->dishesService->updateDish($data, $dish);
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
        $result = $this->dishesService->listTrashedDish($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }
    public function softDeleteDish(string $slug)
    {
        $dish = $this->dishRepository->getByConditions(['slug' => $slug]);
        if (!$dish) {
            return $this->responseFail(message: 'Món ăn không tồn tại', statusCode: 404);
        }
        $result = $this->dishesService->softDeleteDish($slug);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function forceDeleteDish(string $slug)
    {
        $result = $this->dishesService->forceDeleteDish($slug);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function restoreDish($slug)
    {
        $result = $this->dishesService->restoreDish($slug);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
}