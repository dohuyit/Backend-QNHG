<?php

namespace App\Http\Controllers\Client\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartRequest\AddCartItemRequest;
use App\Http\Requests\CartRequest\RemoveCartItemRequest;
use App\Services\Carts\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Lấy thông tin giỏ hàng
     */
    public function getCart()
    {
        $result = $this->cartService->getCart();

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
       $data = $result->getData();
        return $this->responseSuccess($data);
    }

    /**
     * Thêm nhiều món vào giỏ hàng
     */
    public function addToCart(AddCartItemRequest $request)
    {
        $items = $request->input('items', []);

        $result = $this->cartService->addItem($items);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    /**
     * Cập nhật số lượng nhiều món cùng lúc
     */
    public function updateCartItems(AddCartItemRequest $request)
    {
        $items = $request->input('items', []);

        $result = $this->cartService->updateItems($items);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    /**
     * Xoá nhiều món trong giỏ
     */
public function removeCartItems(Request  $request)
{
    $items = $request->input('items', []);
    $dishIds = collect($items)->pluck('dish_id')->toArray();

    $result = $this->cartService->removeItems($dishIds);

    if (!$result->isSuccessCode()) {
        return $this->responseFail(message: $result->getMessage());
    }

    return $this->responseSuccess(message: $result->getMessage());
}







    /**
     * Xoá toàn bộ giỏ hàng
     */
    public function clearCart()
    {
        $result = $this->cartService->clearCart();

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }
}
