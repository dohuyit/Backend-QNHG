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
        Schema::create('dishes', function (Blueprint $table) {
            $table->id()->comment('Mã món ăn');
            $table->foreignId('category_id')->comment('Mã danh mục')->constrained('categories', 'id');
            $table->string('name', 255)->comment('Tên món ăn');
            $table->text('description')->nullable()->comment('Mô tả món ăn');
            $table->decimal('original_price', 10, 2)->comment('Giá gốc');
            $table->decimal('selling_price', 10, 2)->comment('Giá bán');
            $table->enum('unit', ['bowl', 'plate', 'cup', 'glass', 'large_bowl', 'other'])->comment('Unit of measurement');
            $table->string('image_url', 255)->nullable()->comment('URL hình ảnh');
            $table->json('tags')->nullable()->comment('Các tag cho món ăn (VD: ["món chay", "cay", "best seller"])');
            $table->boolean('is_featured')->default(false)->comment('Món nổi bật');
            $table->boolean('is_active')->default(true)->comment('Trạng thái hiển thị');
            $table->timestamps();
            $table->softDeletes()->comment('Thêm trường xóa mềm');
            $table->comment('Các món ăn trong thực đơn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dishes');
    }
};
