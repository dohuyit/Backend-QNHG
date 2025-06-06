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
        Schema::create('branches', function (Blueprint $table) {
            $table->id()->comment('Mã cơ sở/chi nhánh');
            $table->string('name', 100)->comment('Tên cơ sở/chi nhánh');
            $table->string('slug', 120)->unique()->nullable()->comment('Slug cho URL thân thiện');
            $table->string('image_banner', 255)->nullable()->comment('URL hình ảnh banner đại diện cho chi nhánh');
            $table->string('phone_number', 20)->nullable()->comment('Số điện thoại của cơ sở');
            $table->string('opening_hours', 255)->nullable()->comment('Giờ mở cửa');
            $table->enum('status', ['active', 'inactive', 'temporarily_closed'])->default('active')->comment('Trạng thái hoạt động');
            $table->json('tags')->nullable()->comment('Các tag cho chi nhánh (ví dụ: "gần trường học", "có chỗ đậu xe ô tô")');
            $table->string('city_id', 10)->comment('ID Thành phố từ API địa giới hành chính, bắt buộc nhập');
            $table->string('district_id', 10)->comment('ID Quận/Huyện từ API địa giới hành chính, bắt buộc nhập');
            $table->enum('is_main_branch', ['true', 'false'])->default('false')->comment('Là trụ sở chính');
            $table->integer('capacity')->nullable()->comment('Sức chứa tổng cộng');
            $table->decimal('area_size', 10, 2)->nullable()->comment('Diện tích (mét vuông)');
            $table->integer('number_of_floors')->nullable()->comment('Số tầng');
            $table->text('url_map')->nullable()->comment('URL bản đồ');
            $table->string('description', 255)->nullable()->comment('Mô tả ngắn gọn');
            $table->longText('main_description')->nullable()->comment('Mô tả chi tiết về chi nhánh');
            $table->timestamps();
            $table->softDeletes()->comment('Thêm trường xóa mềm');
            $table->comment('Thông tin các cơ sở/chi nhánh của nhà hàng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
