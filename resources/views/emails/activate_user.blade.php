<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Kích hoạt tài khoản - Quán nhậu Hoàng Gia</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f6f6f6;
            margin: 0;
            padding: 20px;
        }
        .email-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .email-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .email-header h2 {
            color: #cc3300;
        }
        .email-content p {
            font-size: 15px;
            margin-bottom: 10px;
        }
        .btn-activate {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background-color: #cc3300;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h2>🍻 Quán nhậu Hoàng Gia</h2>
        </div>

        <div class="email-content">
            <p>Xin chào <strong>{{ $fullName }}</strong>,</p>
            <p>Tài khoản của bạn đã được tạo thành công.</p>

            <p><strong>Tên đăng nhập:</strong> {{ $username }}</p>
            <p><strong>Email:</strong> {{ $email }}</p>
            <p><strong>Mật khẩu tạm thời:</strong> {{ $password }}</p>

            <p>Vui lòng nhấn vào nút bên dưới để kích hoạt tài khoản:</p>

            <p style="text-align: center;">
                <a href="{{ $activationLink }}" class="btn-activate">Kích hoạt ngay</a>
            </p>

            <p>Nếu bạn không yêu cầu tạo tài khoản này, vui lòng bỏ qua email này.</p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Quán nhậu Hoàng Gia
        </div>
    </div>
</body>
</html>
