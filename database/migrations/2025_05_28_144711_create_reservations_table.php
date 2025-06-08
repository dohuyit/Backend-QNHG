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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id()->comment('Mã đặt bàn');
            $table->foreignId('customer_id')->nullable()->comment('Mã khách hàng')->constrained('customers', 'id')->onDelete('set null');
            $table->string('customer_name', 100)->comment('Tên khách đặt');
            $table->string('customer_phone', 20)->comment('SĐT khách đặt');
            $table->string('customer_email', 100)->nullable()->comment('Email khách đặt');
            $table->dateTime('reservation_time')->comment('Thời gian đặt');
            $table->integer('number_of_guests')->comment('Số lượng khách');
            $table->foreignId('table_id')->nullable()->comment('Bàn chỉ định')->constrained('tables', 'id')->onDelete('set null');
            $table->text('notes')->nullable()->comment('Ghi chú');
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'no_show', 'seated'])->default('pending')->comment('Trạng thái đặt bàn');
            $table->foreignId('user_id')->nullable()->comment('Nhân viên tạo')->constrained('users', 'id')->onDelete('set null');
            $table->timestamp('confirmed_at')->nullable()->comment('Thời gian xác nhận');
            $table->timestamp('cancelled_at')->nullable()->comment('Thời gian hủy');
            $table->timestamp('completed_at')->nullable()->comment('Thời gian hoàn tất');
            $table->timestamps();
            $table->softDeletes()->comment('Thêm trường xóa mềm');
            $table->comment('Thông tin các yêu cầu đặt bàn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
