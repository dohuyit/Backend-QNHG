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
        Schema::create('bills', function (Blueprint $table) {
            $table->id()->comment('Mã hóa đơn');
            $table->foreignId('order_id')->unique()->comment('Mã đơn hàng')->constrained('orders', 'id')->onDelete('cascade');
            $table->string('bill_code', 20)->unique()->comment('Mã hóa đơn')->index();
            $table->decimal('sub_total', 10, 2)->comment('Tổng tiền hàng');
            $table->decimal('discount_amount', 10, 2)->default(0.00)->comment('Tiền giảm giá');
            $table->decimal('delivery_fee', 10, 2)->default(0.00)->comment('Phí giao hàng');
            $table->decimal('final_amount', 10, 2)->comment('Tổng tiền cuối cùng');
            $table->enum('status', ['unpaid', 'paid', 'cancelled'])->default('unpaid')->comment('Trạng thái thanh toán');
            $table->timestamp('issued_at')->useCurrent()->comment('Thời gian xuất hóa đơn');
            $table->foreignId('user_id')->nullable()->comment('Nhân viên tạo/phụ trách')->constrained('users', 'id')->onDelete('set null');
            $table->timestamps();
            $table->comment('Thông tin hóa đơn thanh toán');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
