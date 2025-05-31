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
        Schema::create('categories', function (Blueprint $table) {
            $table->id()->comment('Mã danh mục món ăn');
            $table->string('name', 100)->comment('Tên danh mục');
            $table->string('slug', 100)->unique()->comment('Định danh URL');
            $table->text('description')->nullable()->comment('Mô tả danh mục');
            $table->string('image_url', 255)->nullable()->comment('URL hình ảnh');
            $table->boolean('is_active')->default(true)->comment('Trạng thái hiển thị');
            $table->timestamps();
            $table->softDeletes()->comment('Thêm trường xóa mềm');

            $table->comment('Danh mục các loại món ăn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
