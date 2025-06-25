<?php

namespace App\Http\Controllers\Admin\Dish;

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
            'image_url',
            'description',
            'original_price',
            'selling_price',
            'unit',
            'tags',
            'is_featured',
            'status'
        );
        $result = $this->dishService->getListDishes($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }

    public function createDish(StoreDishRequest $request)
    {
        $data = $request->only([
            'category_id',
            'name',
            'description',
            'original_price',
            'selling_price',
            'image_url',
            'unit',
            'tags',
            'is_featured',
            'status'
        ]);

        $result = $this->dishService->createDish($data);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function getDishDetail(int $id)
    {
        $result = $this->dishService->getDishDetail($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }
        $data = $result->getData();
        return $this->responseSuccess($data);
    }
    public function updateDish(UpdateDishRequest $request, int $id)
    {
        $data = $request->only([
            'category_id',
            'name',
            'description',
            'original_price',
            'selling_price',
            'image_url',
            'unit',
            'tags',
            'is_featured',
            'status'
        ]);

        $dish = $this->dishRepository->getByConditions(['id' => $id]);
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
            'image_url',
            'description',
            'original_price',
            'selling_price',
            'unit',
            'tags',
            'is_featured',
            'status'
        );
        $result = $this->dishService->listTrashedDish($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }
    public function softDeleteDish(int $id)
    {
        $dish = $this->dishRepository->getByConditions(['id' => $id]);
        if (!$dish) {
            return $this->responseFail(message: 'Món ăn không tồn tại', statusCode: 404);
        }
        $result = $this->dishService->softDeleteDish($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function forceDeleteDish(int $id)
    {
        $result = $this->dishService->forceDeleteDish($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function restoreDish($id)
    {
        $result = $this->dishService->restoreDish($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
}
