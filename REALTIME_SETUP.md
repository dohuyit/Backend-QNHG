# Hướng dẫn cấu hình Realtime cho đơn đặt bàn

## 1. Cấu hình Backend (Laravel)

### 1.1. Cài đặt dependencies
```bash
composer require pusher/pusher-php-server
```

### 1.2. Cấu hình .env
Thêm các biến môi trường sau vào file `.env`:

```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_pusher_app_id
PUSHER_APP_KEY=your_pusher_app_key
PUSHER_APP_SECRET=your_pusher_app_secret
PUSHER_APP_CLUSTER=mt1
```

### 1.3. Cấu hình Frontend (React)
Thêm các biến môi trường vào file `.env` của frontend:

```env
VITE_PUSHER_APP_KEY=your_pusher_app_key
VITE_PUSHER_APP_CLUSTER=mt1
VITE_API_URL=http://localhost:8000
```

### 1.4. Cài đặt Pusher JS
```bash
cd Frontend-QNHG
npm install pusher-js
```

## 2. Các Events đã tạo

### 2.1. ReservationCreated
- **File**: `app/Events/ReservationCreated.php`
- **Channels**: `reservations`, `private-admin.reservations`
- **Event name**: `reservation.created`
- **Triggered when**: Tạo đơn đặt bàn mới

### 2.2. ReservationStatusUpdated
- **File**: `app/Events/ReservationStatusUpdated.php`
- **Channels**: `reservations`, `private-admin.reservations`
- **Event name**: `reservation.status.updated`
- **Triggered when**: Cập nhật trạng thái đơn đặt bàn

## 3. Các Listeners đã tạo

### 3.1. SendReservationNotification
- **File**: `app/Listeners/SendReservationNotification.php`
- **Handles**: `ReservationCreated`
- **Purpose**: Gửi thông báo khi có đơn đặt bàn mới

### 3.2. UpdateReservationCounters
- **File**: `app/Listeners/UpdateReservationCounters.php`
- **Handles**: `ReservationCreated`, `ReservationStatusUpdated`
- **Purpose**: Cập nhật bộ đếm đơn đặt bàn

## 4. Components Frontend

### 4.1. NotificationDropdown
- **File**: `src/components/admin/CommonForBoth/TopbarDropdown/NotificationDropdown.jsx`
- **Features**: 
  - Hiển thị thông báo realtime
  - Đếm số thông báo chưa đọc
  - Tự động cập nhật khi có đơn đặt bàn mới

### 4.2. RealtimeReservationUpdater
- **File**: `src/components/admin/Reservations/RealtimeReservationUpdater.jsx`
- **Features**:
  - Cập nhật UI realtime
  - Hiển thị toast notifications
  - Tự động refresh data

## 5. Routes đã thêm

### 5.1. Broadcasting Authentication
- **Route**: `POST /api/broadcasting/auth`
- **Controller**: `BroadcastingController@authenticate`
- **Purpose**: Xác thực cho private channels

## 6. Cách sử dụng

### 6.1. Tạo đơn đặt bàn từ client
```php
// Trong ReservationClientService
event(new ReservationCreated($reservationData));
```

### 6.2. Cập nhật trạng thái
```php
// Trong ReservationService
event(new ReservationStatusUpdated($reservation, $oldStatus, $newStatus));
```

### 6.3. Frontend sẽ tự động nhận thông báo
- NotificationDropdown sẽ hiển thị thông báo mới
- RealtimeReservationUpdater sẽ cập nhật UI
- Toast notifications sẽ hiển thị

## 7. Testing

### 7.1. Test tạo đơn đặt bàn
1. Tạo đơn đặt bàn từ client
2. Kiểm tra thông báo xuất hiện trong admin panel
3. Kiểm tra UI được cập nhật realtime

### 7.2. Test cập nhật trạng thái
1. Cập nhật trạng thái đơn đặt bàn
2. Kiểm tra thông báo cập nhật
3. Kiểm tra UI được cập nhật

## 8. Troubleshooting

### 8.1. Không nhận được thông báo
- Kiểm tra cấu hình Pusher trong .env
- Kiểm tra console browser có lỗi không
- Kiểm tra network tab có request đến Pusher không

### 8.2. Lỗi authentication
- Kiểm tra token admin có hợp lệ không
- Kiểm tra route broadcasting/auth có hoạt động không
- Kiểm tra middleware auth:sanctum

### 8.3. Events không được dispatch
- Kiểm tra EventServiceProvider đã được đăng ký
- Kiểm tra events có được dispatch đúng không
- Kiểm tra logs Laravel 