<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatAgentStatus extends Model
{
    protected $table = 'chat_agent_status'; // Tên bảng trong cơ sở dữ liệu
    protected $fillable = [
        'user_id', // PK
        'status',
        'last_seen',
        'max_concurrent_chats',
        'current_chat_count',
    ];
}
