<?php

namespace App\Http\Controllers\Admin\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest\StoreCustomerRequest;
use App\Http\Requests\CustomerRequest\UpdateCustomerRequest;
use App\Repositories\Customers\CustomerRepositoryInterface;
use App\Services\Customers\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected CustomerService $customerService;

    protected CustomerRepositoryInterface $customerRepository;

    public function __construct(
        CustomerService $customerService,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->customerService = $customerService;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function getListCustomers(Request $request)
    {
        $params = $request->only(
            'page',
            'limit',
            'query',
            'full_name',
            'phone_number',
            'email',
            'address',
            'gender',
            'city_id',
            'district_id',
            'ward_id',
            'notes',
            'status',
        );
        $result = $this->customerService->getListCustomers($params);
        $data = $result->getResult();

        return $this->responseSuccess($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function createCustomer(StoreCustomerRequest $request)
    {
        $data = $request->only([
            'full_name',
            'avatar',
            'phone_number',
            'email',
            'password',
            'address',
            'date_of_birth',
            'gender',
            'city_id',
            'district_id',
            'ward_id',
            'tags',
            'notes',
            'status',
            'email_verified_at',
        ]);

        $result = $this->customerService->createCustomer($data);
        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    /**
     * Display the specified resource.
     */
    public function getCustomerDetail(string $id)
    {
        $result = $this->customerService->getCustomerDetail($id);
        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }
        $data = $result->getData();

        return $this->responseSuccess($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateCustomer(UpdateCustomerRequest $request, string $id)
    {
        $data = $request->only([
            'full_name',
            'avatar',
            'phone_number',
            'email',
            'password',
            'address',
            'date_of_birth',
            'gender',
            'city_id',
            'district_id',
            'ward_id',
            'tags',
            'notes',
            'status',
            'email_verified_at',
        ]);

        $branch = $this->customerRepository->getByConditions(['id' => $id]);
        if (! $branch) {
            return $this->responseFail(message: 'Chi nhánh không tồn tại', statusCode: 404);
        }

        $result = $this->customerService->updateCustomer($data, $branch);
        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function listTrashedCustomer(Request $request)
    {
        $params = $request->only(
            'page',
            'limit',
            'query',
            'full_name',
            'phone_number',
            'email',
            'address',
            'gender',
            'city_id',
            'district_id',
            'ward_id',
            'notes',
            'status',
        );
        $result = $this->customerService->listTrashedCustomer($params);
        $data = $result->getResult();

        return $this->responseSuccess($data);
    }

    public function softDeleteCustomer(string $id)
    {
        $customer = $this->customerRepository->getByConditions(['id' => $id]);
        if (!$customer) {
            return $this->responseFail(message: 'Khách hàng không tồn tại', statusCode: 404);
        }
        $result = $this->customerService->softDeleteCustomer($id);
        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function forceDeleteCustomer(string $id)
    {
        $result = $this->customerService->forceDeleteCustomer($id);
        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function restoreCustomer(string $id)
    {
        $result = $this->customerService->restoreCustomer($id);
        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }
}
