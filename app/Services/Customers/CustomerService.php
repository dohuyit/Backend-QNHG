<?php

namespace App\Services\Customers;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Helpers\ConvertHelper;
use App\Models\Customer;
use App\Repositories\Customers\CustomerRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomerService
{
    protected CustomerRepositoryInterface $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function getListCustomers(array $params): ListAggregate
    {
        $filter = $params;
        $limit = ! empty($params['limit']) && $params['limit'] > 0 ? (int) $params['limit'] : 5;

        $pagination = $this->customerRepository->getCustomerList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string) $item->id,
                'full_name' => $item->full_name ?? null,
                'avatar' => $item->avatar ?? null,
                'phone_number' => $item->phone_number ?? null,
                'email' => $item->email ?? null,
                'address' => $item->address ?? null,
                'date_of_birth' => $item->date_of_birth ? Carbon::parse($item->date_of_birth)->format('Y-m-d') : null,
                'gender' => $item->gender ?? null,
                'city_id' => $item->city_id ?? null,
                'district_id' => $item->district_id ?? null,
                'ward_id' => $item->ward_id ?? null,
                'status_customer' => $item->status_customer ?? null,
                'email_verified_at' => $item->email_verified_at,
                'created_at' => $item->created_at->toDateTimeString(),
                'updated_at' => $item->updated_at->toDateTimeString(),
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

    public function getCustomerDetail(string $id): DataAggregate
    {
        $result = new DataAggregate;
        $customer = $this->customerRepository->getByConditions(['id' => $id]);
        if (! $customer) {
            $result->setResultError(message: 'Khách hàng bạn tìm không hợp lệ hoặc đã bị khóa');
            return $result;
        }

        $result->setResultSuccess(data: ['customer' => $customer]);
        return $result;
    }

    public function updateCustomer(array $data, Customer $customer): DataAggregate
    {
        $result = new DataAggregate;
        $listDataUpdate = [
            'full_name' => $data['full_name'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => isset($data['password']) ? bcrypt($data['password']) : null,
            'address' => $data['address'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender' => $data['gender'] ?? null,
            'city_id' => $data['city_id'] ?? null,
            'district_id' => $data['district_id'] ?? null,
            'ward_id' => $data['ward_id'] ?? null,
            'status_customer' => $data['status_customer'] ?? 'active',
        ];

        if (!empty($data['avatar'])) {
            if (!empty($customer->avatar) && $customer->avatar !== $data['avatar']) {
                $oldImagePath = storage_path('app/public/' . $customer->avatar);

                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $file = $data['avatar'];

            // Xóa ảnh cũ nếu tồn tại
            if (!empty($customer->avatar) && Storage::disk('public')->exists($customer->avatar)) {
                Storage::disk('public')->delete($customer->avatar);
            }

            $extension = $file->getClientOriginalExtension();
            $filename = 'customer_' . uniqid() . '.' . $extension;

            // Lưu ảnh mới
            $path = Storage::disk('public')->putFileAs('customers', $file, $filename);
            $listDataUpdate['avatar'] = $path;
        }

        $ok = $this->customerRepository->updateByConditions(['id' => $customer->id], $listDataUpdate);

        if (!$ok) {
            $result->setMessage('Cập nhật thất bại, vui lòng thử lại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Cập nhật thông tin khách hàng thành công!');

        return $result;
    }

    public function listTrashedCustomer(array $params): ListAggregate
    {
        $filter = $params;
        $limit = ! empty($params['limit']) && $params['limit'] > 0 ? (int) $params['limit'] : 10;
        $pagination = $this->customerRepository->getTrashCustomerList($filter, $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'full_name' => $item->full_name ?? null,
                'avatar' => $item->avatar ?? null,
                'phone_number' => $item->phone_number ?? null,
                'email' => $item->email ?? null,
                'address' => $item->address ?? null,
                'date_of_birth' => $item->date_of_birth ? Carbon::parse($item->date_of_birth)->format('Y-m-d') : null,
                'gender' => $item->gender ?? null,
                'city_id' => $item->city_id ?? null,
                'district_id' => $item->district_id ?? null,
                'ward_id' => $item->ward_id ?? null,
                'status_customer' => $item->status_customer ?? null,
                'email_verified_at' => $item->email_verified_at,
                'created_at' => $item->created_at->toDateTimeString(),
                'updated_at' => $item->updated_at->toDateTimeString(),
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

    public function softDeleteCustomer($id): DataAggregate
    {
        $result = new DataAggregate;
        $customer = $this->customerRepository->getByConditions(['id' => $id]);
        $ok = $customer->delete();
        if (! $ok) {
            $result->setMessage(message: 'Xóa thất bại, vui lòng thử lại!');

            return $result;
        }
        $result->setResultSuccess(message: 'Xóa thành công!');

        return $result;
    }

    public function forceDeleteCustomer($id): DataAggregate
    {
        $result = new DataAggregate;
        $customer = $this->customerRepository->findOnlyTrashedById($id);

        if (!$customer) {
            $result->setMessage(message: 'Dữ liệu khách hàng không tồn tại trong thùng rác!');
            return $result;
        }

        if (!empty($customer->avatar)) {
            if (Storage::disk('public')->exists($customer->avatar)) {
                Storage::disk('public')->delete($customer->avatar);
            }

            $oldImagePath = storage_path('app/public/' . $customer->avatar);
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        $ok = $customer->forceDelete();
        if (!$ok) {
            $result->setMessage(message: 'Xóa vĩnh viễn thất bại, vui lòng thử lại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Xóa vĩnh viễn thành công!');
        return $result;
    }

    public function restoreCustomer($id): DataAggregate
    {
        $result = new DataAggregate;
        $customer = $this->customerRepository->findOnlyTrashedById($id);
        $ok = $customer->restore();
        if (!$ok) {
            $result->setMessage(message: 'Khôi phục thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Khôi phục thành công!');

        return $result;
    }
}
