<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderChangeLog extends Model
{
    protected $table = 'order_change_logs';

    protected $fillable = [
        'order_id',             // ID đơn hàng
        'user_id',              // ID nhân viên thực hiện thay đổi
        'change_timestamp',     // Thời điểm thay đổi
        'change_type',          // Loại thay đổi: STATUS_UPDATE, ITEM_ADDED, etc.
        'field_changed',        // Trường bị thay đổi: status, note, ...
        'old_value',            // Giá trị cũ
        'new_value',            // Giá trị mới
        'description',          // Mô tả chi tiết
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
