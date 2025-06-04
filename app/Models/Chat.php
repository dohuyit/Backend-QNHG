<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table = 'chats'; // Tên bảng trong cơ sở dữ liệu

    protected $fillable = [
        'customer_identifier',  // Session ID, user ID, phone...
        'customer_id',          // Nếu khách hàng đã đăng nhập
        'status',               // 'open', 'assigned', 'closed', 'pending'
        'user_id',              // Nhân viên CSKH được gán
        'last_message_at',
        'closed_at',
        'channel',              // 'website', 'app', 'facebook'...
    ];
}
