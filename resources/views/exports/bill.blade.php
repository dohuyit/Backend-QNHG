<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Bill #{{ $bill->bill_code }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .restaurant-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .bill-info {
            margin-bottom: 20px;
        }

        .bill-info p {
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f5f5f5;
        }

        .total-section {
            margin-top: 20px;
            text-align: right;
        }

        .total-section p {
            margin: 5px 0;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-style: italic;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="restaurant-name">Quán Nhậu Hoàng Gia</div>
        <p>Hóa Đơn Thanh Toán</p>
    </div>

    <div class="bill-info">
        <p><strong>Mã hóa đơn:</strong> {{ $bill->bill_code }}</p>
        <p><strong>Thời gian:</strong> {{ $dateTime }}</p>
        <p><strong>Loại đơn:</strong> {{ $order->order_type === 'dine-in' ? 'Tại quán' : 'Mang về' }}</p>
        @if ($order->order_type === 'dine-in' && count($tables) > 0)
            <p><strong>Bàn:</strong>
                {{ implode(
                    ', ',
                    array_map(function ($table) {
                        return $table['tableItem']['name'];
                    }, $tables),
                ) }}
            </p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Tên món</th>
                <th>Số lượng</th>
                <th>Đơn giá</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        @if ($item->combo)
                            {{ $item->combo->name }}
                        @else
                            {{ $item->menuItem->name }}
                        @endif
                    </td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format($item->unit_price) }} đ</td>
                    <td>{{ number_format($item->quantity * $item->unit_price) }} đ</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <p><strong>Tổng tiền hàng:</strong> {{ number_format($bill->sub_total) }} đ</p>
        @if ($bill->discount_amount > 0)
            <p><strong>Giảm giá:</strong> {{ number_format($bill->discount_amount) }} đ</p>
        @endif
        @if ($bill->delivery_fee > 0)
            <p><strong>Phí giao hàng:</strong> {{ number_format($bill->delivery_fee) }} đ</p>
        @endif
        <p><strong>Tổng cộng:</strong> {{ number_format($bill->final_amount) }} đ</p>
    </div>

    @if (count($payments) > 0)
        <div class="payment-info">
            <h3>Thông tin thanh toán</h3>
            <table>
                <thead>
                    <tr>
                        <th>Phương thức</th>
                        <th>Số tiền</th>
                        <th>Thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments as $payment)
                        <tr>
                            <td>{{ strtoupper($payment->payment_method) }}</td>
                            <td>{{ number_format($payment->amount_paid) }} đ</td>
                            <td>{{ \Carbon\Carbon::parse($payment->payment_time)->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="footer">
        <p>Cảm ơn quý khách đã sử dụng dịch vụ!</p>
        <p>Hẹn gặp lại quý khách!</p>
    </div>
</body>

</html>
