<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tables', function (Blueprint $table) {
            $table->id()->comment('Mã bàn ăn duy nhất');
            $table->foreignId('table_area_id')->comment('Mã khu vực chứa bàn')->constrained('table_areas', 'id');
            $table->string('table_number', 50)->comment('Số hiệu hoặc tên của bàn');
            $table->enum('table_type', ['2_seats', '4_seats', '6_seats', '8_seats'])->comment('Loại bàn (2 ghế, 4 ghế, 6 ghế, 8 ghế)');
            $table->string('description', 255)->nullable()->comment('Mô tả vị trí cụ thể của bàn');
            $table->json('tags')->nullable()->comment('Các tag cho bàn (VD: ["gần cửa sổ", "yên tĩnh", "phòng VIP"])');
            $table->enum('status', ['available', 'occupied', 'cleaning', 'out_of_service'])->nullable()->comment('Trạng thái');
            $table->timestamps();
            $table->softDeletes()->comment('Thêm trường xóa mềm');
            $table->comment('Thông tin các bàn ăn trong nhà hàng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tables');
    }
};
