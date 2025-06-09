<?php

namespace App\Services\Combos;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Helpers\ConvertHelper;
use App\Repositories\Combos\ComboRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ComboServices
{
    protected ComboRepositoryInterface $comboRepository;
    public function __construct(ComboRepositoryInterface $comboRepository)
    {
        $this->comboRepository = $comboRepository;
    }
    public function getListCombos(array $params): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int)$params['limit'] : 10;
        $pagination = $this->comboRepository->getComboList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string)$item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'image_url' => $item->image_url,
                'description' => $item->description,
                'original_total_price' => $item->original_total_price,
                'selling_price' => $item->selling_price,
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
    public function createCombo(array $data): DataAggregate
    {
        $result = new DataAggregate();
        $slug = Str::slug($data['name'] ?? '');
        $slugExists = $this->comboRepository->getByConditions(['slug' => $slug]);
        if ($slugExists) {  
            $result->setMessage(message: 'Tên combo đã tồn tại, vui lòng chọn tên khác!');
            return $result;
        }
        $listDataCreate = [
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'],
            'original_total_price' => $data['original_total_price'],
            'selling_price' => $data['selling_price'],
            'is_active' => $data['is_active'] ?? true,
        ];

        if (!empty($data['tags'])) {
            $listDataCreate['tags'] = ConvertHelper::convertStringToJson($data['tags']);
        }

        if (!empty($data['image_url'])) {
            $file = $data['image_url'];
            $extension = $file->getClientOriginalExtension();

            $filename = 'combo_' . uniqid() . '.' . $extension;

            $path = $file->storeAs('combos', $filename, 'public');
            $listDataCreate['image_url'] = $path;
        }

        $ok = $this->comboRepository->createData($listDataCreate);
        if (!$ok) {
            $result->setMessage('Thêm mới thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Thêm mới thành công!');
        return $result;
    }
    public function getComboDetail(string $slug): ?DataAggregate
    {
        $result = new DataAggregate();
        $combo = $this->comboRepository->getByConditions(['slug' => $slug]);
        if (!$combo) {
            $result->setMessage('Không tìm thấy combo!');
            return $result;
        }
        $comboItems = $combo->items;

        $items = [];
        foreach($comboItems as $item){
            $items[] = [
                'dish_name' => $item->dish->name ?? '',
                'quantity' => $item->quantity,
            ];
        }
        $result->setResultSuccess([
            'combo' => $combo,
            'items' => $items,
        ]);
        return $result;
    }

    public function updateCombo(array $data, $combo)
    {
        $result = new DataAggregate();
        $slug = Str::slug($data['name'] ?? '');

        if ($slug !== $combo->slug && $this->comboRepository->getByConditions(['slug' => $slug])) {
            $result->setMessage(message: 'Tên combo đã tồn tại, vui lòng chọn tên khác!');
            return $result;
        }

        $listDataUpdate = [
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'],
            'original_total_price' => $data['original_total_price'],
            'selling_price' => $data['selling_price'],
            'is_active' => $data['is_active'] ?? true,
        ];

        if (!empty($data['image_url'])) {
            if (!empty($combo->image_url) && $combo->image_url !== $data['image_url']) {
                $oldImagePath = storage_path('app/public/' . $combo->image_url);

                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $file = $data['image_url'];

            if (!empty($combo->image_url) && Storage::disk('public')->exists($combo->image_url)) {
                Storage::disk('public')->delete($combo->image_url);
            }

            $extension = $file->getClientOriginalExtension();
            $filename = 'combo_' . uniqid() . '.' . $extension;

            $path = Storage::disk('public')->putFileAs('combos', $file, $filename);
            $listDataUpdate['image_url'] = $path;
        }

        $ok = $this->comboRepository->updateByConditions(['slug' => $combo->slug], $listDataUpdate);
        if (!$ok) {
            $result->setMessage('Cập nhật thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Cập nhật thành công!');
        return $result;
    }
    public function listTrashedCombo(array $params): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int)$params['limit'] : 10;
        $pagination = $this->comboRepository->getTrashComboList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string)$item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'image_url' => $item->image_url,
                'description' => $item->description,
                'original_total_price' => $item->original_total_price,
                'selling_price' => $item->selling_price,
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
    public function softDeleteCombo($slug): DataAggregate
    {
        $result = new DataAggregate();
        $combo = $this->comboRepository->getByConditions(['slug' => $slug]);

        $ok = $combo->delete();
        if (!$ok) {
            $result->setMessage('Xóa tạm thời thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Xóa tạm thời thành công!');
        return $result;
    }

    public function forceDeleteCombo($slug): DataAggregate
    {
        $result = new DataAggregate();
        $combo = $this->comboRepository->findOnlyTrashedBySlug(['slug' => $slug]);
        // dd($combo);
        if (!empty($combo->image_url)) {
            if (Storage::disk('public')->exists($combo->image_url)) {
                Storage::disk('public')->delete($combo->image_url);
            }
        }
        
        $ok = $combo->forceDelete();
        if (!$ok) {
            $result->setMessage('Xóa vĩnh viễn thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Xóa vĩnh viễn thành công!');
        return $result;
    }
    public function restoreCombo(string $slug): DataAggregate
    {
        $result = new DataAggregate();
        $combo = $this->comboRepository->findOnlyTrashedBySlug(['slug' => $slug]);
        $ok = $combo->restore();
        if (!$ok) {
            $result->setMessage('Khôi phục thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Khôi phục thành công!');
        return $result;
    }
}
