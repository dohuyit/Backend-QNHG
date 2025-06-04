<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\BranchRequest\StoreBranchRequest;
use App\Http\Requests\BranchRequest\UpdateBranchRequest;
use App\Repositories\Branchs\BranchRepositoryInterface;
use App\Services\Branchs\BranchService;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    protected BranchService $branchService;

    protected BranchRepositoryInterface $branchRepository;

    public function __construct(
        BranchService $branchService,
        BranchRepositoryInterface $branchRepository
    ) {
        $this->branchService = $branchService;
        $this->branchRepository = $branchRepository;
    }

    /**
     * Display a listing of the resource.
     */
    public function getListBranchs(Request $request)
    {
        $params = $request->only(
            'page',
            'limit',
            'query',
            'city_id',
            'district_id',
            'name',
            'slug',
            'image_banner',
            'phone_number',
            'opening_hours',
            'tags',
            'status',
            'is_main_branch',
            'capacity',
            'area_size',
            'number_of_floors',
            'url_map',
            'description',
            'main_description',
        );
        $result = $this->branchService->getListBranchs($params);
        $data = $result->getResult();

        return $this->responseSuccess($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function createBranch(StoreBranchRequest $request)
    {
        $data = $request->only([
            'city_id',
            'district_id',
            'name',
            'slug',
            'image_banner',
            'phone_number',
            'opening_hours',
            'tags',
            'status',
            'is_main_branch',
            'capacity',
            'area_size',
            'number_of_floors',
            'url_map',
            'description',
            'main_description',
        ]);

        $result = $this->branchService->createBranch($data);
        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    /**
     * Display the specified resource.
     */
    public function getBranchDetail(string $slug)
    {
        $result = $this->branchService->getBranchDetail($slug);
        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }
        $data = $result->getData();

        return $this->responseSuccess($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateBranch(UpdateBranchRequest $request, string $slug)
    {
        dd($request->all());
        $data = $request->only([
            'city_id',
            'district_id',
            'name',
            'slug',
            'image_banner',
            'phone_number',
            'opening_hours',
            'tags',
            'status',           // Ví dụ: 'active', 'inactive', 'temporarily_closed'
            'is_main_branch',   // Ví dụ: true, false
            'capacity',         // Ví dụ: 150 (sức chứa 150 khách)
            'area_size',        // Ví dụ: 200.50 (diện tích 200.5 m2)
            'number_of_floors', // Ví dụ: 3
            'url_map',
            'description',      // Ví dụ: "Chi nhánh chuyên các món lẩu và nướng."
            'main_description',
        ]);

        $branch = $this->branchRepository->getByConditions(['slug' => $slug]);
        if (! $branch) {
            return $this->responseFail(message: 'Chi nhánh không tồn tại', statusCode: 404);
        }

        $result = $this->branchService->updateBranch($data, $branch);
        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function listTrashedBranch(Request $request)
    {
        $params = $request->only(
            'page',
            'limit',
            'query',
            'city_id',
            'district_id',
            'name',
            'slug',
            'image_banner',
            'phone_number',
            'opening_hours',
            'tags',
            'status',
            'is_main_branch',
            'capacity',
            'area_size',
            'number_of_floors',
            'url_map',
            'description',
            'main_description',
        );
        $result = $this->branchService->listTrashedBranch($params);
        $data = $result->getResult();

        return $this->responseSuccess($data);
    }

    public function softDeleteBranch($slug)
    {
        $branch = $this->branchRepository->getByConditions(['slug' => $slug]);
        if (! $branch) {
            return $this->responseFail(message: 'Chi nhánh không tồn tại', statusCode: 404);
        }
        $result = $this->branchService->softDeleteBranch($slug);
        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function forceDeleteBranch($slug)
    {
        $result = $this->branchService->forceDeleteBranch($slug);
        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function restoreBranch($slug)
    {
        $result = $this->branchService->restoreBranch($slug);
        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }
}
