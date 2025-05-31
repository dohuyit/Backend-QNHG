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
            $table->foreignId('order_id')->unique()->comment('Mã đơn hàng')->constrained('orders', 'id');
            $table->foreignId('customer_id')->nullable()->comment('Mã khách thanh toán')->constrained('customers', 'id')->onDelete('set null');
            $table->string('bill_code', 50)->unique()->nullable()->comment('Mã tham chiếu hóa đơn');
            $table->decimal('sub_total', 12, 2)->comment('Tổng tiền hàng');
            $table->decimal('discount_amount', 12, 2)->default(0.00)->comment('Tổng tiền giảm giá');
            $table->decimal('vat_percentage', 5, 2)->default(8.00)->comment('% VAT');
            $table->decimal('vat_amount', 12, 2)->comment('Tiền VAT');
            $table->decimal('service_charge_percentage', 5, 2)->default(0.00)->comment('% Phí dịch vụ');
            $table->decimal('service_charge_amount', 12, 2)->default(0.00)->comment('Tiền phí dịch vụ');
            $table->decimal('delivery_fee', 10, 2)->default(0.00)->comment('Phí giao hàng');
            $table->decimal('final_amount', 12, 2)->comment('Tổng tiền cuối cùng');
            $table->enum('status', ['unpaid', 'partially_paid', 'paid', 'cancelled', 'refunded'])->default('unpaid')->comment('Trạng thái thanh toán');
            $table->timestamp('issued_at')->useCurrent()->comment('Thời gian xuất hóa đơn');
            $table->foreignId('user_id')->comment('Nhân viên xuất hóa đơn')->constrained('users', 'id');
            $table->timestamps();
            $table->softDeletes()->comment('Thêm trường xóa mềm');
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
