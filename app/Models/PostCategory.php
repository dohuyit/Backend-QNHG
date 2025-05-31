<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostCategory extends Model
{
    protected $table = 'post_categories';
    protected $fillable = [
        'category_name',
        'slug',
        'description',
        'is_visible',
    ];
}
