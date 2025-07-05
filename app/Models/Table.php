<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    use HasFactory;
    protected $table = 'tables';
    protected $casts = [
        'tags' => 'array',
    ];
    protected $fillable = [
        'table_area_id',    // ID của khu vực bàn
        'table_number',     // Ví dụ: "A10", "VIP02", "Bar-05"
        'table_type',       // Ví dụ: '2_seats', '4_seats', '8_seats'
        'description',      // Ví dụ: "Bàn gần cửa sổ, view đẹp", "Bàn tròn lớn"
        'tags',             // Ví dụ (JSON): ["yên tĩnh", "view đẹp", "ghế sofa"]
        'status',           // Ví dụ: 'available', 'occupied', 'reserved', 'cleaning', 'out_of_service'
    ];

    // Constants cho table_type
    const TABLE_TYPE_2_SEATS = '2_seats';
    const TABLE_TYPE_4_SEATS = '4_seats';
    const TABLE_TYPE_8_SEATS = '8_seats';

    // Constants cho status
    const STATUS_AVAILABLE = 'available';
    const STATUS_OCCUPIED = 'occupied';
    const STATUS_RESERVED = 'reserved';
    const STATUS_CLEANING = 'cleaning';
    const STATUS_OUT_OF_SERVICE = 'out_of_service';

    public function tableArea(): BelongsTo
    {
        return $this->belongsTo(TableArea::class);
    }

    /**
     * Relationship với OrderTable
     */
    public function orderTables(): HasMany
    {
        return $this->hasMany(OrderTable::class);
    }

    /**
     * Lấy danh sách các loại bàn
     */
    public static function getTableTypes(): array
    {
        return [
            self::TABLE_TYPE_2_SEATS => '2 ghế',
            self::TABLE_TYPE_4_SEATS => '4 ghế',
            self::TABLE_TYPE_8_SEATS => '8 ghế',
        ];
    }

    /**
     * Lấy danh sách các trạng thái
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_AVAILABLE => 'Có sẵn',
            self::STATUS_OCCUPIED => 'Đang sử dụng',
            self::STATUS_RESERVED => 'Đã đặt trước',
            self::STATUS_CLEANING => 'Đang dọn dẹp',
            self::STATUS_OUT_OF_SERVICE => 'Không phục vụ',
        ];
    }

    public function orders()
    {
        // Nhiều-nhiều qua bảng order_tables
        return $this->belongsToMany(Order::class, 'order_tables', 'table_id', 'order_id');
    }
}
