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
        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id()->comment('Mã giao dịch thanh toán');
            $table->foreignId('bill_id')->comment('Mã hóa đơn')->constrained('bills', 'id');
            $table->enum('payment_method', ['cash', 'credit_card', 'bank_transfer', 'momo', 'vnpay', 'points', 'other'])->comment('Phương thức thanh toán');
            $table->decimal('amount_paid', 12, 2)->comment('Số tiền thanh toán');
            $table->timestamp('payment_time')->useCurrent()->comment('Thời gian thanh toán');
            $table->string('transaction_ref', 100)->nullable()->comment('Mã tham chiếu giao dịch');
            $table->foreignId('user_id')->comment('Nhân viên nhận thanh toán')->constrained('users', 'id');
            $table->text('notes')->nullable()->comment('Ghi chú thanh toán');
            $table->timestamps();
            $table->comment('Lịch sử thanh toán hóa đơn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bill_payments');
    }
};
