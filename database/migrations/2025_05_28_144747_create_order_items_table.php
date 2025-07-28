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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id()->comment('Mã chi tiết đơn hàng');
            $table->foreignId('order_id')->comment('Mã đơn hàng')->constrained('orders', 'id')->onDelete('cascade');
            $table->foreignId('dish_id')->nullable()->comment('Mã món ăn')->constrained('dishes', 'id')->onDelete('set null');
            $table->foreignId('combo_id')->nullable()->comment('Mã combo')->constrained('combos', 'id')->onDelete('set null');
            $table->integer('quantity')->comment('Số lượng');
            $table->decimal('unit_price', 10, 2)->comment('Giá tại thời điểm đặt');
            $table->text('notes')->nullable()->comment('Ghi chú cho món');
            $table->enum('kitchen_status', ['pending', 'preparing', 'ready', 'cancelled'])->default('pending')->comment('Trạng thái món ở bếp');
            $table->boolean('is_priority')->default(false)->comment('Món ưu tiên');
            $table->boolean('is_additional')->default(false)->comment('Món được bổ sung (gọi thêm)');
            $table->timestamps();
            $table->comment('Chi tiết món ăn/combo trong đơn hàng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
