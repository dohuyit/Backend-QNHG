<?php

namespace App\Repositories\Carts;

use App\Models\Cart;
use App\Models\CartItem;

class CartRepository implements CartRepositoryInterface
{
    public function getByConditions(array $conditions): ?Cart
    {
        return Cart::where($conditions)->with('items')->first();
    }

    public function createData(array $data): Cart
    {
        return Cart::create($data);
    }

    public function getItems(int $cartId): array
    {
        return CartItem::where('cart_id', $cartId)->get()->toArray();
    }

    public function createItem(array $data): CartItem
    {
        return CartItem::create($data);
    }

    public function updateItem(int $id, array $data): bool
    {
        return CartItem::where('id', $id)->update($data);
    }

    public function deleteItems(int $id): bool
    {
        return CartItem::where('id', $id)->delete();
    }

    public function deleteItemsByCartId(int $cartId): bool
    {
        return CartItem::where('cart_id', $cartId)->delete();
    }

    public function update(int $id, array $data): bool
    {
        return Cart::where('id', $id)->update($data);
    }


}
