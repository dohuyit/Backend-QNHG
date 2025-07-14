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

    public function getCart()
    {
        $result = $this->cartService->getCart();

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        $data = $result->getData();
        return $this->responseSuccess($data);
    }

    public function addToCart(AddCartItemRequest $request)
    {
        $items = $request->input('items', []);

        $result = $this->cartService->addItem($items);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function updateCartItems(AddCartItemRequest $request)
    {
        $items = $request->input('items', []);

        $result = $this->cartService->updateItems($items);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function removeCartItems(Request  $request)
    {
        $items = $request->input('items', []);

        $result = $this->cartService->removeItems($items);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function clearCart()
    {
        $result = $this->cartService->clearCart();

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }
}
