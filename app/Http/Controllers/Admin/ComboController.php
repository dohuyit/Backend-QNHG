<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComboRequest\StoreComboRequest;
use App\Http\Requests\ComboRequest\UpdateComboRequest;
use App\Repositories\Combos\ComboRepositoryInterface;
use App\Services\Combos\ComboServices;
use Faker\Provider\Base;
use Illuminate\Http\Request;
use Illuminate\Contracts\Cache\Store;

class ComboController extends Controller
{
    protected ComboServices $comboService;
    protected ComboRepositoryInterface $comboRepository;
    public function __construct(
        ComboServices $comboService,
        ComboRepositoryInterface $comboRepository
    ) {
        $this->comboService = $comboService;
        $this->comboRepository = $comboRepository;
    }
    public function getListCombos()
    {
        $params = request()->only(
            'page',
            'limit',
            'query',
            'name',
            'slug',
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
            'slug',
            'description',
            'original_total_price',
            'selling_price',
            'is_active'
        );

        $result = $this->comboService->createCombo($data);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function getComboDetail(string $slug)
    {
        $result = $this->comboService->getComboDetail($slug);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        $data = $result->getData();
        return $this->responseSuccess($data);
    }
    public function updateCombo(UpdateComboRequest $request, string $slug)
    {
        $data = $request->only(
            'name',
            'slug',
            'description',
            'image_url',
            'original_total_price',
            'selling_price',
            'is_active'
        );
        $combo = $this->comboRepository->getByConditions(['slug' => $slug]);
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
            'slug',
            'description',
            'original_total_price',
            'selling_price',
            'is_active'
        );
        $result = $this->comboService->listTrashedCombo($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }
    public function softDeleteCombo(string $slug)
    {
        $combo = $this->comboRepository->getByConditions(['slug' => $slug]);
        if (!$combo) {
            return $this->responseFail(message: 'Combo không tồn tại', statusCode: 404);
        }
        $result = $this->comboService->softDeleteCombo($slug);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function forceDeleteCombo(string $slug)
    {
        $result = $this->comboService->forceDeleteCombo($slug);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function restoreCombo($slug)
    {
        $result = $this->comboService->restoreCombo($slug);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
}