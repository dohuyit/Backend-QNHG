<?php

namespace App\Repositories\Carts;

use App\Models\Cart;
use App\Models\CartItem;

interface CartRepositoryInterface
{
    public function getByConditions(array $conditions): ?Cart;

    public function createData(array $data): Cart;

    public function getItems(int $cartId): array;

    public function createItem(array $data): CartItem;

    public function updateItem(int $id, array $data): bool;

    public function deleteItems(int $id): bool;

    public function deleteItemsByCartId(int $cartId): bool;

    public function update(int $id, array $data): bool;


}
