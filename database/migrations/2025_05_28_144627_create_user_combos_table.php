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
        Schema::create('user_combos', function (Blueprint $table) {
            $table->id()->comment('Mã combo riêng của người dùng');
            $table->foreignId('user_id')->comment('Mã người dùng tạo')->constrained('users', 'id')->onDelete('cascade');
            $table->string('combo_name', 255)->comment('Tên combo người dùng đặt');
            $table->string('slug', 255)->unique()->nullable()->comment('Slug cho combo riêng');
            $table->text('description')->nullable()->comment('Mô tả combo riêng');
            $table->decimal('price', 10, 2)->nullable()->comment('Giá combo riêng');
            $table->boolean('is_active')->default(true)->comment('Trạng thái sử dụng');
            $table->timestamps();
            $table->softDeletes()->comment('Thêm trường xóa mềm');
            $table->comment('Combo riêng do người dùng tự tạo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_combos');
    }
};
