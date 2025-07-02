<?php
// App\Services\Bills\BillService.php

namespace App\Services\Bills;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Repositories\Bills\BillRepositoryInterface;
use App\Repositories\Order\OrderRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BillService
{
    protected BillRepositoryInterface $billRepository;
    protected OrderRepositoryInterface $orderRepository;

    public function __construct(BillRepositoryInterface $billRepository, OrderRepositoryInterface $orderRepository)
    {
        $this->billRepository = $billRepository;
        $this->orderRepository = $orderRepository;
    }

    public function getListBills(array $params): ListAggregate
    {
        $limit = $params['limit'] ?? 10;
        $pagination = $this->billRepository->getBillList(filter: $params, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string) $item->id,
                'order_id' => (string) $item->order_id,
                'sub_total' => $item->sub_total,
                'discount' => $item->discount,
                'final_amount' => $item->final_amount,
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

    public function getBillDetail(int $id): DataAggregate
    {
        $result = new DataAggregate();
        $bill = $this->billRepository->getByConditions(['id' => $id]);

        if (!$bill) {
            $result->setMessage('Không tìm thấy hóa đơn');
            return $result;
        }

        $result->setResultSuccess(data: ['bill' => $bill]);
        return $result;
    }

    public function createBill(array $data): DataAggregate
    {
        $result = new DataAggregate();

        $order = $this->orderRepository->getByConditions(['id' => $data['order_id']]);
        if (!$order) {
            $result->setMessage('Đơn hàng không tồn tại');
            return $result;
        }

        $subTotal = $order->total_amount;
        $discount = $data['discount_amount'] ?? 0;
        $deliveryFee = $data['delivery_fee'] ?? 0;
        $finalAmount = $subTotal - $discount + $deliveryFee;

        $listDataCreate = [
            'order_id' => $order->id,
            'sub_total' => $subTotal,
            'discount_amount' => $discount,
            'delivery_fee' => $deliveryFee,
            'final_amount' => $finalAmount,
            'status' => $data['status'] ?? 'unpaid',
            'user_id' => Auth::id(),
            'bill_code' => $data['bill_code'] ?? 'HD-' . strtoupper(Str::random(8)),
        ];

        $ok = $this->billRepository->createData($listDataCreate);

        if (!$ok) {
            $result->setMessage('Tạo hóa đơn thất bại');
            return $result;
        }

        $this->orderRepository->updateByConditions(['id' => $order->id], [
            'final_amount' => $finalAmount,
        ]);

        $result->setResultSuccess(message: 'Tạo hóa đơn thành công!');
        return $result;
    }

    public function updateBill(int $id, array $data): DataAggregate
    {
        $result = new DataAggregate();

        $bill = $this->billRepository->getByConditions(['id' => $id]);
        if (!$bill) {
            $result->setMessage('Hóa đơn không tồn tại');
            return $result;
        }
        if ($bill->status === 'paid') {
            $result->setMessage('Hóa đơn đã thanh toán, không thể chỉnh sửa.');
            return $result;
        }

        $discount = $data['discount_amount'] ?? $bill->discount_amount;
        $deliveryFee = $data['delivery_fee'] ?? $bill->delivery_fee;
        $subTotal = $bill->sub_total;
        $finalAmount = $subTotal - $discount + $deliveryFee;

        $this->billRepository->updateByConditions(['id' => $id], [
            'discount_amount' => $discount,
            'delivery_fee' => $deliveryFee,
            'final_amount' => $finalAmount,
            'status' => $data['status'] ?? $bill->status,
        ]);

        $this->orderRepository->updateByConditions(['id' => $bill->order_id], [
            'final_amount' => $finalAmount,
        ]);

        $result->setResultSuccess(message: 'Cập nhật hóa đơn thành công');
        return $result;
    }
}
