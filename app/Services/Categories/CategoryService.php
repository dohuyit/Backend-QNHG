<?php

namespace App\Services\Categories;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Models\Category;
use App\Repositories\Categories\CategoryRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class CategoryService
{ 
    protected CategoryRepositoryInterface $categoryRepository;
    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }
    public function getListCategories(array $params): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int)$params['limit'] : 10;

        $pagination = $this->categoryRepository->getCategoryList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string)$item->id,
                'name' => $item->name,
                'description' => $item->description,
                'image_url' => $item->image_url,
                'is_active' => (bool)$item->is_active,
                'parent_id' => $item->parent_id,
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
    public function createCategory(array $data): DataAggregate
    {
        $result = new DataAggregate();

        $listDataCreate = [
            'name' => $data['name'],
            'description' => $data['description'],
            'is_active' => $data['is_active'],
            'parent_id' => $data['parent_id'],
        ];

        if (!empty($data['image_url'])) {
            $file = $data['image_url'];
            $extension = $file->getClientOriginalExtension();
            $filename = 'category_' . uniqid() . '.' . $extension;

            $path = Storage::disk('public')->putFileAs('categories', $file, $filename);
            $listDataCreate['image_url'] = $path;
        }

        $ok = $this->categoryRepository->createData($listDataCreate);
        if (!$ok) {
            $result->setMessage(message: 'Thêm mới thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Thêm mới thành công!');
        return $result;
    }
    public function getCategoryDetail(int $id): DataAggregate
    {
        $result = new DataAggregate();

        $category = $this->categoryRepository->getByConditions(['id' => $id, 'is_active' => true]);

        if (!$category) {
            $result->setResultError(message: 'Danh mục không tồn tại');
            return $result;
        }

        $result->setResultSuccess(data: ['category' => $category]);
        return $result;
    }
    public function updateCategory(array $data, Category $category): DataAggregate
    {
        $result = new DataAggregate();

        $listDataUpdate = [
            'name' => $data['name'],
            'description' => $data['description'],            
            'is_active' => $data['is_active'],
            'parent_id' => $data['parent_id'],
        ];

        if (!empty($data['image_url'])) {

            if(!empty($category->image_url) && $category->image_url !== $data['image_url']){
            $oldImagePath = storage_path('app/public/' . $category->image_url);

                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $file = $data['image_url'];

            if(!empty($category->image_url) && Storage::disk('public')->exists($category->image_url)) {
                Storage::disk('public')->delete($category->image_url);
            }

            $extension = $file->getClientOriginalExtension();
            $filename = 'category_' . uniqid() . '.' . $extension;

            $path = Storage::disk('public')->putFileAs('categories', $file, $filename);
            $listDataUpdate['image_url'] = $path;
            
        }

        $ok = $this->categoryRepository->updateByConditions(['id' => $category->id], $listDataUpdate);
        if (!$ok) {
            $result->setMessage(message: 'Cập nhật thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Cập nhật thành công!');
        return $result;
    }
    public function listTrashedCategory(array $params): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int)$params['limit'] : 10;
        $pagination =  $this->categoryRepository->getTrashCategoryList($filter, $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string)$item->id,
                'name' => $item->name,
                'description' => $item->description,
                'image_url' => $item->image_url,
                'is_active' => (bool)$item->is_active,
                'parent_id' => $item->parent_id,
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
    public function softDeleteCategory($id): DataAggregate
    {
        $result = new DataAggregate();
        $category = $this->categoryRepository->getByConditions(['id' => $id]);
        $ok = $category->delete();
        if (!$ok) {
            $result->setMessage(message: 'Xóa thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Xóa thành công!');
        return $result;
    }

    public function forceDeleteCategory($id): DataAggregate
    {
        $result = new DataAggregate();
        $category = $this->categoryRepository->findOnlyTrashedById($id);

        if (!empty($category->image_url)) {
            if(Storage::disk('public')->exists($category->image_url)){
                Storage::disk('public')->delete($category->image_url);
            }
        }

        $ok = $category->forceDelete();
        if (!$ok) {
            $result->setMessage(message: 'Xóa vĩnh viễn thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Xóa vĩnh viễn thành công!');
        return $result;
    }

    public function restoreCategory($id): DataAggregate
    {
        $result = new DataAggregate();
        $category = $this->categoryRepository->findOnlyTrashedById($id);
        $ok = $category->restore();
        if (!$ok) {
            $result->setMessage(message: 'Khôi phục thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Khôi phục thành công!');
        return $result;
    }
}
