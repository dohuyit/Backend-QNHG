<?php

namespace App\Services\ComboItems;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Repositories\ComboItems\ComboItemRepositoryInterface;
use App\Repositories\Combos\ComboRepositoryInterface;
use App\Repositories\Dishes\DishRepositoryInterface;

class ComboItemServices
{
    protected ComboItemRepositoryInterface $comboItemRepository;
    protected ComboRepositoryInterface $comboRepository;
    protected DishRepositoryInterface $dishRepository;


    public function __construct(ComboItemRepositoryInterface $comboItemRepository, ComboRepositoryInterface $comboRepository, DishRepositoryInterface $dishRepository)
    {
        $this->comboItemRepository = $comboItemRepository;
        $this->comboRepository = $comboRepository;
        $this->dishRepository = $dishRepository;
    }
    public function addItem(array $data): DataAggregate
    {
        $result = new DataAggregate();
        $existingItem = $this->comboItemRepository->getByConditions([
            'combo_id' => $data['combo_id'],
            'dish_id' => $data['dish_id'],
        ]);

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $data['quantity'];
            $updated = $this->comboItemRepository->updateByConditions(
                ['id' => $existingItem->id],
                ['quantity' => $newQuantity],
            );
            if (!$updated) {
                $result->setMessage('Cập nhật số lượng thất bại');
                return $result;
            }
            $result->setResultSuccess(message: 'Cập nhật số lượng thành công');
            return $result;
        }

        $ok = $this->comboItemRepository->createData($data);

        if (!$ok) {
            $result->setMessage('Thêm món vào combo thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Thêm món vào combo thành công!');
        return $result;
    }
    public function updateItemQuantity(array $data): DataAggregate
    {
        $result = new DataAggregate();

        $combo = $this->comboRepository->getByConditions(['id' => $data['combo_id']]);
        $dish = $this->dishRepository->getByConditions(['id' => $data['dish_id']]);

        if (!$combo || !$dish) {
            $result->setMessage('Combo hoặc món ăn không tồn tại');
            return $result;
        }
        $comboItem = $this->comboItemRepository->getByConditions([
            'combo_id' => $combo->id,
            'dish_id' => $dish->id,
        ]);

        if (!$comboItem) {
            $result->setMessage('Không tìm thấy món trong combo để cập nhật.');
            return $result;
        }

        $ok = $this->comboItemRepository->updateByConditions(
            ['id' => $comboItem->id],
            ['quantity' => $data['quantity']]
        );

        if (!$ok) {
            $result->setMessage('Cập nhật số lượng thất bại.');
            return $result;
        }

        $result->setResultSuccess(message: 'Cập nhật số lượng thành công.');
        return $result;
    }

    public function forceDeleteComboItem($comboId, $dishId): DataAggregate
    {
        $result = new DataAggregate();
        $combo = $this->comboRepository->getByConditions(['id' => $comboId]);
        $dish = $this->dishRepository->getByConditions(['id' => $dishId]);

        if (!$combo || !$dish) {
            $result->setMessage('Combo hoặc món ăn không tồn tại');
            return $result;
        }

        $comboItem = $this->comboItemRepository->getByConditions([
            'combo_id' => $combo->id,
            'dish_id' => $dish->id
        ]);

        if (!$comboItem) {
            $result->setMessage('Món không tồn tại trong combo');
            return $result;
        }

        $ok = $comboItem->delete();

        if (!$ok) {
            $result->setMessage('Xóa thất bại, vui lòng thử lại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Xóa thành công');
        return $result;
    }
}
