<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComboItemRequest\StoreComboItemRequest;
use App\Http\Requests\ComboItemRequest\UpdateComboItemRequest;
use App\Repositories\ComboItems\ComboItemRepositoryInterface;
use App\Services\ComboItems\ComboItemServices;

class ComboItemController extends Controller
{
    protected ComboItemServices $comboItemService;
    protected ComboItemRepositoryInterface $comboItemRepository;

    public function __construct(
        ComboItemServices $comboItemService,
        ComboItemRepositoryInterface $comboItemRepository
    ) {
        $this->comboItemService = $comboItemService;
        $this->comboItemRepository = $comboItemRepository;
    }
    public function getListComboItems()
    {
        $params = request()->only(
            'page',
            'limit',
            'query',
            'combo_id',
            'dish_id',
            'quantity'
        );
        $result = $this->comboItemService->getListComboItems($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }
    public function addItemToCombo(StoreComboItemRequest $request)
    {
        $data = $request->only('combo_id', 'dish_id', 'quantity');

        $result = $this->comboItemService->addItem($data);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }
    public function updateItemQuantity(UpdateComboItemRequest $request, $comboSlug, $dishSlug)
    {
        $quantity = $request->get('quantity');

        $result = $this->comboItemService->updateItemQuantity($comboSlug, $dishSlug, (int)$quantity);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }


    public function forceDeleteComboItem(string $comboSlug, string $dishSlug)
    {
        $result = $this->comboItemService->forceDeleteComboItem($comboSlug, $dishSlug);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
}
