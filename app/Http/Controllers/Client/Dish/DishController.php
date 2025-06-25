<?php

namespace App\Http\Controllers\Client\Dish;

use App\Http\Controllers\Controller;
use App\Repositories\Dishes\DishRepositoryInterface;
use App\Services\Dishes\DishService;

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

    public function getFeaturedDishes()
    {
        $result = $this->dishService->getFeaturedDishes();
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }
        $data = $result->getData();
        return $this->responseSuccess($data);
    }

    public function getDishesByChildCategory(int $id)
    {
        $result = $this->dishService->getDishesByChildCategory($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }
        $data = $result->getData();
        return $this->responseSuccess($data);
    }
}
