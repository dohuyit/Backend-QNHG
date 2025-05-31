<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';
    protected $fillable = [
        'title',
        'slug',
        'content',
        'thumbnail_url',
        'tags',                 // Ví dụ (JSON): ["thông báo", "món mới", "sự kiện"]
        'post_category_id',
        'user_id',              // ID tác giả
        'is_published',
        // 'published_at' // Có thể set khi is_published = true
    ];
}
