<?php

namespace App\Services\Branchs;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Helpers\ConvertHelper;
use App\Models\Branch;
use App\Repositories\Branchs\BranchRepositoryInterface;
use Illuminate\Support\Str;

class BranchService
{
    protected BranchRepositoryInterface $branchRepository;

    public function __construct(BranchRepositoryInterface $branchRepository)
    {
        $this->branchRepository = $branchRepository;
    }

    public function getListBranchs(array $params): ListAggregate
    {
        $filter = $params;
        $limit = ! empty($params['limit']) && $params['limit'] > 0 ? (int) $params['limit'] : 10;

        $pagination = $this->branchRepository->getBranchList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string) $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'city_id' => $item->city_id,
                'district_id' => $item->district_id,
                'status' => $item->status,
                'is_main_branch' => (bool) $item->is_main_branch,
                'capacity' => $item->capacity,
                'area_size' => $item->area_size,
                'number_of_floors' => $item->number_of_floors,
                'image_banner' => $item->image_banner,
                'url_map' => $item->url_map,
                'description' => $item->description,
                'main_description' => $item->main_description,
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

    public function createBranch(array $data): DataAggregate
    {
        $result = new DataAggregate;
        $slug = Str::slug($data['name'] ?? '');
        $listDataCreate = [
            'city_id' => $data['city_id'],
            'district_id' => $data['district_id'],
            'name' => $data['name'],
            'slug' => $slug,
            'phone_number' => $data['phone_number'],
            'opening_hours' => $data['opening_hours'],
            'status' => $data['status'],
            'is_main_branch' => $data['is_main_branch'],
            'capacity' => $data['capacity'],
            'area_size' => $data['area_size'],
            'number_of_floors' => $data['number_of_floors'],
            'url_map' => $data['url_map'],
            'description' => $data['description'],
            'main_description' => $data['main_description'],
        ];

        if (! empty($data['tags'])) {
            $listDataCreate['tags'] = ConvertHelper::convertStringToJson($data['tags']);
        }
        if (! empty($data['image_banner'])) {
            $file = $data['image_banner'];
            $extension = $file->getClientOriginalExtension();

            $filename = 'branch_'.uniqid().'.'.$extension;

            $path = $file->storeAs('branchs', $filename, 'public');
            $listDataUpdate['image_banner'] = $path;
        }

        $ok = $this->branchRepository->createData($listDataCreate);
        if (! $ok) {
            $result->setMessage(message: 'Thêm mới thất bại, vui lòng thử lại!');

            return $result;
        }
        $result->setResultSuccess(message: 'Thêm mới thành công!');

        return $result;
    }

    public function getBranchDetail(string $slug): DataAggregate
    {
        $result = new DataAggregate;

        $branch = $this->branchRepository->getByConditions(['slug' => $slug, 'status' => Branch::STATUS_ACTIVE]);

        if (! $branch) {
            $result->setResultError(message: 'Chi nhánh không tồn tại');

            return $result;
        }

        $result->setResultSuccess(data: ['branch' => $branch]);

        return $result;
    }

    public function updateBranch(array $data, Branch $branch): DataAggregate
    {
        $result = new DataAggregate;
        $slug = Str::slug($data['name'] ?? '');
        $listDataUpdate = [
            'city_id' => $data['city_id'],
            'district_id' => $data['district_id'],
            'name' => $data['name'],
            'slug' => $slug,
            'phone_number' => $data['phone_number'],
            'opening_hours' => $data['opening_hours'],
            'status' => $data['status'],
            'is_main_branch' => $data['is_main_branch'],
            'capacity' => $data['capacity'],
            'area_size' => $data['area_size'],
            'number_of_floors' => $data['number_of_floors'],
            'url_map' => $data['url_map'],
            'description' => $data['description'],
            'main_description' => $data['main_description'],
        ];

        if (! empty($data['tags'])) {
            $listDataUpdate['tags'] = ConvertHelper::convertStringToJson($data['tags']);
        }

        if (! empty($data['image_banner'])) {
            $file = $data['image_banner'];
            $extension = $file->getClientOriginalExtension();

            $filename = 'branch_'.uniqid().'.'.$extension;

            $path = $file->storeAs('branchs', $filename, 'public');
            $listDataUpdate['image_banner'] = $path;
        }

        $ok = $this->branchRepository->updateByConditions(['slug' => $branch->slug], $listDataUpdate);
        if (! $ok) {
            $result->setMessage(message: 'Cập nhật thất bại, vui lòng thử lại!');

            return $result;
        }
        $result->setResultSuccess(message: 'Cập nhật thành công!');

        return $result;
    }

    public function listTrashedBranch(array $params): ListAggregate
    {
        $filter = $params;
        $limit = ! empty($params['limit']) && $params['limit'] > 0 ? (int) $params['limit'] : 10;
        $pagination = $this->branchRepository->getTrashBranchList($filter, $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string) $item->id,
                'name' => $item->name,
                'slug' => $item->slug,
                'city_id' => $item->city_id,
                'district_id' => $item->district_id,
                'status' => $item->status,
                'is_main_branch' => (bool) $item->is_main_branch,
                'capacity' => $item->capacity,
                'area_size' => $item->area_size,
                'number_of_floors' => $item->number_of_floors,
                'image_banner' => $item->image_banner,
                'url_map' => $item->url_map,
                'description' => $item->description,
                'main_description' => $item->main_description,
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

    public function softDeleteBranch($slug): DataAggregate
    {
        $result = new DataAggregate;
        $branch = $this->branchRepository->getByConditions(['slug' => $slug]);
        $ok = $branch->delete();
        if (! $ok) {
            $result->setMessage(message: 'Xóa thất bại, vui lòng thử lại!');

            return $result;
        }
        $result->setResultSuccess(message: 'Xóa thành công!');

        return $result;
    }

    public function forceDeleteBranch($slug): DataAggregate
    {
        $result = new DataAggregate;
        $branch = $this->branchRepository->findOnlyTrashedBySlug($slug);
        $ok = $branch->forceDelete();
        if (! $ok) {
            $result->setMessage(message: 'Xóa vĩnh viễn thất bại, vui lòng thử lại!');

            return $result;
        }
        $result->setResultSuccess(message: 'Xóa vĩnh viễn thành công!');

        return $result;
    }

    public function restoreBranch($slug): DataAggregate
    {
        $result = new DataAggregate;
        $branch = $this->branchRepository->findOnlyTrashedBySlug($slug);
        $ok = $branch->restore();
        if (! $ok) {
            $result->setMessage(message: 'Khôi phục thất bại, vui lòng thử lại!');

            return $result;
        }
        $result->setResultSuccess(message: 'Khôi phục thành công!');

        return $result;
    }
}
