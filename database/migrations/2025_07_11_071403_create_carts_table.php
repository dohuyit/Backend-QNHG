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
        Schema::create('carts', function (Blueprint $table) {
            $table->id()->comment('Mã giỏ hàng');
            $table->foreignId('customer_id')->nullable()->comment('Mã người dùng')->constrained('customers', 'id')->onDelete('set null');
            $table->decimal('total_amount', 12, 2)->default(0.00)->comment('Tổng tiền trong giỏ');
            $table->timestamps();
            $table->comment('Thông tin giỏ hàng của người dùng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
