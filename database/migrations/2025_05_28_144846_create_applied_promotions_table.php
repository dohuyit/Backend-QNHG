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
        Schema::create('applied_promotions', function (Blueprint $table) {
            $table->id()->comment('ID áp dụng khuyến mãi');
            $table->foreignId('bill_id')->comment('Mã hóa đơn')->constrained('bills', 'id')->onDelete('cascade');
            $table->foreignId('promotion_id')->comment('Mã khuyến mãi')->constrained('promotions', 'id');
            $table->decimal('discount_applied', 12, 2)->comment('Số tiền giảm giá');
            $table->string('coupon_code_used', 50)->nullable()->comment('Mã coupon đã dùng');
            $table->timestamp('applied_at')->useCurrent()->comment('Thời gian áp dụng');
            $table->foreignId('customer_id')->nullable()->comment('Mã khách sử dụng')->constrained('customers', 'id')->onDelete('set null');
            $table->timestamps();
            $table->comment('Lịch sử khuyến mãi đã áp dụng vào hóa đơn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applied_promotions');
    }
};
