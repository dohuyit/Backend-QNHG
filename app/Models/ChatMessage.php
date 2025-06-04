<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $table = 'chat_messages'; // Tên bảng trong cơ sở dữ liệu

    protected $fillable = [
        'chat_id',
        'sender_type',          // 'customer', 'agent', 'system'
        'sender_id',            // ID của người gửi
        'message_text',
        'message_type',         // 'text', 'image', 'file', 'notification'
        'attachment_url',
        'sent_at',              // Thường tự động set
        'read_at',
    ];
}
