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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id()->comment('Mã khuyến mãi');
            $table->string('name', 150)->comment('Tên khuyến mãi');
            $table->string('slug', 170)->unique()->nullable()->comment('Slug cho khuyến mãi');
            $table->text('description')->nullable()->comment('Mô tả');
            $table->enum('type', ['percentage', 'fixed'])->comment('Loại hình');
            $table->decimal('discount_value', 10, 2)->comment('Giá trị giảm giá');
            $table->decimal('min_order_amount', 12, 2)->nullable()->comment('Giá trị đơn hàng tối thiểu');
            $table->string('coupon_code', 50)->unique()->nullable()->comment('Mã coupon');
            $table->dateTime('start_date')->comment('Thời gian bắt đầu');
            $table->dateTime('end_date')->comment('Thời gian kết thúc');
            $table->json('tags')->nullable()->comment('Các tag cho khuyến mãi (VD: ["cuối tuần", "ngày lễ", "khách mới"])');
            $table->integer('usage_limit')->nullable()->comment('Giới hạn sử dụng tổng');
            $table->integer('usage_limit_per_customer')->nullable()->comment('Giới hạn sử dụng/khách');
            $table->integer('current_usage_count')->default(0)->comment('Số lần đã sử dụng');
            $table->boolean('is_active')->default(true)->comment('Trạng thái');
            $table->foreignId('user_id')->nullable()->comment('Người tạo')->constrained('users', 'id')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes()->comment('Thêm trường xóa mềm');
            $table->comment('Các chương trình khuyến mãi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
