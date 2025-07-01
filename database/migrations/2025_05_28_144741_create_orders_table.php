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
        Schema::create('orders', function (Blueprint $table) {
            $table->id()->comment('Mã đơn hàng');
            $table->string('order_code', 20)->unique()->comment('Mã code đơn hàng');
            $table->enum('order_type', ['dine-in', 'takeaway', 'delivery'])->comment('Loại đơn hàng');
            $table->foreignId('reservation_id')->nullable()->comment('Mã đặt bàn liên quan')->constrained('reservations', 'id')->onDelete('set null');
            $table->foreignId('user_id')->nullable()->comment('Nhân viên tạo/phụ trách')->constrained('users', 'id')->onDelete('set null');
            $table->foreignId('customer_id')->nullable()->comment('Mã khách hàng')->constrained('customers', 'id')->onDelete('set null');
            $table->timestamp('order_time')->useCurrent()->comment('Thời gian đặt/tạo đơn');
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'ready', 'served', 'delivering', 'completed', 'cancelled'])->default('pending')->comment('Trạng thái xử lý');
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid', 'refunded'])->default('unpaid')->comment('Trạng thái thanh toán');
            $table->text('notes')->nullable()->comment('Ghi chú chung');
            $table->text('delivery_address')->nullable()->comment('Địa chỉ giao hàng (delivery)');
            $table->string('contact_name', 100)->nullable()->comment('Tên liên hệ (khách hàng vãng lai hoặc giao hàng)');
            $table->string('contact_email', 100)->nullable()->comment('Email liên hệ (khách hàng vãng lai hoặc giao hàng)');
            $table->string('contact_phone', 20)->nullable()->comment('SĐT liên hệ (khách hàng vãng lai hoặc giao hàng)');
            $table->decimal('total_amount', 12, 2)->default(0.00)->comment('Tổng tiền tạm tính');
            $table->decimal('final_amount', 12, 2)->default(0.00)->comment('Tổng tiền cuối cùng (từ Bill)');
            $table->dateTime('delivered_at')->nullable()->comment('TG giao thành công');
            $table->timestamps();
            $table->softDeletes()->comment('Thêm trường xóa mềm');
            $table->comment('Thông tin đơn hàng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
