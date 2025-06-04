<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use SoftDeletes;

    protected $table = 'branches';

    protected $fillable = [
        'city_id',          // Ví dụ: "01" (ID Thành phố Hà Nội từ API)
        'district_id',      // Ví dụ: "001" (ID Quận Ba Đình từ API)
        'name',             // Ví dụ: "Nhà hàng ABC - Chi nhánh Cầu Giấy"
        'slug',             // Ví dụ: "nha-hang-abc-chi-nhanh-cau-giay"
        'image_banner',         // Ví dụ: "image.jpg"
        'phone_number',     // Ví dụ: "02431234567"
        'opening_hours',    // Ví dụ: "08:00 - 22:00 hàng ngày"
        'tags',             // Ví dụ (JSON): ["gần đại học", "có điều hòa", "không gian rộng"]
        'status',           // Ví dụ: 'active', 'inactive', 'temporarily_closed'
        'is_main_branch',   // Ví dụ: true, false
        'capacity',         // Ví dụ: 150 (sức chứa 150 khách)
        'area_size',        // Ví dụ: 200.50 (diện tích 200.5 m2)
        'number_of_floors', // Ví dụ: 3
        'url_map',
        'description',      // Ví dụ: "Chi nhánh chuyên các món lẩu và nướng."
        'main_description', // Ví dụ: "Chi nhánh Cầu Giấy của Nhà hàng ABC, chuyên phục vụ các món lẩu và nướng với không gian rộng rãi, thoáng mát."
        'deleted_at',      // Trường xóa mềm
    ];

    const STATUS_ACTIVE = 'active';

    const STATUS_INACTIVE = 'inactive';
}
