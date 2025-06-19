<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xác nhận đặt bàn - Quán Nhậu Hoàng Gia</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6;">
    <h2 style="color: #d4af37;">🍻 Xin chào {{ $reservation['customer_name'] }}!</h2>

    <p>Quán Nhậu Hoàng Gia rất vinh dự khi nhận được yêu cầu đặt bàn của bạn.</p>

    <p><strong>🪑 Bàn đã đặt:</strong> {{ $reservation['table_name'] ?? 'Chưa rõ' }} ({{ $reservation['table_area_name'] ?? 'Không rõ khu vực' }})</p>
    <p><strong>📅 Thời gian:</strong> {{ \Carbon\Carbon::parse($reservation['reservation_time'])->format('H:i d/m/Y') }}</p>
    <p><strong>👥 Số lượng khách:</strong> {{ $reservation['number_of_guests'] }}</p>
    <p><strong>📝 Ghi chú:</strong> {{ $reservation['notes'] ?? 'Không có' }}</p>

    <p>Đơn đặt của bạn đang trong trạng thái <strong>chờ xác nhận</strong>. Chúng tôi sẽ liên hệ lại trong thời gian sớm nhất để xác nhận và hỗ trợ thêm nếu cần.</p>

    <p>Nếu bạn có bất kỳ câu hỏi hay yêu cầu đặc biệt nào, đừng ngại liên hệ với chúng tôi qua email này hoặc hotline của nhà hàng.</p>

    <p>Trân trọng,<br>
    <strong>Quán Nhậu Hoàng Gia</strong><br>
    📞 1900 8888 39<br>
    📬 quannhauhoanggia@example.com</p>
</body>
</html>
