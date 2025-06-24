<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;
    protected $table = 'orders'; // Tên bảng trong cơ sở dữ liệu

    protected $fillable = [
        'order_code',           // Tự sinh hoặc theo quy tắc
        'order_type',           // 'dine-in', 'takeaway', 'delivery'
        'table_id',             // Cho 'dine-in'
        'reservation_id',       // Nếu đơn hàng từ đặt bàn
        'user_id',              // Nhân viên tạo đơn
        'customer_id',          // Khách hàng (nếu có)
        'order_time',           // Thường tự động set
        'status',               // 'pending_confirmation', 'confirmed', 'preparing', ...
        'payment_status',       // 'unpaid', 'partially_paid', 'paid', 'refunded'
        'notes',                // Ghi chú chung cho đơn hàng
        'delivery_address',     // Địa chỉ giao hàng (cho 'delivery')
        'contact_name',         // Tên liên hệ (khách vãng lai hoặc giao hàng)
        'contact_email',        // Email liên hệ (khách vãng lai hoặc giao hàng)
        'contact_phone',        // SĐT liên hệ (khách vãng lai hoặc giao hàng)
        'total_amount',         // Tổng tiền các món (trước giảm giá, phí)
        'final_amount',         // Tổng tiền cuối cùng (tham chiếu từ Bill)
        'delivered_at',         // Thời gian giao thành công (cho 'delivery')
    ];

    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    // Quan hệ với bảng reservations
    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    // Quan hệ với bảng users
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Quan hệ với bảng customers
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    // Quan hệ với bảng order_items
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    // Quan hệ với bảng bills
    public function bill()
    {
        return $this->hasOne(Bill::class, 'order_id');

    }
}
