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

        // Sử dụng model Combo để tạo mới combo và các item liên quan
        try {
            // Giả sử đã import model Combo ở đầu file: use App\Models\Combo;
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
                'dish_name' => $item->dish->name ?? '',
                'quantity' => $item->quantity,
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

    public function updateCombo(array $data, $combo)
    {
        $result = new DataAggregate();

        $listDataUpdate = [
            'name' => $data['name'],
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

        $ok = $this->comboRepository->updateByConditions(['id' => $combo->id], $listDataUpdate);
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
}
