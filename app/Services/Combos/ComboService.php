<?php

namespace App\Services\Combos;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Helpers\ConvertHelper;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Combos\ComboRepositoryInterface;

class ComboService
{
    protected ComboRepositoryInterface $comboRepository;
    public function __construct(ComboRepositoryInterface $comboRepository)
    {
        $this->comboRepository = $comboRepository;
    }
    public function getListCombos(array $params): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int) $params['limit'] : 8;
        $pagination = $this->comboRepository->getComboList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string) $item->id,
                'name' => $item->name,
                'image_url' => $item->image_url,
                'description' => $item->description,
                'original_total_price' => $item->original_total_price,
                'selling_price' => $item->selling_price,
                'is_active' => (bool) $item->is_active,
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
    public function createCombo(array $data, array $items = []): DataAggregate
    {
        $result = new DataAggregate();

        $listDataCreate = [
            'name' => $data['name'],
            'description' => $data['description'],
            'original_total_price' => $data['original_total_price'],
            'selling_price' => $data['selling_price'],
            'is_active' => $data['is_active'] ?? true,
        ];

        // Xử lý upload ảnh nếu có
        if (!empty($data['image_url']) && $data['image_url'] instanceof \Illuminate\Http\UploadedFile) {
            $file = $data['image_url'];
            $extension = $file->getClientOriginalExtension();
            $filename = 'combo_' . uniqid() . '.' . $extension;
            $path = $file->storeAs('combos', $filename, 'public');
            $listDataCreate['image_url'] = $path;
        }

        // Sử dụng model Combo để tạo mới combo và các item liên quan
        try {
            $combo = \App\Models\Combo::create($listDataCreate);

            if ($combo && !empty($items)) {
                foreach ($items as $item) {
                    $combo->items()->create([
                        'dish_id' => $item['dish_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }
            }

            $result->setResultSuccess(message: 'Thêm mới thành công!');
        } catch (\Exception $e) {
            $result->setMessage('Thêm mới thất bại, vui lòng thử lại!');
        }

        return $result;
    }
    public function getComboDetail(int $id): ?DataAggregate
    {
        $result = new DataAggregate();
        $combo = $this->comboRepository->getByConditions(['id' => $id]);

        if (!$combo) {
            $result->setMessage('Không tìm thấy combo!');
            return $result;
        }
        $comboItems = $combo->items;

        $items = [];
        foreach ($comboItems as $item) {
            $items[] = [
                'id' => $item->id,
                'combo_id' => $item->combo_id,
                'dish_id' => $item->dish_id,
                'quantity' => $item->quantity,
                'dish_name' => $item->dish->name ?? '',
            ];
        }

        $comboData = [
            'id' => $combo->id,
            'name' => $combo->name,
            'description' => $combo->description,
            'original_total_price' => $combo->original_total_price,
            'selling_price' => $combo->selling_price,
            'image_url' => $combo->image_url,
            'is_active' => $combo->is_active,
            'created_at' => $combo->created_at,
            'updated_at' => $combo->updated_at,
            'deleted_at' => $combo->deleted_at,
        ];

        $result->setResultSuccess([
            'combo' => $comboData,
            'items' => $items
        ]);
        return $result;
    }

    /**
     * Cập nhật tất cả các trường của combo và combo item
     */
    public function updateCombo(array $data, $combo, array $items = []): DataAggregate
    {
        $result = new DataAggregate();

        if (!$combo) {
            $result->setMessage('Không tìm thấy combo!');
            return $result;
        }

        try {
            $listDataUpdate = [
                'name' => $data['name'] ?? $combo->name,
                'description' => $data['description'] ?? $combo->description,
                'original_total_price' => $data['original_total_price'] ?? $combo->original_total_price,
                'selling_price' => $data['selling_price'] ?? $combo->selling_price,
                'is_active' => array_key_exists('is_active', $data) ? $data['is_active'] : $combo->is_active,
            ];

            // Xử lý upload ảnh mới nếu có
            if (!empty($data['image_url']) && $data['image_url'] instanceof \Illuminate\Http\UploadedFile) {
                $file = $data['image_url'];
                // Xóa ảnh cũ nếu có
                if (!empty($combo->image_url) && Storage::disk('public')->exists($combo->image_url)) {
                    Storage::disk('public')->delete($combo->image_url);
                }

                // Upload ảnh mới
                $extension = $file->getClientOriginalExtension();
                $filename = 'combo_' . uniqid() . '.' . $extension;
                $path = $file->storeAs('combos', $filename, 'public');
                $listDataUpdate['image_url'] = $path;
            }
            // Nếu không upload ảnh mới, KHÔNG ghi đè image_url (giữ nguyên ảnh cũ)

            // Cập nhật thông tin combo
            $ok = $this->comboRepository->updateByConditions(['id' => $combo->id], $listDataUpdate);
            if (!$ok) {
                $result->setMessage('Cập nhật thất bại, vui lòng thử lại!');
                return $result;
            }

            // Cập nhật từng món ăn trong combo (combo items)
            if (is_array($items)) {
                $oldItems = $combo->items()->get()->keyBy('dish_id');
                $newDishIds = [];
                foreach ($items as $item) {
                    $dishId = $item['dish_id'];
                    $quantity = $item['quantity'];
                    $newDishIds[] = $dishId;
                    if ($oldItems->has($dishId)) {
                        $oldItem = $oldItems[$dishId];
                        $oldItem->id = $oldItem->id;
                        $oldItem->combo_id = $combo->id;
                        $oldItem->dish_id = $dishId;
                        $oldItem->quantity = $quantity;
                        $oldItem->save();
                    } else {
                        $combo->items()->create([
                            'combo_id' => $combo->id,
                            'dish_id' => $dishId,
                            'quantity' => $quantity,
                        ]);
                    }
                }
                $combo->items()->whereNotIn('dish_id', $newDishIds)->delete();
            }

            $result->setResultSuccess(message: 'Cập nhật thành công!');
        } catch (\Exception $e) {
            $result->setMessage('Cập nhật thất bại, vui lòng thử lại!');
        }

        return $result;
    }
    public function listTrashedCombo(array $params): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int) $params['limit'] : 10;
        $pagination = $this->comboRepository->getTrashComboList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string) $item->id,
                'name' => $item->name,
                'image_url' => $item->image_url,
                'description' => $item->description,
                'original_total_price' => $item->original_total_price,
                'selling_price' => $item->selling_price,
                'is_active' => (bool) $item->is_active,
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
    public function softDeleteCombo($id): DataAggregate
    {
        $result = new DataAggregate();
        $combo = $this->comboRepository->getByConditions(['id' => $id]);

        $ok = $combo->delete();
        if (!$ok) {
            $result->setMessage('Xóa tạm thời thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Xóa tạm thời thành công!');
        return $result;
    }

    public function forceDeleteCombo($id): DataAggregate
    {
        $result = new DataAggregate();
        $combo = $this->comboRepository->findOnlyTrashedById(['id' => $id]);

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
    public function restoreCombo(int $id): DataAggregate
    {
        $result = new DataAggregate();
        $combo = $this->comboRepository->findOnlyTrashedById(['id' => $id]);
        $ok = $combo->restore();
        if (!$ok) {
            $result->setMessage('Khôi phục thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Khôi phục thành công!');
        return $result;
    }
    public function countByStatus(): array
    {
        $listStatus = [true, false];
        $counts = [];

        foreach ($listStatus as $status) {
            $key = $status ? 'active' : 'inactive';
            $counts[$key] = $this->comboRepository->countByConditions(['is_active' => $status]);
        }

        return $counts;
    }
    public function updateStatus(int $id): DataAggregate
    {
        $result = new DataAggregate();
        $combo =  $this->comboRepository->getByConditions(['id' => $id]);
        if (!$combo) {
            $result->setMessage('Combo không tồn tại!');
            return $result;
        }
        $newValue = !$combo->is_active;
        $ok = $this->comboRepository->updateByConditions(['id'  => $id], ['is_active' => $newValue]);

        $message = $newValue ? 'Combo đã được kích hoạt thành công!' : 'Combo đã hủy kích hoạt thành công!';
        if (!$ok) {
            $result->setMessage('Cập nhật trạng thái combo thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: $message);
        return $result;
    }
}