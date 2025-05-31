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
        Schema::create('order_tables', function (Blueprint $table) {
            $table->id()->comment('ID gán bàn vào đơn hàng');
            $table->foreignId('order_id')->comment('Mã đơn hàng')->constrained('orders', 'id')->onDelete('cascade');
            $table->foreignId('table_id')->comment('Mã bàn sử dụng')->constrained('tables', 'id')->onDelete('restrict');
            $table->text('notes')->nullable()->comment('Ghi chú');
            $table->timestamps();
            $table->comment('Liên kết đơn hàng với bàn ăn (hỗ trợ gộp bàn)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_tables');
    }
};
