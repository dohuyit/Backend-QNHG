<?php
namespace App\Http\Controllers\Admin\Combo;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComboItemRequest\StoreComboItemRequest;
use App\Http\Requests\ComboItemRequest\UpdateComboItemRequest;
use App\Http\Requests\ComboRequest\StoreComboRequest;
use App\Http\Requests\ComboRequest\UpdateComboRequest;
use App\Repositories\ComboItems\ComboItemRepositoryInterface;
use App\Repositories\Combos\ComboRepositoryInterface;
use App\Services\ComboItems\ComboItemService;
use App\Services\Combos\ComboService;
use Faker\Provider\Base;
use Illuminate\Http\Request;
use Illuminate\Contracts\Cache\Store;

class ComboController extends Controller
{
    protected ComboService $comboService;
    protected ComboRepositoryInterface $comboRepository;
    protected ComboItemService $comboItemService;
    protected ComboItemRepositoryInterface $comboItemRepository;
    public function __construct(
        ComboService $comboService,
        ComboRepositoryInterface $comboRepository,
        ComboItemService $comboItemService,
        ComboItemRepositoryInterface $comboItemRepository,
    ) {
        $this->comboService = $comboService;
        $this->comboRepository = $comboRepository;
        $this->comboItemService = $comboItemService;
        $this->comboItemRepository = $comboItemRepository;
    }
    public function getListCombos()
    {
        $params = request()->only(
            'page',
            'limit',
            'query',
            'name',
            'image_url',
            'description',
            'original_total_price',
            'selling_price',
            'tags',
            'is_active'
        );
        $result = $this->comboService->getListCombos($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }
    public function createCombo(StoreComboRequest $request)
    {
        $data = $request->only(
            'name',
            'description',
            'original_total_price',
            'selling_price',
            'is_active'
        );
        $items = $request->input('items', []);

        // Sửa dòng này:
        $result = $this->comboService->createCombo($data, $items);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function getComboDetail(int $id)
    {
        $result = $this->comboService->getComboDetail($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        $data = $result->getData();
        return $this->responseSuccess($data);
    }
    public function updateCombo(UpdateComboRequest $request, int $id)
    {
        $data = $request->only(
            'name',
            'description',
            'image_url',
            'original_total_price',
            'selling_price',
            'is_active'
        );
        $combo = $this->comboRepository->getByConditions(['id' => $id]);
        if(!$combo) {
            return $this->responseFail(message: 'Combo không tồn tại', statusCode: 404);
        }
        $result = $this->comboService->updateCombo($data, $combo);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function listTrashedCombo(Request $request)
    {
        $params = $request->only(
            'page',
            'limit',
            'query',
            'name',
            'image_url',
            'description',
            'original_total_price',
            'selling_price',
            'is_active'
        );
        $result = $this->comboService->listTrashedCombo($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }
    public function softDeleteCombo(int $id)
    {
        $combo = $this->comboRepository->getByConditions(['id' => $id]);
        if (!$combo) {
            return $this->responseFail(message: 'Combo không tồn tại', statusCode: 404);
        }
        $result = $this->comboService->softDeleteCombo($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function forceDeleteCombo(int $id)
    {
        $result = $this->comboService->forceDeleteCombo($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function restoreCombo($id)
    {
        $result = $this->comboService->restoreCombo($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }

    public function addItemToCombo(StoreComboItemRequest $request, $id)
    {
        $id = (int) $id;
        $data = $request->only( 'dish_id', 'quantity');

        $data['combo_id'] = $id;

        $result = $this->comboItemService->addItem($data);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }
    public function updateItemQuantity(UpdateComboItemRequest $request, $comboId, $dishId)
    {
        $data['combo_id'] = $comboId;
        $data['dish_id'] = $dishId;
        $data['quantity'] = $request->get('quantity');


        $result = $this->comboItemService->updateItemQuantity($data);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function forceDeleteComboItem(string $comboId, string $dishId)
    {
        $result = $this->comboItemService->forceDeleteComboItem($comboId, $dishId);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
      public function countByStatus()
    {
        $result = $this->comboService->countByStatus();

        return $this->responseSuccess($result);
    }
}