<?php

namespace App\Services\Dishes;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Helpers\ConvertHelper;
use App\Repositories\Dishes\DishRepositoryInterface;
use Illuminate\Support\Str;

class DishesService
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
        foreach ($pagination->items() as $item){
            $data[] = [
                'id' => (string)$item->id,
                'category_id' => $item->category_id,
                'name' => $item->name,
                'slug' => $item->slug,
                'description' => $item->description,
                'original_price' => $item->original_price,
                'selling_price' => $item->selling_price,
                'unit' => $item->unit,
                'tags' => ConvertHelper::convertJsonToString($item->tags),
                'is_featured' => (bool)$item->is_featured,
                'is_active' => (bool)$item->is_active,
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
        $slug = Str::slug($data['name'] ?? '');
        $listDataCreate = [
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'],
            'original_price' => $data['original_price'],
            'selling_price' => $data['selling_price'],
            'unit' => $data['unit'],
            'is_featured' => $data['is_featured'] ?? false,
            'is_active' => $data['is_active'] ?? true,
        ];
        if (!empty($data['tags'])){
            $listDataCreate['tags'] = ConvertHelper::convertStringToJson($data['tags']);
        }

        if (!empty($data['image_url'])) {
            $file = $data['image_url'];
            $extension = $file->getClientOriginalExtension();

            $filename = 'dish_' . uniqid() . '.' . $extension;

            $path = $file->storeAs('dishes', $filename, 'public');
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

    public function getDishDetail(string $slug): DataAggregate
    {
        $result = new DataAggregate();

        $dish = $this->dishRepository->getByConditions(['slug' => $slug, 'is_active' => true]);

        if (!$dish) {
            $result->setMessage(message: 'Món ăn không tồn tại');
            return $result;
        }

        $dish->tags = ConvertHelper::convertJsonToString($dish->tags);

        $result->setResultSuccess(data: ['dish' => $dish]);
        return $result;
    }
    public function updateDish(array $data, $dish): DataAggregate
    {
        $result = new DataAggregate();
        $slug = Str::slug($data['name'] ?? '');
        $listDataUpdate = [
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'],
            'original_price' => $data['original_price'],
            'selling_price' => $data['selling_price'],
            'unit' => $data['unit'],
            'is_featured' => $data['is_featured'] ?? false,
            'is_active' => $data['is_active'] ?? true,
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
            $extension = $file->getClientOriginalExtension();

            $filename = 'dish_' . uniqid() . '.' . $extension;

            $path = $file->storeAs('dishes', $filename, 'public');
            $listDataUpdate['image_url'] = $path;
        }

        $ok = $this->dishRepository->updateByConditions( ['slug' => $dish->slug], $listDataUpdate);
        if  (!$ok) {
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
                'category_id' => $item->category_id,
                'name' => $item->name,
                'slug' => $item->slug,
                'description' => $item->description,
                'original_price' => $item->original_price,
                'selling_price' => $item->selling_price,
                'unit' => $item->unit,
                'tags' => ConvertHelper::convertJsonToString($item->tags),
                'is_featured' => $item->is_featured,
                'is_active' => (bool)$item->is_active,
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
    public function softDeleteDish(string $slug): DataAggregate
    {
        $result = new DataAggregate();
        $dish = $this->dishRepository->getByConditions(['slug' => $slug]);
        $ok = $dish->delete();
        if (!$ok) {
            $result->setMessage(message: 'Xóa thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Xóa thành công!');
        return $result;
    }
    public function forceDeleteDish(string $slug): DataAggregate
    {
        $result = new DataAggregate();
        $dish = $this->dishRepository->findOnlyTrashedBySlug($slug);
        $oldImagePath = storage_path('app/public/' . $dish->image_url);
        if (file_exists($oldImagePath)) {
            unlink($oldImagePath);
        }
        $ok = $dish->forceDelete();
        if (!$ok) {
            $result->setMessage(message: 'Xóa vĩnh viễn thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Xóa vĩnh viễn thành công!');
        return $result;
    }
    public function restoreDish(string $slug): DataAggregate
    {
        $result = new DataAggregate();
        $dish = $this->dishRepository->findOnlyTrashedBySlug($slug);
        $ok = $dish->restore();
        if (!$ok) {
            $result->setMessage(message: 'Khôi phục thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Khôi phục thành công!');
        return $result;
    }
    public function getDishByCategory(array $params, string $slug): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int)$params['limit'] : 10;
        $pagination = $this->dishRepository->getDishesByCategorySlug($slug, $filter, $limit);

        $data = [];
        foreach ($pagination->items() as $item){
            $data[] = [
                'id' => (string)$item->id,
                'category_id' => $item->category_id,
                'name' => $item->name,
                'slug' => $item->slug,
                'description' => $item->description,
                'original_price' => $item->original_price,
                'selling_price' => $item->selling_price,
                'unit' => $item->unit,
                'tags' => ConvertHelper::convertJsonToString($item->tags),
                'is_featured' => (bool)$item->is_featured,
                'is_active' => (bool)$item->is_active,
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
    

}

