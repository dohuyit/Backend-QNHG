<?php

namespace App\Services\Carts;

use App\Common\DataAggregate;
use App\Repositories\Carts\CartRepositoryInterface;
use App\Repositories\Dishes\DishRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use App\Models\CartItem;
use Illuminate\Support\Facades\Log;

class CartService
{
    protected CartRepositoryInterface $cartRepository;
    protected DishRepositoryInterface $dishRepository;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        DishRepositoryInterface $dishRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->dishRepository = $dishRepository;
    }

    public function getCart(?int $customerId = null): DataAggregate
    {
        $result = new DataAggregate();

        $customerId = $customerId ?? Auth::guard('customer')->id();
        $cart = $this->cartRepository->getByConditions(['customer_id' => $customerId]);

        if (!$cart) {
            $result->setMessage('Giỏ hàng trống.');
            return $result;
        }

        $data = [
            'id' => $cart->id,
            'customer_id' => $cart->customer_id,
            'total_amount' => $cart->total_amount,
            'items' => $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'dish_id' => $item->dish_id,
                    'dish_name' => optional($item->dish)->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ];
            })->toArray(),
        ];

        $result->setResultSuccess(data: ['cart' => $data]);
        return $result;
    }

    public function addItem(array $items): DataAggregate
    {
        $result = new DataAggregate();
        $customerId = Auth::guard('customer')->id();

        $cart = $this->cartRepository->getByConditions(['customer_id' => $customerId]);
        if (!$cart) {
            $cart = $this->cartRepository->createData([
                'customer_id'  => $customerId,
                'total_amount' => 0,
            ]);
        }

        foreach ($items as $item) {
            $dishId = $item['dish_id'] ?? null;
            $quantity = $item['quantity'] ?? 1;

            $dish = $this->dishRepository->getByConditions(['id' => $dishId]);
            if (!$dish) {
                continue; // Skip nếu món không tồn tại
            }

            $existingItem = $cart->items()->where('dish_id', $dishId)->first();

            if ($existingItem) {
                $newQuantity = $existingItem->quantity + $quantity;
                $this->cartRepository->updateItem($existingItem->id, ['quantity' => $newQuantity]);
            } else {
                $this->cartRepository->createItem([
                    'cart_id'  => $cart->id,
                    'dish_id'  => $dishId,
                    'quantity' => $quantity,
                    'price'    => $dish->selling_price,
                ]);
            }
        }

        $this->updateCartTotal($cart->id);
        $result->setResultSuccess(message: 'Thêm món ăn vào giỏ hàng thành công!');
        return $result;
    }

    public function updateItems(array $items): DataAggregate
    {
        $result = new DataAggregate();
        $customerId = Auth::guard('customer')->id();

        $cart = $this->cartRepository->getByConditions(['customer_id' => $customerId]);
        if (!$cart) {
            $result->setMessage('Giỏ hàng không tồn tại.');
            return $result;
        }

        foreach ($items as $item) {
            $dishId = $item['dish_id'] ?? null;
            $quantity = $item['quantity'] ?? null;

            if ($dishId && is_numeric($quantity) && $quantity > 0) {
                $cartItem = $cart->items()->where('dish_id', $dishId)->first();
                if ($cartItem) {
                    $this->cartRepository->updateItem($cartItem->id, ['quantity' => $quantity]);
                }
            }
        }

        $this->updateCartTotal($cart->id);
        $result->setResultSuccess(message: 'Cập nhật giỏ hàng thành công!');
        return $result;
    }

    public function removeItems(array $dishIds): DataAggregate
    {
        $result = new DataAggregate();
        $customerId = Auth::guard('customer')->id();

        $cart = $this->cartRepository->getByConditions(['customer_id' => $customerId]);
        if (!$cart) {
            $result->setMessage('Giỏ hàng không tồn tại.');
            return $result;
        }

        $cartItems = $cart->items()->whereIn('dish_id', $dishIds)->get();

        if ($cartItems->isEmpty()) {
            $result->setMessage('Không tìm thấy món ăn để xoá.');
            return $result;
        }

        foreach ($cartItems as $item) {
            $this->cartRepository->deleteItems($item->id);
        }

        $this->updateCartTotal($cart->id);

        $result->setResultSuccess(message: 'Xoá món ăn thành công!');
        return $result;
    }












    public function clearCart(?int $customerId = null): DataAggregate
    {
        $result = new DataAggregate();

        $customerId = $customerId ?? Auth::guard('customer')->id();
        $cart = $this->cartRepository->getByConditions(['customer_id' => $customerId]);

        if (!$cart) {
            $result->setMessage('Không tìm thấy giỏ hàng để xoá.');
            return $result;
        }

        $this->cartRepository->deleteItemsByCartId($cart->id);
        $this->cartRepository->update($cart->id, ['total_amount' => 0]);

        $result->setResultSuccess(message: 'Đã xoá toàn bộ giỏ hàng');
        return $result;
    }

    protected function updateCartTotal(int $cartId): void
    {
        $items = $this->cartRepository->getItems($cartId);
        $total = collect($items)->reduce(fn($carry, $item) => $carry + ($item['price'] * $item['quantity']), 0);
        $this->cartRepository->update($cartId, ['total_amount' => $total]);
    }
}
