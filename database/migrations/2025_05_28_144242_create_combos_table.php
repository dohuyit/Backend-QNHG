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
        Schema::create('combos', function (Blueprint $table) {
            $table->id()->comment('Mã combo');
            $table->string('name', 255)->comment('Tên combo');
            $table->string('slug', 255)->unique()->comment('Định danh URL');
            $table->text('description')->nullable()->comment('Mô tả combo');
            $table->decimal('original_total_price', 10, 2)->comment('Tổng giá gốc các món');
            $table->decimal('selling_price', 10, 2)->comment('Giá bán combo');
            $table->string('image_url', 255)->nullable()->comment('URL hình ảnh');
            $table->boolean('is_active')->default(true)->comment('Trạng thái áp dụng');
            $table->timestamps();
            $table->softDeletes()->comment('Thêm trường xóa mềm');
            $table->comment('Các combo món ăn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combos');
    }
};
