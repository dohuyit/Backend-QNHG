<?php

namespace App\Services\Dishes;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Helpers\ConvertHelper;
use App\Repositories\Dishes\DishRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DishService
{
    protected DishRepositoryInterface $dishRepository;
    public function __construct(DishRepositoryInterface $dishRepository)
    {
        $this->dishRepository = $dishRepository;
    }
    public function getListDishes(array $params): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int)$params['limit'] : 10;
        $pagination = $this->dishRepository->getDishList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string)$item->id,
                'category' => $item->category ? [
                    'id' => (string) $item->category->id,
                    'name' => $item->category->name,
                ] : null,
                'name' => $item->name,
                'image_url' => $item->image_url,
                'description' => $item->description,
                'original_price' => $item->original_price,
                'selling_price' => $item->selling_price,
                'unit' => $item->unit,
                'tags' => $item->tags ? ConvertHelper::convertJsonToString($item->tags) : '',
                'is_featured' => (bool)$item->is_featured,
                'status' => $item->status,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        }

        $result = new ListAggregate($data);
        $result->setMeta(
            page: $pagination->currentPage(),
            perPage: $pagination->perPage(),
            total: $pagination->total()
        );

        return $result;
    }
    public function createDish(array $data): DataAggregate
    {
        $result = new DataAggregate();

        $listDataCreate = [
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'],
            'original_price' => $data['original_price'],
            'selling_price' => $data['selling_price'],
            'unit' => $data['unit'],
            'is_featured' => $data['is_featured'] ?? false,
            'status' => $data['status'] ?? true,
        ];
        if (!empty($data['tags'])) {
            $listDataCreate['tags'] = ConvertHelper::convertStringToJson($data['tags']);
        }

        if (!empty($data['image_url'])) {
            $file = $data['image_url'];
            $extension = $file->getClientOriginalExtension();
            $filename = 'dish_' . uniqid() . '.' . $extension;

            $path = Storage::disk('public')->putFileAs('dishes', $file, $filename);
            $listDataCreate['image_url'] = $path;
        }

        $ok = $this->dishRepository->createData($listDataCreate);
        if (!$ok) {
            $result->setMessage(message: 'Thêm mới thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Thêm mới thành công!');
        return $result;
    }

    public function getDishDetail(int $id): DataAggregate
    {
        $result = new DataAggregate();

        $dish = $this->dishRepository->getByConditions(['id' => $id]);

        if (!$dish) {
            $result->setMessage(message: 'Món ăn không tồn tại');
            return $result;
        }

        $dish->tags = !empty($dish->tags)
            ? ConvertHelper::convertJsonToString($dish->tags)
            : '';

        $dish->category_name =  $dish->category->name;

        $dish->unsetRelation('category');

        $result->setResultSuccess(data: ['dish' => $dish]);
        return $result;
    }
    public function updateDish(array $data, $dish): DataAggregate
    {
        $result = new DataAggregate();

        $listDataUpdate = [
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'original_price' => $data['original_price'],
            'selling_price' => $data['selling_price'],
            'unit' => $data['unit'] ?? null,
            'is_featured' => $data['is_featured'] ?? false,
            'status' => $data['status'],
        ];

        if (!empty($data['tags'])) {
            $listDataUpdate['tags'] = ConvertHelper::convertStringToJson($data['tags']);
        }

        if (!empty($data['image_url'])) {
            if (!empty($dish->image_url) && $dish->image_url !== $data['image_url']) {
                $oldImagePath = storage_path('app/public/' . $dish->image_url);

                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $file = $data['image_url'];

            if (!empty($dish->image_url) && Storage::disk('public')->exists($dish->image_url)) {
                Storage::disk('public')->delete($dish->image_url);
            }

            $extension = $file->getClientOriginalExtension();
            $filename = 'dish_' . uniqid() . '.' . $extension;

            $path = Storage::disk('public')->putFileAs('dishes', $file, $filename);
            $listDataUpdate['image_url'] = $path;
        }

        $ok = $this->dishRepository->updateByConditions(['id' => $dish->id], $listDataUpdate);
        if (!$ok) {
            $result->setMessage(message: 'Cập nhật thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Cập nhật thành công!');
        return $result;
    }
    public function listTrashedDish(array $params): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int)$params['limit'] : 10;
        $pagination = $this->dishRepository->getTrashDishList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string)$item->id,
                'category' => $item->category ? [
                    'id' => (string) $item->category->id,
                    'name' => $item->category->name,
                ] : null,
                'name' => $item->name,
                'image_url' => $item->image_url,
                'description' => $item->description,
                'original_price' => $item->original_price,
                'selling_price' => $item->selling_price,
                'unit' => $item->unit,
                'tags' => $item->tags ? ConvertHelper::convertJsonToString($item->tags) : '',
                'is_featured' => (bool)$item->is_featured,
                'status' => $item->status,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        }

        $result = new ListAggregate($data);
        $result->setMeta(
            page: $pagination->currentPage(),
            perPage: $pagination->perPage(),
            total: $pagination->total()
        );

        return $result;
    }
    public function softDeleteDish(int $id): DataAggregate
    {
        $result = new DataAggregate();
        $dish = $this->dishRepository->getByConditions(['id' => $id]);
        $ok = $dish->delete();
        if (!$ok) {
            $result->setMessage(message: 'Xóa thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Xóa thành công!');
        return $result;
    }
    public function forceDeleteDish(int $id): DataAggregate
    {
        $result = new DataAggregate();
        $dish = $this->dishRepository->findOnlyTrashedById($id);

        if (!empty($dish->image_url)) {
            if (Storage::disk('public')->exists($dish->image_url)) {
                Storage::disk('public')->delete($dish->image_url);
            }
        }

        $ok = $dish->forceDelete();
        if (!$ok) {
            $result->setMessage(message: 'Xóa vĩnh viễn thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Xóa vĩnh viễn thành công!');
        return $result;
    }
    public function restoreDish(int $id): DataAggregate
    {
        $result = new DataAggregate();
        $dish = $this->dishRepository->findOnlyTrashedById($id);
        $ok = $dish->restore();
        if (!$ok) {
            $result->setMessage(message: 'Khôi phục thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Khôi phục thành công!');
        return $result;
    }

    public function getFeaturedDishes(): DataAggregate
    {
        $result = new DataAggregate();
        $dishes = $this->dishRepository->getFeaturedDishes();

        if ($dishes->isEmpty()) {
            $result->setMessage(message: 'Không tìm thấy món ăn nổi bật');
            return $result;
        }

        $data = [];
        foreach ($dishes as $item) {
            $data[] = [
                'id' => (string)$item->id,
                'category' => $item->category ? [
                    'id' => (string) $item->category->id,
                    'name' => $item->category->name,
                ] : null,
                'name' => $item->name,
                'image_url' => $item->image_url,
                'description' => $item->description,
                'original_price' => $item->original_price,
                'selling_price' => $item->selling_price,
                'unit' => $item->unit,
                'tags' => $item->tags ? ConvertHelper::convertJsonToString($item->tags) : '',
                'is_featured' => (bool)$item->is_featured,
                'status' => $item->status,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        }

        $result->setResultSuccess(data: $data);
        return $result;
    }

    public function getDishesByChildCategory(int $id): DataAggregate
    {
        $result = new DataAggregate();

        $dishes = $this->dishRepository->getByCategoryId($id);

        if ($dishes->isEmpty()) {
            $result->setMessage(message: 'Không tìm thấy món ăn nào trong danh mục');
            return $result;
        }

        $data = [];
        foreach ($dishes as $item) {
            $data[] = [
                'id' => (string)$item->id,
                'category' => $item->category ? [
                    'id' => (string) $item->category->id,
                    'name' => $item->category->name,
                ] : null,
                'name' => $item->name,
                'image_url' => $item->image_url,
                'description' => $item->description,
                'original_price' => $item->original_price,
                'selling_price' => $item->selling_price,
                'unit' => $item->unit,
                'tags' => $item->tags ? ConvertHelper::convertJsonToString($item->tags) : '',
                'is_featured' => (bool)$item->is_featured,
                'status' => $item->status,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        }

        $result->setResultSuccess(data: $data);
        return $result;
    }
    public function countByStatus(): array
    {
        $listStatus = ['active', 'inactive'];
        $counts = [];

        foreach ($listStatus as $status) {
            $counts[$status] = $this->dishRepository->countByConditions(['status' => $status]);
        }
        return $counts;
    }
  public function getAllActiveDishes(): DataAggregate
{
    $result = new DataAggregate();
    $dishes = $this->dishRepository->getAllActiveDishes();

    if ($dishes->isEmpty()) {
        $result->setMessage('Không có món ăn nào');
        return $result;
    }

    $data = [];
    foreach ($dishes as $item) {
        $data[] = [
            'id' => (string)$item->id,
            'name' => $item->name,
            'image_url' => $item->image_url,
            'selling_price' => $item->selling_price,
            'original_price' => $item->original_price,
            'unit' => $item->unit,
            'is_featured' => (bool)$item->is_featured,
            'category_name' => optional($item->category)->name,
            'created_at' => $item->created_at,
        ];
    }
    $result->setResultSuccess(data: $data);
    return $result;
}

    public function getLatestActiveDishes(int $limit = 10): DataAggregate
    {
        $result = new DataAggregate();
        $dishes = $this->dishRepository->getLatestActiveDishes($limit);

        if ($dishes->isEmpty()) {
            $result->setMessage('Không có món ăn mới!');
            return $result;
        }
        $data = [];
        foreach ($dishes as $item) {
            $data[] = [
                'id' => (string)$item->id,
                'name' => $item->name,
                'image_url' => $item->image_url,
                'selling_price' => $item->selling_price,
                'original_price' => $item->original_price,
                'unit' => $item->unit,
                'is_featured' => (bool)$item->is_featured,
                'category_name' => optional($item->category)->name,
                'created_at' => $item->created_at,
            ];
        }

        $result->setResultSuccess(data: $data);
        return $result;
    }
    public function getActiveDishDetail(int $id): DataAggregate
    {
        $result = new DataAggregate();
        $dish = $this->dishRepository->getActiveDishDetail($id);

        if (!$dish) {
            $result->setMessage('Không tìm thấy món ăn!');
            return $result;
        }

        $dish->tags = !empty($dish->tags)
            ? ConvertHelper::convertJsonToString($dish->tags)
            : '';

        $dish->category_name = $dish->category?->name;
        $dish->unsetRelation('category');

        $result->setResultSuccess(data: ['dish' => $dish]);
        return $result;
    }
}
