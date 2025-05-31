<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'branchs';
    protected $fillable = [
        'city_id',          // Ví dụ: "01" (ID Thành phố Hà Nội từ API)
        'district_id',      // Ví dụ: "001" (ID Quận Ba Đình từ API)
        'ward_id',          // Ví dụ: "00001" (ID Phường Phúc Xá từ API)
        'name',             // Ví dụ: "Nhà hàng ABC - Chi nhánh Cầu Giấy"
        'slug',             // Ví dụ: "nha-hang-abc-chi-nhanh-cau-giay"
        'address',          // Ví dụ: "Số 123, Đường Xuân Thủy, Cầu Giấy, Hà Nội"
        'phone_number',     // Ví dụ: "02431234567"
        'opening_hours',    // Ví dụ: "08:00 - 22:00 hàng ngày"
        'tags',             // Ví dụ (JSON): ["gần đại học", "có điều hòa", "không gian rộng"]
        'status',           // Ví dụ: 'active', 'inactive', 'temporarily_closed'
        'is_main_branch',   // Ví dụ: true, false
        'capacity',         // Ví dụ: 150 (sức chứa 150 khách)
        'area_size',        // Ví dụ: 200.50 (diện tích 200.5 m2)
        'number_of_floors', // Ví dụ: 3
        'short_description', // Ví dụ: "Chi nhánh chuyên các món lẩu và nướng."
    ];
}
