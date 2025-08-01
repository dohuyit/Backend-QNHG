<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitchenOrder extends Model
{
    use HasFactory;
    protected $table = 'kitchen_orders'; // Tên bảng trong cơ sở dữ liệu

    protected $fillable = [
        'order_item_id',        // UNIQUE
        'order_id',
        'table_numbers',
        'item_name',
        'quantity',
        'notes',
        'status',               // 'pending', 'preparing', 'ready', 'cancelled'
        'is_priority',
        'received_at',          // Thường tự động set
        'completed_at',
    ];

    protected $casts = [
        'table_numbers' => 'array',
    ];
    // Accessor: luôn trả về danh sách số bàn đúng cho từng đơn bếp
    public function getTableNumbersAttribute($value)
    {
        // Nếu đã có sẵn (ví dụ khi tạo mới), trả về luôn
        if (!empty($value)) {
            return is_array($value) ? $value : json_decode($value, true);
        }
        // Nếu chưa có, tự động truy vấn qua order_id
        $orderId = $this->order_id;
        if (!$orderId) return [];
        // Join order_tables và tables để lấy table_number
        $tableNumbers = \DB::table('order_tables')
            ->join('tables', 'order_tables.table_id', '=', 'tables.id')
            ->where('order_tables.order_id', $orderId)
            ->pluck('tables.table_number')
            ->toArray();
        return $tableNumbers;
    }
}
